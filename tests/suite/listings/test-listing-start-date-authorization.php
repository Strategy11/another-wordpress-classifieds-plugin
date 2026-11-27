<?php
/**
 * @package AWPCP\Tests
 */

use Brain\Monkey\Functions;

/**
 * Owners may only move a past start date while the listing is being submitted.
 */
class AWPCP_ListingStartDateAuthorizationTest extends AWPCP_UnitTestCase {

    /**
     * @var \Mockery\MockInterface
     */
    public $listing_renderer;

    /**
     * @var \Mockery\MockInterface
     */
    public $roles;

    /**
     * @var \Mockery\MockInterface
     */
    public $settings;

    /**
     * @var \Mockery\MockInterface
     */
    public $request;

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->roles            = Mockery::mock( 'AWPCP_RolesAndCapabilities' );
        $this->settings         = Mockery::mock( 'AWPCP_Settings_API' );
        $this->request          = Mockery::mock( 'AWPCP_Request' );

        $this->request->shouldReceive( 'post' )->andReturnUsing(
            function ( $name ) {
                $params = array(
                    'mode'   => 'create',
                    'action' => 'awpcp_save_listing_information',
                );

                return isset( $params[ $name ] ) ? $params[ $name ] : null;
            }
        );

        $this->roles->shouldReceive( 'current_user_is_moderator' )->andReturn( false );
        $this->settings->shouldReceive( 'get_option' )
            ->with( 'allow-start-date-modification' )
            ->andReturn( true );

        $now = time();

        Functions\when( 'current_time' )->justReturn( $now );

        $this->listing_renderer->shouldReceive( 'get_start_date' )
            ->andReturn( gmdate( 'Y-m-d H:i:s', $now - 86400 ) );
    }

    /**
     * The request claims a create/save, but the listing is already running.
     *
     * @since x.x
     *
     * @dataProvider running_listing_statuses
     *
     * @param string $status Post status of the listing.
     */
    public function test_past_start_date_cannot_be_changed_once_the_listing_was_submitted( $status ) {
        $listing = (object) array(
            'ID'          => 1,
            'post_status' => $status,
        );

        $this->assertFalse( $this->get_test_subject()->is_current_user_allowed_to_edit_listing_start_date( $listing ) );
    }

    /**
     * @since x.x
     */
    public function running_listing_statuses() {
        return array(
            array( 'publish' ),
            array( 'pending' ),
            array( 'disabled' ),
        );
    }

    /**
     * @since x.x
     *
     * @dataProvider submit_flow_statuses
     *
     * @param string $status Post status of the listing.
     */
    public function test_past_start_date_can_be_changed_while_the_listing_is_being_submitted( $status ) {
        $listing = (object) array(
            'ID'          => 1,
            'post_status' => $status,
        );

        $this->assertTrue( $this->get_test_subject()->is_current_user_allowed_to_edit_listing_start_date( $listing ) );
    }

    /**
     * @since x.x
     */
    public function submit_flow_statuses() {
        return array(
            array( 'auto-draft' ),
            array( 'draft' ),
        );
    }

    /**
     * @since x.x
     */
    private function get_test_subject() {
        return new AWPCP_ListingAuthorization(
            $this->listing_renderer,
            $this->roles,
            $this->settings,
            $this->request
        );
    }
}
