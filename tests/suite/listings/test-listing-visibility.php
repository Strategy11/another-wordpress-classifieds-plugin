<?php
/**
 * @package AWPCP\Tests\Listings
 */

use Brain\Monkey\Functions;

/**
 * Tests for the rule that hides non-public listings requested by ID.
 */
class AWPCP_ListingVisibilityTest extends AWPCP_UnitTestCase {

    /**
     * @var \Mockery\MockInterface
     */
    public $wordpress;

    /**
     * @var int
     */
    public $owner_id;

    public function setUp(): void {
        parent::setUp();

        require_once AWPCP_DIR . '/frontend/placeholders.php';

        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );
        $this->owner_id  = wp_rand() + 1;

        Functions\when( 'awpcp_basic_regions_api' )->justReturn( null );
        Functions\when( 'current_time' )->justReturn( strtotime( '2026-06-15 12:00:00' ) );
        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( false );
    }

    /**
     * @dataProvider visibility_data_provider
     */
    public function test_current_user_can_view( $status, $end_date, $viewer, $expected ) {
        $listing = $this->get_listing( $status, $end_date );

        $this->set_viewer( $viewer );

        $this->assertSame( $expected, $this->get_renderer()->current_user_can_view( $listing ) );
    }

    public function visibility_data_provider() {
        $future = '2026-12-31 00:00:00';
        $past   = '2026-01-01 00:00:00';

        return [
            'public listing, visitor'       => [ 'publish', $future, 'visitor', true ],
            'expired listing, visitor'      => [ 'publish', $past, 'visitor', false ],
            'pending listing, visitor'      => [ 'pending', $future, 'visitor', false ],
            'disabled listing, visitor'     => [ 'disabled', $future, 'visitor', false ],
            'pending listing, other user'   => [ 'pending', $future, 'subscriber', false ],
            'pending listing, owner'        => [ 'pending', $future, 'owner', true ],
            'expired listing, owner'        => [ 'disabled', $past, 'owner', true ],
            'disabled listing, moderator'   => [ 'disabled', $future, 'moderator', true ],
        ];
    }

    public function test_current_user_can_view_does_not_treat_anonymous_listings_as_owned_by_visitors() {
        $listing = $this->get_listing( 'pending', '2026-12-31 00:00:00' );

        $listing->post_author = 0;

        $this->logout();

        $this->assertFalse( $this->get_renderer()->current_user_can_view( $listing ) );
    }

    public function test_user_listings_shortcode_shows_non_public_listings_only_to_owner_and_moderators() {
        $this->set_viewer( 'subscriber' );
        $this->assertFalse( awpcp_current_user_can_see_user_listings( $this->owner_id ) );

        $this->set_viewer( 'owner' );
        $this->assertTrue( awpcp_current_user_can_see_user_listings( (string) $this->owner_id ) );

        $this->set_viewer( 'moderator' );
        $this->assertTrue( awpcp_current_user_can_see_user_listings( $this->owner_id ) );
    }

    public function test_view_counter_ignores_posts_that_are_not_listings() {
        $this->set_viewer( 'visitor' );

        $collection = Mockery::mock( 'AWPCP_ListingsCollection' );
        $collection->shouldReceive( 'get' )->andThrow( new AWPCP_Exception( 'Not a listing.' ) );

        $response = $this->run_view_counter( $collection, $this->get_renderer(), 'never' );

        $this->assertSame( 'error', $response['status'] );
    }

    public function test_view_counter_ignores_non_public_listings_for_visitors() {
        $this->set_viewer( 'visitor' );

        $collection = Mockery::mock( 'AWPCP_ListingsCollection' );
        $collection->shouldReceive( 'get' )->andReturn( $this->get_listing( 'pending', '2026-12-31 00:00:00' ) );

        $response = $this->run_view_counter( $collection, $this->get_renderer(), 'never' );

        $this->assertSame( 'error', $response['status'] );
    }

    public function test_view_counter_counts_public_listings() {
        $this->set_viewer( 'visitor' );

        $collection = Mockery::mock( 'AWPCP_ListingsCollection' );
        $collection->shouldReceive( 'get' )->andReturn( $this->get_listing( 'publish', '2026-12-31 00:00:00' ) );

        $response = $this->run_view_counter( $collection, $this->get_renderer(), 'once' );

        $this->assertSame( 'ok', $response['status'] );
    }

    /**
     * Runs the view counter AJAX handler and returns the JSON response.
     *
     * @param object $collection    Listings Collection.
     * @param object $renderer      Listing Renderer.
     * @param string $visits_called Mockery expectation for increase_visits_count().
     */
    private function run_view_counter( $collection, $renderer, $visits_called ) {
        $request = Mockery::mock( 'AWPCP_Request' );
        $request->shouldReceive( 'is_bot' )->andReturn( false );
        $request->shouldReceive( 'post' )->with( 'listing_id' )->andReturn( '123' );

        $listings_logic = Mockery::mock( 'AWPCP_ListingsAPI' );
        $listings_logic->shouldReceive( 'increase_visits_count' )->$visits_called();

        Functions\when( 'awpcp_do_placeholder_legacy_views' )->justReturn( '1' );
        Functions\when( 'get_post' )->justReturn( (object) [ 'ID' => 123, 'post_status' => 'draft', 'post_type' => 'post' ] );

        $response = null;

        $this->redefine(
            'AWPCP_AjaxHandler::flush',
            function ( $array_response ) use ( &$response ) {
                $response = $array_response;
            }
        );

        $counter = new AWPCP_ListingsViewCounter( null, $request, $listings_logic, $collection, $renderer );
        $counter->ajax();

        return $response;
    }

    private function get_renderer() {
        return new AWPCP_ListingRenderer( null, null, null, $this->wordpress );
    }

    private function get_listing( $status, $end_date ) {
        $listing = (object) [
            'ID'          => wp_rand() + 1,
            'post_status' => $status,
            'post_author' => $this->owner_id,
        ];

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $listing->ID, '_awpcp_end_date', true )
            ->andReturn( $end_date );

        return $listing;
    }

    private function set_viewer( $viewer ) {
        if ( 'visitor' === $viewer ) {
            $this->logout();
            return;
        }

        $this->login_as_subscriber();

        if ( 'owner' === $viewer ) {
            Functions\when( 'get_current_user_id' )->justReturn( $this->owner_id );
        }

        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( 'moderator' === $viewer );
    }
}
