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
    public function setUp(): void {
        parent::setUp();

        $this->listing_renderer       = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->roles_and_capabilities = Mockery::mock( 'AWPCP_RolesAndCapabilities' );
        $this->settings               = Mockery::mock( 'AWPCP_Settings' );
        $this->request                = Mockery::mock( 'AWPCP_Request' );
        $this->payments               = null;
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

        wp_get_current_user()->thenReturn( $user );

        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )->andReturn( false );

        $this->assertFalse( $this->get_test_subject()->is_current_user_allowed_to_edit_listing( $listing ) );
    }

    /**
     * @since 4.4.5
     */
    public function test_is_current_user_allowed_to_manage_listing_rejects_auto_draft_without_transaction() {
        $listing = (object) array(
            'ID'          => wp_rand() + 1,
            'post_author' => 0,
            'post_status' => 'auto-draft',
        );

        Functions\when( 'is_user_logged_in' )->justReturn( false );

        $this->assertFalse( $this->get_test_subject()->is_current_user_allowed_to_manage_listing( $listing ) );
    }

    /**
     * @since 4.4.6
     */
    public function test_is_current_user_allowed_to_manage_listing_rejects_auto_draft_with_no_matching_transaction() {
        $listing = (object) array(
            'ID'          => wp_rand() + 1,
            'post_author' => 0,
            'post_status' => 'auto-draft',
        );

        $this->payments = Mockery::mock( 'AWPCP_PaymentsAPI' );
        $this->payments->shouldReceive( 'get_transaction' )->andReturn( null );

        Functions\when( 'is_user_logged_in' )->justReturn( false );

        $this->assertFalse( $this->get_test_subject()->is_current_user_allowed_to_manage_listing( $listing ) );
    }

    /**
     * @since 4.4.6
     */
    public function test_is_current_user_allowed_to_manage_listing_allows_auto_draft_with_valid_transaction() {
        $listing_id = wp_rand() + 1;

        $listing = (object) array(
            'ID'          => $listing_id,
            'post_author' => 0,
            'post_status' => 'auto-draft',
        );

        $transaction = Mockery::mock( 'AWPCP_Payment_Transaction' );
        $transaction->shouldReceive( 'get' )->with( 'ad-id' )->andReturn( $listing_id );

        $this->payments = Mockery::mock( 'AWPCP_PaymentsAPI' );
        $this->payments->shouldReceive( 'get_transaction' )->andReturn( $transaction );

        Functions\when( 'is_user_logged_in' )->justReturn( false );

        $this->assertTrue( $this->get_test_subject()->is_current_user_allowed_to_manage_listing( $listing ) );
    }

    /**
     * @since 4.4.5
     */
    public function test_is_current_user_allowed_to_manage_listing_for_anonymous_users_with_valid_edit_nonce() {
        $listing = (object) array(
            'ID'          => wp_rand() + 1,
            'post_author' => 0,
            'post_status' => 'publish',
        );

        Functions\when( 'is_user_logged_in' )->justReturn( false );
        Functions\when( 'awpcp_get_var' )->justReturn( null );
        Functions\when( 'wp_verify_nonce' )->justReturn( true );

        $this->request->shouldReceive( 'post' )
            ->with( 'edit_nonce', null )
            ->andReturn( 'valid-edit-nonce' );

        $this->assertTrue( $this->get_test_subject()->is_current_user_allowed_to_manage_listing( $listing ) );
    }

    /**
     * @since 4.0.0
     */
    public function get_test_subject() {
        return new AWPCP_ListingAuthorization(
            $this->listing_renderer,
            $this->roles_and_capabilities,
            $this->settings,
            $this->request,
            $this->payments
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
