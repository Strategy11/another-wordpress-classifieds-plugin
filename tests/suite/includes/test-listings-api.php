<?php
/**
 * @package AWPCP\Tests\Plugin\Listings
 */

use Brain\Monkey\Functions;

/**
 * @group core
 *
 * @SuppressWarnings(PHPMD)
 */
class AWPCP_TestListingsAPI extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->timezone_identifier = date_default_timezone_get();

        // phpcs:ignore WordPress.WP.TimezoneChange.timezone_change_date_default_timezone_set
        date_default_timezone_set( 'UTC' );

        $this->attachments      = Mockery::mock( 'AWPCP_Attachments_Collection' );
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->settings         = Mockery::mock( 'AWPCP_Settings_API' );
        $this->wordpress        = Mockery::mock( 'AWPCP_WordPress' );
    }

    /**
     * @since 4.0.0
     */
    public function teardown() {
        // phpcs:ignore WordPress.WP.TimezoneChange.timezone_change_date_default_timezone_set
        date_default_timezone_set( $this->timezone_identifier );

        parent::teardown();
    }

    /**
     * Handle_email_verification_link should be called if proper query vars are set
     */
    public function test_dispatch_verify_action() {
        $this->markTestSkipped();

        $request = Phake::mock( 'AWPCP_Request' );

        Phake::when( $request )->get_query_var( 'awpcpx' )->thenReturn( true );
        Phake::when( $request )->get_query_var( 'module' )->thenReturn( 'listings' );
        Phake::when( $request )->get_query_var( 'action' )->thenReturn( 'verify' );
        Phake::when( $request )->get_query_var( 'awpcp-module', Phake::ignoreRemaining() )->thenCallParent();
        Phake::when( $request )->get_query_var( 'awpcp-action', Phake::ignoreRemaining() )->thenCallParent();

        $listings = $this->getMockBuilder( 'AWPCP_ListingsAPI' )
            ->setMethods( array( 'handle_email_verification_link' ) )
            ->setConstructorArgs( array( null, null, null, null, $request, null, null, null ) )
            ->getMock();

        $listings->expects( $this->once() )
            ->method( 'handle_email_verification_link' );

        $listings->expects( $this->once() )->method( 'handle_email_verification_link' );

        $listings->dispatch();
    }

    public function test_create_listing() {
        $this->markTestSkipped();

        $listing = awpcp_tests_create_listing();

        Phake::when( $this->wordpress )->insert_post->thenReturn( $listing->ID );

        $listings_logic = $this->get_test_subject();

        $listings_logic->create_listing(
            [
                'post_fields' => array( 'post_content' => 'Test' ),
                'metadata'    => array( '_awpcp_price' => 100 ),
            ]
        );

        Phake::verify( $this->wordpress )->insert_post( Phake::capture( $post_fields ), true );

        $this->assertContains( 'post_content', array_keys( $post_fields ) );

        Phake::verify( $this->wordpress )->update_post_meta( $listing->ID, '_awpcp_most_recent_start_date', Phake::capture( $most_recent_start_date ) );
        Phake::verify( $this->wordpress )->update_post_meta( $listing->ID, '_awpcp_renewed_date', Phake::capture( $renewed_date ) );
        Phake::verify( $this->wordpress )->update_post_meta( $listing->ID, '_awpcp_price', 100 );

        $this->assertNotEmpty( $most_recent_start_date );
        $this->assertEmpty( $renewed_date );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListingsAPI(
            null,
            $this->attachments,
            $this->listing_renderer,
            null,
            null,
            null,
            $this->settings,
            $this->wordpress,
            null
        );
    }


    public function test_update_listing_verified_status() {
        $this->markTestSkipped();

        $this->logout();

        $listing     = awpcp_tests_create_empty_listing();
        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );

        Phake::when( $this->settings )->get_option( 'enable-email-verification' )->thenReturn( true );
        Phake::when( $this->listing_renderer )->is_verified( $listing )->thenReturn( false );
        Phake::when( $transaction )->payment_is_completed()->thenReturn( true );
        Phake::when( $transaction )->payment_is_pending()->thenReturn( false );

        $listings_logic = $this->get_test_subject();

        $listings_logic->update_listing_verified_status( $listing, $transaction );

        Phake::verify( $this->wordpress )->update_post_meta( $listing->ID, '_awpcp_verified', true );
    }

    public function test_consolidate_new_unverified_ad() {
        $this->markTestSkipped();

        $this->pause_filter( 'awpcp-place-ad' );
        $this->pause_filter( 'awpcp_disable_ad' );

        $listing = awpcp_tests_create_empty_listing();

        $transaction      = Phake::mock( 'AWPCP_Payment_Transaction' );
        $listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $wordpress        = Phake::mock( 'AWPCP_WordPress' );

        Phake::when( $listing_renderer )->is_verified( $listing )->thenReturn( false );
        Phake::when( $listing_renderer )->is_disabled( $listing )->thenReturn( false );

        $listings_logic = $this->get_partial_mock( $listing_renderer, $wordpress );

        Phake::when( $listings_logic )->send_verification_email( $listing )->thenReturn( null );

        $listings_logic->consolidate_new_ad( $listing, $transaction );

        Phake::verify( $listings_logic )->send_verification_email( $listing );
        Phake::verify( $listings_logic )->disable_listing( $listing );
        Phake::verify( $transaction )->set( 'ad-consolidated-at', Phake::ignoreRemaining() );
    }

    /**
     * @since 4.0.0
     */
    private function get_partial_mock( $listing_renderer, $wordpress ) {
        return Phake::partialMock(
            'AWPCP_ListingsAPI',
            null,
            null,
            $listing_renderer,
            null,
            null,
            null,
            null,
            $wordpress,
            null
        );
    }

    public function test_consolidate_new_verified_ad() {
        $this->markTestSkipped();

        $this->pause_filter( 'awpcp-place-ad' );

        $listing = (object) array( 'ID' => wp_rand() + 1 );

        $transaction      = Phake::mock( 'AWPCP_Payment_Transaction' );
        $listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $wordpress        = Phake::mock( 'AWPCP_WordPress' );

        Phake::when( $listing_renderer )->is_verified( $listing )->thenReturn( true );
        PHake::when( $listing_renderer )->is_disabled( $listing )->thenReturn( false );

        $listings_logic = $this->get_partial_mock( $listing_renderer, $wordpress );

        Phake::when( $listings_logic )->send_ad_posted_email_notifications( Phake::anyParameters() )->thenReturn( null );

        $listings_logic->consolidate_new_ad( $listing, $transaction );

        Phake::verify( $listings_logic )->send_ad_posted_email_notifications( Phake::anyParameters() );
        Phake::verify( $listings_logic, Phake::times( 0 ) )->disable_listing( $listing );
        Phake::verify( $transaction )->set( 'ad-consolidated-at', Phake::ignoreRemaining() );
    }

    public function test_consolidate_existing_ad_resets_disabled_date_if_ad_should_be_disabled() {
        $this->markTestSkipped();

        $this->pause_filter( 'awpcp_disable_ad' );

        awpcp()->settings->set_or_update_option( 'disable-edited-listings-until-admin-approves', true );

        $listing = awpcp_tests_create_empty_listing();

        $listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $wordpress        = Phake::mock( 'AWPCP_WordPress' );

        $listings_logic = $this->get_partial_mock( $listing_renderer, $wordpress );

        $listings_logic->consolidate_existing_ad( $listing );

        Phake::verify( $listings_logic )->disable_listing( $listing );
        Phake::verify( $wordpress )->delete_post_meta( $listing->ID, '_awpcp_disabled_date' );
    }

    public function test_consolidate_existing_ad_resets_disabled_date_if_ad_was_already_disabled() {
        $this->markTestSkipped();

        awpcp()->settings->set_or_update_option( 'disable-edited-listings-until-admin-approves', true );

        $listing = awpcp_tests_create_empty_listing();

        $listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $wordpress        = Phake::mock( 'AWPCP_WordPress' );

        Phake::when( $listing_renderer )->is_verified( $listing )->thenReturn( true );
        Phake::when( $listing_renderer )->is_disabled( $listing )->thenReturn( true );

        update_post_meta( $listing->ID, '_awpcp_disabled_date', current_time( 'mysql' ) );

        $listings_logic = $this->get_partial_mock( $listing_renderer, $wordpress );

        $listings_logic->consolidate_existing_ad( $listing );

        Phake::verify( $wordpress )->delete_post_meta( $listing->ID, '_awpcp_disabled_date' );
    }

    public function test_consolidate_existing_ad_does_not_resets_disabled_date_if_admin_approval_is_disabled() {
        $this->markTestSkipped();

        $this->login_as_subscriber();

        awpcp_settings_api()->set_or_update_option( 'disable-edited-listings-until-admin-approves', false );

        $listing = awpcp_tests_create_empty_listing();

        $listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $wordpress        = Phake::mock( 'AWPCP_WordPress' );

        Phake::when( $listing_renderer )->is_verified->thenReturn( true );

        // Disabled_date shouldn't be cleared when admin approval is disabled,
        // not even if the listing is already disabled.
        Phake::when( $listing_renderer )->is_disabled->thenReturn( true );

        $listings_logic = $this->get_test_subject();

        $listings_logic->consolidate_existing_ad( $listing );

        Phake::verify( $wordpress, Phake::times( 0 ) )->delete_post_meta( $listing->ID, '_awpcp_disabled_date' );
    }

    public function test_renew_enables_listing_if_listing_is_disabled() {
        $this->markTestSkipped();

        $end_date = awpcp_datetime( 'mysql', current_time( 'timestamp' ) + 24 * 3600 );

        $listing = (object) array( 'ID' => wp_rand() + 1 );

        $listings_logic = $this->get_partial_mock( $listing_renderer, $wordpress );

        Phake::when( $this->listing_renderer )->is_disabled->thenReturn( true );

        Phake::when( $listings_logic )->enable_listing->thenReturn( true );

        $listings_logic->renew_listing( $listing, $end_date );

        Phake::verify( $this->wordpress )->update_post_meta( $listing->ID, '_awpcp_end_date', $end_date );
        Phake::verify( $listings_logic )->enable_listing( $listing );
    }

    public function test_verify_ad() {
        $this->markTestSkipped();

        $now      = current_time( 'timestamp' );
        $tomorrow = $now + 24 * 60 * 60;

        $listing = (object) array( 'ID' => wp_rand() + 1 );

        $payment_term = Phake::mock( 'AWPCP_PaymentTerm' );

        Phake::when( $payment_term )->calculate_end_date->thenReturn( awpcp_datetime( 'mysql', $tomorrow ) );
        Phake::when( $this->listing_renderer )->is_verified->thenReturn( false );
        Phake::when( $this->listing_renderer )->get_payment_term->thenReturn( $payment_term );
        Phake::when( $this->listing_renderer )->is_disabled->thenReturn( true );

        $listings_logic = $this->get_partial_mock( $this->listing_renderer, $this->wordpress );

        Phake::when( $listings_logic )->send_ad_posted_email_notifications->thenReturn( true );
        Phake::when( $listings_logic )->enable_listing_without_triggering_actions->thenReturn( true );

        /* Execution */
        $listings_logic->verify_ad( $listing );

        /* Verification */
        Phake::verify( $this->wordpress )->delete_post_meta( $listing->ID, '_awpcp_verification_needed' );
        Phake::verify( $this->wordpress )->update_post_meta( $listing->ID, '_awpcp_verified', true );
        Phake::verify( $this->wordpress )->update_post_meta( $listing->ID, '_awpcp_verification_date', Phake::capture( $verification_date ) );
        Phake::verify( $this->wordpress )->update_post_meta( $listing->ID, '_awpcp_start_date', Phake::capture( $start_date ) );
        Phake::verify( $this->wordpress )->update_post_meta( $listing->ID, '_awpcp_end_date', Phake::capture( $end_date ) );
        Phake::verify( $listings_logic )->enable_listing_without_triggering_actions( $listing );
        Phake::verify( $listings_logic )->send_ad_posted_email_notifications( $listing );

        $this->assertTrue( strtotime( $verification_date ) >= $now );
        $this->assertTrue( strtotime( $start_date ) >= $now );
        $this->assertTrue( strtotime( $end_date ) > strtotime( $start_date ) );

        $this->assertEquals( $end_date, awpcp_datetime( 'mysql', $tomorrow ) );
    }

    public function test_verify_ad_do_not_verify_ad_twice() {
        $this->markTestSkipped();

        $ad               = awpcp_tests_create_empty_listing();
        $listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $wordpress        = Phake::mock( 'AWPCP_WordPress' );

        Phake::when( $listing_renderer )->is_verified->thenReturn( true );

        $listings_logic = $this->get_test_subject();
        $listings_logic->verify_ad( $ad );

        Phake::verifyNoInteraction( $wordpress );
    }

    /**
     * @dataProvider enable_listing_data_provider
     */
    public function test_enable_listing_new( $old_listing_status, $new_listing_status, $actions_triggered ) {
        $listing = (object) [
            'ID'          => wp_rand(),
            'post_status' => '',
        ];

        $this->listing_renderer->shouldReceive( 'is_public' )
            ->with( $listing )
            ->andReturn( $old_listing_status === 'publish' );

        $this->listing_renderer->shouldReceive( 'is_pending_approval' )
            ->with( $listing )
            ->andReturn( $old_listing_status === 'pending' );

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'imagesapprove', false )
            ->andReturn( false );

        $this->attachments->shouldReceive( 'find_attachments_of_type_awaiting_approval' )
            ->with( 'image', Mockery::any() )
            ->andReturn( [] );

        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->with( $listing->ID, '_awpcp_disabled_date' )
            ->andReturn( true );

        $this->redefine(
            'AWPCP_ListingsAPI::update_listing',
            function( $_listing, $_post_data ) use ( &$post_data ) {
                $post_data = $_post_data;

                return true;
            }
        );

        $listings_logic = $this->get_test_subject();

        // Execution.
        $listings_logic->enable_listing( $listing );

        // Verification.
        $this->assertEquals( $actions_triggered, did_action( 'awpcp_approve_ad' ) );

        $this->assertEquals( $new_listing_status, $post_data['post_fields']['post_status'] );
        $this->assertEquals( $new_listing_status, $listing->post_status );
    }

    /**
     * @since 4.0.0
     */
    public function enable_listing_data_provider() {
        return [
            [ 'draft', 'publish', 1 ],
            [ 'disabled', 'publish', 1 ],
            [ 'publish', '', 0 ],
        ];
    }

    public function test_disable_listing() {
        $this->markTestSkipped();

        $this->pause_filter( 'awpcp_disable_ad' );

        $listings_logic = $this->get_test_subject();
        $listings_logic->disable_listing( $this->listing );

        Phake::verify( $this->wordpress )->update_post( Phake::capture( $post_params ) );

        $this->assertEquals( 'disabled', $post_params['post_status'] );
        $this->assertEquals( 'disabled', $this->listing->post_status );
    }

    /**
     * @since 4.0.0
     */
    public function test_flag_listing() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );

        $this->wordpress->shouldReceive( 'update_post_meta' )
            ->once()
            ->with( $post->ID, '_awpcp_flagged', true )
            ->andReturn( wp_rand() + 1 );

        Functions\expect( 'awpcp_send_listing_was_flagged_notification' )
            ->once()
            ->with( $post );

        $listings_logic = $this->get_test_subject();

        // Execution.
        $listing_flagged = $listings_logic->flag_listing( $post );

        // Verification.
        $this->assertTrue( $listing_flagged );
    }

    /**
     * @since 4.0.0
     */
    public function test_unflag_listing() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $this->wordpress = Mockery::mock( 'AWPCP_WordPress' );

        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->once()
            ->with( $post->ID, '_awpcp_flagged' )
            ->andReturn( true );

        $listings_logic = $this->get_test_subject();

        // Execution.
        $listing_unflagged = $listings_logic->unflag_listing( $post );

        // Verification.
        $this->assertTrue( $listing_unflagged );
    }

    /**
     * @since 4.0.0
     */
    public function test_mark_as_having_images_awaiting_approval() {
        $post = (object) [
            'ID' => wp_rand() + 1,
        ];

        $this->wordpress->shouldReceive( 'update_post_meta' )
            ->once()
            ->with( $post->ID, '_awpcp_has_images_awaiting_approval', true );

        // Execution.
        $this->get_test_subject()->mark_as_having_images_awaiting_approval( $post );
    }

    /**
     * @since 4.0.0
     */
    public function test_send_verification_email() {
        $post = (object) [
            'ID' => wp_rand() + 1,
        ];

        $email_helper = Mockery::mock( 'AWPCP_EmailHelper' );
        $email        = Mockery::mock( 'AWPPC_Email' );

        $awpcp = (object) [
            'container' => [
                'EmailHelper' => $email_helper,
            ],
        ];

        $contact_name  = 'John Doe';
        $contact_email = 'john.doe@example.org';

        Functions\when( 'awpcp_get_blog_name' )->justReturn( 'Test Blog' );
        Functions\when( 'home_url' )->justReturn( 'https://example.org' );
        Functions\when( 'awpcp' )->justReturn( $awpcp );
        Functions\when( 'awpcp_get_email_verification_url' )->justReturn( 'https://example.org' );

        Functions\expect( 'awpcp_format_recipient_address' )
            ->once()
            ->with( $contact_email, $contact_name );

        $this->listing_renderer->shouldReceive(
            [
                'get_contact_name'  => $contact_name,
                'get_contact_email' => $contact_email,
                'get_listing_title' => 'Test Listing',
            ]
        );

        $email_helper->shouldReceive( 'prepare_email_from_template_setting' )
            ->andReturn( $email );

        $email->shouldReceive( 'send' )->andReturn( false );

        // Execution.
        $this->get_test_subject()->send_verification_email( $post );
    }

    /**
     * @since 4.0.0
     *
     * @dataProvider calculate_start_and_end_dates_for_payment_term_data_provider
     */
    public function test_calculate_start_and_end_dates_using_payment_term(
        $payment_term,
        $start_date,
        $start_timestamp,
        $now_date,
        $now_timestamp,
        $expected_start_date,
        $expected_end_date
    ) {
        if ( $payment_term ) {
            $payment_term->shouldReceive( 'calculate_end_date' )
                ->with( $start_timestamp )
                ->andReturn( $expected_end_date );
        }

        Functions\expect( 'current_time' )
            ->with( 'mysql' )
            ->andReturn( $now_date );

        Functions\expect( 'current_time' )
            ->with( 'timestamp' )
            ->andReturn( $now_timestamp );

        $metadata = $this->get_test_subject()->calculate_start_and_end_dates_using_payment_term(
            $payment_term,
            $start_date
        );

        $this->assertEquals( $expected_start_date, $metadata['_awpcp_start_date'] );
        $this->assertEquals( $expected_end_date, $metadata['_awpcp_end_date'] );
    }

    public function calculate_start_and_end_dates_for_payment_term_data_provider() {
        return [
            [
                'payment_term' => Mockery::mock( 'AWPCP_PaymentTerm' ),
                '2019-03-14 02:25:37',
                1552530337,
                '2019-03-22 01:15:19',
                1553217319,
                '2019-03-14 02:25:37',
                '2019-03-21 02:25:37',
            ],
            [
                'payment_term' => Mockery::mock( 'AWPCP_PaymentTerm' ),
                null,
                1553217319,
                '2019-03-22 01:15:19',
                1553217319,
                '2019-03-22 01:15:19',
                '2019-03-29 01:15:19',
            ],
            [
                'payment_term'        => null,
                'start_date'          => null,
                'start_timestamp'     => 1553217319,
                'now_date'            => '2019-03-22 01:15:19',
                'now_timestamp'       => 1553217319,
                'expected_start_date' => '2019-03-22 01:15:19',
                'expected_end_date'   => '2019-03-22 01:15:19',
            ],
        ];
    }

    /**
     * @since 4.0.0
     *
     * @dataProvider fill_default_lisitng_metadata_provider
     */
    public function test_fill_default_listing_metadata( $metadata, $stored, $expected ) {
        $listing = (object) [
            'ID' => wp_rand(),
        ];

        Functions\when( 'get_post_meta' )->justReturn( $stored );
        Functions\when( 'current_time' )->justReturn( $expected['_awpcp_most_recent_start_date'] );
        Functions\when( 'awpcp_getip' )->justReturn( $expected['_awpcp_poster_ip'] );
        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( false );

        Functions\when( 'wp_parse_args' )->alias(
            function( $a, $b ) {
                return array_merge( $b, $a );
            }
        );

        $this->redefine(
            'AWPCP_ListingsAPI::generate_access_key',
            \Patchwork\always( $expected['_awpcp_access_key'] )
        );

        $generated = $this->get_test_subject()->fill_default_listing_metadata(
            $listing,
            $metadata
        );

        $this->assertEquals( $expected, $generated );
    }

    /**
     * @since 4.0.0
     */
    public function fill_default_lisitng_metadata_provider() {
        $expected_metadata = [
            '_awpcp_payment_status'         => 'Unpaid',
            '_awpcp_verification_needed'    => true,
            '_awpcp_most_recent_start_date' => '2019-07-18 16:59:45',
            '_awpcp_renewed_date'           => '',
            '_awpcp_poster_ip'              => '127.0.0.1',
            '_awpcp_is_paid'                => false,
            '_awpcp_is_featured'            => 0,
            '_awpcp_views'                  => 0,
            '_awpcp_access_key'             => 'generated-access-key',
        ];

        return [
            // CASE: The default behavior for entirely new listings.
            [
                [],
                [],
                $expected_metadata,
            ],
            // CASE: The payment term was already set and a associated payment
            // transaction exists.
            [
                [],
                [
                    '_awpcp_payment_status' => [ 'Completed' ],
                    '_awpcp_verified'       => [ true ],
                    '_awpcp_is_paid'        => [ true ],
                ],
                array_diff_key(
                    // Overwrite default expected data with two metadata from
                    // the stored data.
                    array_merge(
                        $expected_metadata,
                        [
                            '_awpcp_payment_status' => 'Completed',
                            '_awpcp_is_paid'        => true,
                        ]
                    ),
                    // However, make sure the generated data doesn't include
                    // the following keys.
                    [
                        '_awpcp_verification_needed' => true,
                    ]
                ),
            ],
        ];
    }

    /**
     * @since 4.0.4
     */
    public function test_send_ad_posted_email_notifications() {
        $listing = (object) [];

        $transaction = null;

        $moderate_listings = true;
        $moderate_images   = false;

        $this->listing_renderer->shouldReceive( 'is_pending_approval' )
            ->with( $listing )
            ->andReturn( true );

        Functions\expect( 'awpcp_send_listing_posted_notification_to_user' )
            ->once()
            ->with( $listing, $transaction, Mockery::any() );

        Functions\expect( 'awpcp_send_listing_posted_notification_to_moderators' )
            ->once()
            ->with( $listing, $transaction, Mockery::any() );

        Functions\expect( 'get_awpcp_option' )
            ->with( 'adapprove' )
            ->andReturn( $moderate_listings );

        Functions\expect( 'get_awpcp_option' )
            ->with( 'imagesapprove' )
            ->andReturn( $moderate_images );

        Functions\expect( 'awpcp_send_listing_awaiting_approval_notification_to_moderators' )
            ->once()
            ->with( $listing, $moderate_listings, $moderate_images );

        // Execution & Verification.
        $this->get_test_subject()->send_ad_posted_email_notifications(
            $listing,
            [],
            $transaction
        );
    }
}
