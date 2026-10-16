<?php
/**
 * @package AWPCP\Tests
 */

use Brain\Monkey\Functions;

/**
 * Tests for the dates set when listings are approved or disabled.
 */
class AWPCP_ListingsAPIApprovalTest extends AWPCP_UnitTestCase {

    /**
     * @var \Mockery\MockInterface
     */
    public $attachments;

    /**
     * @var \Mockery\MockInterface
     */
    public $listing_renderer;

    /**
     * @var \Mockery\MockInterface
     */
    public $settings;

    /**
     * @var \Mockery\MockInterface
     */
    public $wordpress;

    /**
     * @var object
     */
    public $listing;

    /**
     * @var array|null
     */
    public $post_data;

    public function setUp(): void {
        parent::setUp();

        $this->attachments      = Mockery::mock( 'AWPCP_Attachments_Collection' );
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->settings         = Mockery::mock( 'AWPCP_Settings_API' );
        $this->wordpress        = Mockery::mock( 'AWPCP_WordPress' );

        $this->listing = (object) [
            'ID'          => wp_rand() + 1,
            'post_status' => 'pending',
        ];

        $this->post_data = null;

        Functions\when( 'awpcp_request' )->justReturn( null );

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'imagesapprove', false )
            ->andReturn( true );

        $this->listing_renderer->shouldReceive( 'is_public' )->andReturn( false );
        $this->listing_renderer->shouldReceive( 'get_payment_term' )->andReturn( null );

        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->with( $this->listing->ID, '_awpcp_disabled_date' )
            ->andReturn( true );

        $captured_post_data = &$this->post_data;

        $this->redefine(
            'AWPCP_ListingsAPI::update_listing',
            function ( $listing, $post_data ) use ( &$captured_post_data ) {
                $captured_post_data = $post_data;

                return true;
            }
        );

        $this->redefine(
            'AWPCP_ListingsAPI::calculate_start_and_end_dates_using_payment_term',
            function () {
                return [
                    '_awpcp_start_date' => '2026-10-16 00:00:00',
                    '_awpcp_end_date'   => '2026-11-15 00:00:00',
                ];
            }
        );
    }

    public function test_approving_edited_listing_keeps_its_dates() {
        $this->given_pending_listing( true, false );

        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->once()
            ->with( $this->listing->ID, '_awpcp_edited_awaiting_approval' );

        $this->get_test_subject()->enable_listing_without_triggering_actions( $this->listing );

        $this->assertArrayNotHasKey( 'metadata', $this->post_data );
        $this->assertEquals( 'publish', $this->listing->post_status );
    }

    public function test_approving_edited_listing_that_expired_gets_new_dates() {
        $this->given_pending_listing( true, true );

        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->once()
            ->with( $this->listing->ID, '_awpcp_edited_awaiting_approval' );

        $this->get_test_subject()->enable_listing_without_triggering_actions( $this->listing );

        $this->assertEquals( '2026-10-16 00:00:00', $this->post_data['metadata']['_awpcp_start_date'] );
        $this->assertEquals( '2026-11-15 00:00:00', $this->post_data['metadata']['_awpcp_end_date'] );
    }

    public function test_approving_new_listing_gets_new_dates() {
        $this->given_pending_listing( false, false );

        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->once()
            ->with( $this->listing->ID, '_awpcp_edited_awaiting_approval' );

        $this->get_test_subject()->enable_listing_without_triggering_actions( $this->listing );

        $this->assertEquals( '2026-10-16 00:00:00', $this->post_data['metadata']['_awpcp_start_date'] );
        $this->assertEquals( '2026-11-15 00:00:00', $this->post_data['metadata']['_awpcp_end_date'] );
    }

    public function test_enabling_listing_that_is_not_pending_keeps_dates_and_flag() {
        $this->listing->post_status = 'disabled';

        $this->listing_renderer->shouldReceive( 'is_pending_approval' )->andReturn( false );

        $this->wordpress->shouldNotReceive( 'get_post_meta' );
        $this->wordpress->shouldNotReceive( 'delete_post_meta' )
            ->with( $this->listing->ID, '_awpcp_edited_awaiting_approval' );

        $this->get_test_subject()->enable_listing_without_triggering_actions( $this->listing );

        $this->assertArrayNotHasKey( 'metadata', $this->post_data );
    }

    public function test_disabling_listing_clears_edited_flag() {
        Functions\when( 'current_time' )->justReturn( '2026-10-16 00:00:00' );

        $this->wordpress->shouldReceive( 'update_post' )->once();
        $this->wordpress->shouldReceive( 'update_post_meta' )
            ->once()
            ->with( $this->listing->ID, '_awpcp_disabled_date', '2026-10-16 00:00:00' );
        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->once()
            ->with( $this->listing->ID, '_awpcp_edited_awaiting_approval' );

        $this->get_test_subject()->disable_listing_without_triggering_actions( $this->listing );

        $this->assertEquals( 'disabled', $this->listing->post_status );
    }

    /**
     * @param bool $was_edited  Whether the listing is pending because its owner edited it.
     * @param bool $has_expired Whether the listing's end date has passed.
     */
    private function given_pending_listing( $was_edited, $has_expired ) {
        $this->listing_renderer->shouldReceive( 'is_pending_approval' )->andReturn( true );
        $this->listing_renderer->shouldReceive( 'has_expired' )->andReturn( $has_expired );

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $this->listing->ID, '_awpcp_edited_awaiting_approval', true )
            ->andReturn( $was_edited ? '1' : '' );
    }

    private function get_test_subject() {
        return new AWPCP_ListingsAPI(
            null,
            $this->attachments,
            $this->listing_renderer,
            null,
            null,
            $this->settings,
            $this->wordpress
        );
    }
}
