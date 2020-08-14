<?php
/**
 * @package AWPCP\Tests\Plugin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for Listing Authorization.
 */
class AWPCP_TestListingAuthorization extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->listing_renderer       = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->roles_and_capabilities = Mockery::mock( 'AWPCP_RolesAndCapabilities' );
        $this->settings               = Mockery::mock( 'AWPCP_Settings' );
        $this->request                = Mockery::mock( 'AWPCP_Request' );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_current_user_allowed_to_edit_listing_with_anonymous_users_and_anonymous_listings() {
        $user    = (object) array(
            'ID' => 0,
        );
        $listing = (object) array(
            'ID'          => wp_rand() + 1,
            'post_author' => null,
        );

        Functions\when( 'is_user_logged_in' )->justReturn( false );

        $this->request = Phake::mock( 'AWPCP_Request' );

        Phake::when( $this->request )->get_current_user()->thenReturn( $user );

        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )->andReturn( false );

        $this->assertFalse( $this->get_test_subject()->is_current_user_allowed_to_edit_listing( $listing ) );
    }

    /**
     * @since 4.0.0
     */
    public function get_test_subject() {
        return new AWPCP_ListingAuthorization(
            $this->listing_renderer,
            $this->roles_and_capabilities,
            $this->settings,
            $this->request
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_current_user_allowed_to_edit_listing_start_date() {
        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )->andReturn( true );

        $this->assertTrue( $this->get_test_subject()->is_current_user_allowed_to_edit_listing_start_date( null ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_current_user_allowed_to_edit_listing_start_date_for_regular_users() {
        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )->andReturn( false );

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'allow-start-date-modification' )
            ->andReturn( false );

        $this->assertFalse( $this->get_test_subject()->is_current_user_allowed_to_edit_listing_start_date( null ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_current_user_allowed_to_edit_listing_start_date_when_start_date_is_not_set() {
        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )->andReturn( false );

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'allow-start-date-modification' )
            ->andReturn( true );

        $this->listing_renderer->shouldReceive( 'get_start_date' )->andReturn( null );

        $this->assertTrue( $this->get_test_subject()->is_current_user_allowed_to_edit_listing_start_date( null ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_current_user_allowed_to_edit_listing_start_date_when_start_date_is_set_to_a_date_in_the_past() {
        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )->andReturn( false );

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'allow-start-date-modification' )
            ->andReturn( true );
        $this->request->shouldReceive( 'post' )->with( 'mode' )->andReturn( 'edit' );

        $now        = time();
        $start_date = date( 'Y-m-d H:i:s', $now - 3600 );

        $this->listing_renderer->shouldReceive( 'get_start_date' )
            ->andReturn( $start_date );

        Functions\when( 'current_time' )->justReturn( $now );

        $this->assertFalse( $this->get_test_subject()->is_current_user_allowed_to_edit_listing_start_date( null ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_current_user_allowed_to_edit_listing_start_date_when_start_date_is_set_to_a_date_in_the_future() {
        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )->andReturn( false );

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'allow-start-date-modification' )
            ->andReturn( true );

        $now        = time();
        $start_date = date( 'Y-m-d H:i:s', $now + 3600 );

        $this->listing_renderer->shouldReceive( 'get_start_date' )
            ->andReturn( $start_date );

        Functions\when( 'current_time' )->justReturn( $now );
        $this->request->shouldReceive( 'post' )->with( 'mode' )->andReturn( 'edit', 'create' );

        $this->assertTrue( $this->get_test_subject()->is_current_user_allowed_to_edit_listing_start_date( null ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_current_user_allowed_to_edit_listing_end_date() {
        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )->andReturn( true );

        $this->assertTrue( $this->get_test_subject()->is_current_user_allowed_to_edit_listing_end_date( null ) );
    }
}
