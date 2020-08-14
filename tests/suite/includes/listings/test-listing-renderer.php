<?php
/**
 * @package AWPCP\Test\Plugin\Listings
 */

/**
 * Unit tests for Listing Renderer class.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AWPCP_Test_Listing_Renderer extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );
    }

    // phpcs:disable Generic
    // phpcs:disable Squiz
    // phpcs:disable WordPress

    public function test_get_listing_title() {
        $listing = (object) array( 'post_title' => 'Test Listing' );

        $renderer = new AWPCP_ListingRenderer( null, null, null, null );

        $this->assertEquals( stripslashes( $listing->post_title ), $renderer->get_listing_title( $listing ) );
    }

    public function test_get_category_name() {
        $this->markTestSkipped();

        $categories = Phake::mock( 'AWPCP_Categories_Collection' );

        $listing = (object) array( 'ID' => wp_rand() + 1 );
        $category = (object) array( 'name' => 'Test Category' );

        Phake::when( $categories )->find_by_listing_id->thenReturn( array( $category ) );

        $renderer = new AWPCP_ListingRenderer( $categories, null, null, null );

        $this->assertEquals( $category->name, $renderer->get_category_name( $listing ) );
    }

    public function test_get_view_listing_url() {
        $this->markTestSkipped();

        $categories = Phake::mock( 'AWPCP_Categories_Collection' );

        $listing = (object) array( 'ID' => wp_rand() + 1 );
        $category = (object) array( 'name' => 'Test Category' );

        Phake::when( $categories )->find_by_listing_id->thenReturn( array( $category ) );

        $renderer = new AWPCP_ListingRenderer( $categories, null, null, null );

        $this->assertContains( (string) $listing->ID, $renderer->get_view_listing_url( $listing ) );
    }

    public function test_get_view_listing_link() {
        $this->markTestSkipped();

        $categories = Phake::mock( 'AWPCP_Categories_Collection' );

        $listing = awpcp_tests_create_empty_listing();
        $category = (object) array( 'name' => 'Test Category' );

        Phake::when( $categories )->find_by_listing_id->thenReturn( array( $category ) );

        $renderer = new AWPCP_ListingRenderer( $categories, null, null, null );

        $this->assertContains( (string) $listing->ID, $renderer->get_view_listing_link( $listing ) );
    }

    // phpcs:enable Generic
    // phpcs:enable Squiz
    // phpcs:enable WordPress

    /**
     * @since 4.0.0
     */
    public function test_is_featured() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->once()
            ->with( $post->ID, '_awpcp_is_featured', true )
            ->andReturn( true );

        $renderer = $this->get_test_subject();

        // Execution.
        $is_featured = $renderer->is_featured( $post );

        // Verification.
        $this->assertTrue( $is_featured );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListingRenderer(
            null,
            null,
            null,
            $this->wordpress
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_needs_review() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $this->wordpress = Mockery::mock( 'AWPCP_ListingRenderer' );

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->once()
            ->with( $post->ID, '_awpcp_content_needs_review', true )
            ->andReturn( true );

        $renderer = $this->get_test_subject();

        // Execution.
        $needs_review = $renderer->needs_review( $post );

        // Verification.
        $this->assertTrue( $needs_review );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_flagged() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $wordpress = Mockery::mock( 'AWPCP_WordPress' );

        $wordpress->shouldReceive( 'get_post_meta' )
            ->with( $post->ID, '_awpcp_flagged', true )
            ->andReturn( true );

        $renderer = new AWPCP_ListingRenderer( null, null, null, $wordpress );

        // Execution.
        $is_flagged = $renderer->is_flagged( $post );

        // Verification.
        $this->assertTrue( $is_flagged );
    }

    // phpcs:disable Generic
    // phpcs:disable Squiz
    // phpcs:disable WordPress

    public function test_has_expired() {
        $this->markTestSkipped();

        $listing = awpcp_tests_create_empty_listing();
        $wordpress = Phake::mock( 'AWPCP_WordPress' );

        $renderer = new AWPCP_ListingRenderer( null, null, null, $wordpress );

        $end_date = awpcp_datetime( 'mysql', current_time( 'timestamp' ) - MINUTE_IN_SECONDS );
        Phake::when( $wordpress )->get_post_meta( $listing->ID, '_awpcp_end_date', true )->thenReturn( $end_date );

        $this->assertTrue( $renderer->has_expired( $listing ) );

        $end_date = awpcp_datetime( 'mysql', current_time( 'timestamp' ) + MINUTE_IN_SECONDS );
        Phake::when( $wordpress )->get_post_meta( $listing->ID, '_awpcp_end_date', true )->thenReturn( $end_date );

        $this->assertFalse( $renderer->has_expired( $listing ) );
    }

    public function test_is_about_to_expire() {
        $this->markTestSkipped();

        awpcp()->settings->set_or_update_option( 'ad-renew-email-threshold', 1 );

        $listing = awpcp_tests_create_empty_listing();
        $wordpress = Phake::mock( 'AWPCP_WordPress' );

        $renderer = new AWPCP_ListingRenderer( null, null, null, $wordpress );

        // -----

        $current_time = current_time( 'timestamp' );
        $yesterday = awpcp_datetime( 'mysql', $current_time - DAY_IN_SECONDS );

        Phake::when( $wordpress )->get_post_meta( $listing->ID, '_awpcp_end_date', true )->thenReturn( $yesterday );
        $this->assertFalse( $renderer->is_about_to_expire( $listing ) );

        // -----

        $current_time = current_time( 'timestamp' );
        $one_second_before = awpcp_datetime( 'mysql', $current_time - 1 );

        Phake::when( $wordpress )->get_post_meta( $listing->ID, '_awpcp_end_date', true )->thenReturn( $one_second_before );
        $this->assertFalse( $renderer->is_about_to_expire( $listing ) );

        // -----

        $current_time = current_time( 'timestamp' );
        $later_today = awpcp_datetime( 'mysql', $current_time + 10 );

        Phake::when( $wordpress )->get_post_meta( $listing->ID, '_awpcp_end_date', true )->thenReturn( $later_today );
        $this->assertTrue( $renderer->is_about_to_expire( $listing ) );

        // -----

        $current_time = current_time( 'timestamp' );
        $same_time_next_day = strtotime( '+ 1 days', $current_time );
        $end_of_next_day = awpcp_datetime( 'mysql', awpcp_extend_date_to_end_of_the_day( $same_time_next_day ) );

        Phake::when( $wordpress )->get_post_meta( $listing->ID, '_awpcp_end_date', true )->thenReturn( $end_of_next_day );
        $this->assertFalse( $renderer->is_about_to_expire( $listing ) );

        // -----

        $current_time = current_time( 'timestamp' );
        $same_time_next_day = strtotime( '+ 1 days', $current_time );
        $after_next_day = awpcp_datetime( 'mysql', awpcp_extend_date_to_end_of_the_day( $same_time_next_day ) + 1 );

        Phake::when( $wordpress )->get_post_meta( $listing->ID, '_awpcp_end_date', true )->thenReturn( $after_next_day );
        $this->assertFalse( $renderer->is_about_to_expire( $listing ) );
    }

    // phpcs:enable Generic
    // phpcs:enable Squiz
    // phpcs:enable WordPress

    /**
     * @since 4.0.4
     *
     * @dataProvider has_expired_or_is_about_to_expire_data_provider
     */
    public function test_has_expired_or_is_about_to_expire( $expected_result, $has_expired, $is_about_to_expire ) {
        $post = (object) [];

        $this->redefine( 'AWPCP_ListingRenderer::has_expired', \Patchwork\always( $has_expired ) );
        $this->redefine( 'AWPCP_ListingRenderer::is_about_to_expire', \Patchwork\always( $is_about_to_expire ) );

        $result = $this->get_test_subject()->has_expired_or_is_about_to_expire( $post );

        $this->assertSame( $expected_result, $result );
    }

    /**
     * @since 4.0.4
     */
    public function has_expired_or_is_about_to_expire_data_provider() {
        return [
            [ false, false, false ],
            [ true, false, true ],
            [ true, true, false ],
            [ true, true, true ],
        ];
    }

    /**
     * @since 4.0.0
     */
    public function test_get_price() {
        $post = (object) [
            'ID' => wp_rand() + 1,
        ];

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $post->ID, '_awpcp_price', true )
            ->andReturn( '899' );

        $this->assertEquals( 899, $this->get_test_subject()->get_price( $post ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_pending_approval() {
        $post = (object) [ 'post_status' => 'pending' ];

        $this->assertTrue( $this->get_test_subject()->is_pending_approval( $post ) );

        $post = (object) [ 'post_status' => 'publish' ];

        $this->assertFalse( $this->get_test_subject()->is_pending_approval( $post ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_disabled() {
        $post = (object) [
            'ID'          => wp_rand() + 1,
            'post_status' => 'disabled',
        ];

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $post->ID, '_awpcp_expired', true )
            ->andReturn( false );

        $this->assertTrue( $this->get_test_subject()->is_disabled( $post ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_disabled_for_expired_listings() {
        $post = (object) [
            'ID'          => wp_rand() + 1,
            'post_status' => 'disabled',
        ];

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $post->ID, '_awpcp_expired', true )
            ->andReturn( true );

        $this->assertFalse( $this->get_test_subject()->is_disabled( $post ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_disabled_for_listings_with_a_different_post_status() {
        $post = (object) [ 'post_status' => 'not-disabled' ];

        $this->assertFalse( $this->get_test_subject()->is_disabled( $post ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_has_payment() {
        $post = (object) [
            'ID' => wp_rand() + 1,
        ];

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $post->ID, '_awpcp_payment_status', true )
            ->andReturn( AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED );

        $this->assertTrue( $this->get_test_subject()->has_payment( $post ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_has_payment_when_no_payment_has_been_done() {
        $post = (object) [
            'ID' => wp_rand() + 1,
        ];

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $post->ID, '_awpcp_payment_status', true )
            ->andReturn( 'Unpaid' );

        $this->assertFalse( $this->get_test_subject()->has_payment( $post ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_verified() {
        $post = (object) [
            'ID' => wp_rand() + 1,
        ];

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $post->ID, '_awpcp_verification_needed', true )
            ->andReturn( false );

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $post->ID, '_awpcp_verified', true )
            ->andReturn( true );

        $this->assertTrue( $this->get_test_subject()->is_verified( $post ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_verified_returns_false_if_verification_needed_meta_exists() {
        $post = (object) [
            'ID' => wp_rand(),
        ];

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $post->ID, '_awpcp_verification_needed', true )
            ->andReturn( true );

        $this->assertFalse( $this->get_test_subject()->is_verified( $post ) );
    }
}
