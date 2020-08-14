<?php
/**
 * Frontend Tests: Edit Listing Page.
 *
 * @package AnotherWordPressClassifiedsPlugin
 */

use Brain\Monkey\Functions;

/**
 * Tests for Edit Listing Page class.
 */
class AWPCP_Test_Edit_Listing_Page extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->attachments = Phake::mock( 'AWPCP_Attachments_Collection' );
        $this->listing_upload_limits = Phake::mock( 'AWPCP_ListingUploadLimits' );
        $this->authorization = Phake::mock( 'AWPCP_ListingAuthorization' );
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->listings_logic = Phake::mock( 'AWPCP_ListingsAPI' );
        $this->listings         = Mockery::mock( 'AWPCP_ListingsCollection' );
        $this->payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        $this->template_renderer = Phake::mock( 'AWPCP_Template_Renderer' );
        $this->wordpress = Phake::mock( 'AWPCP_WordPress' );
        $this->settings         = Mockery::mock( 'AWPCP_Settings_API' );
        $this->request          = Mockery::mock( 'AWPCP_Request' );
    }

    public function test_constructor() {
        $page = awpcp_edit_listing_page();
        $this->assertInstanceOf( 'AWPCP_EditAdPage', $page );
    }

    /**
     * @large
     */
    public function test_details_step() {
        $listing = awpcp_tests_create_listing();
        $payment_term = Phake::mock( 'AWPCP_Fee' );

        Phake::when( $this->request )->param( 'ad_id', Phake::ignoreRemaining() )->thenReturn( $listing->ID );
        Phake::when( $this->listings )->get->thenThrow( new AWPCP_Exception() );
        Phake::when( $this->listings )->get( $listing->ID )->thenReturn( $listing );
        Phake::when( $this->listing_renderer )->get_payment_term->thenReturn( $payment_term );

        $page = new AWPCP_EditAdPage(
            null,
            null,
            null,
            null,
            null,
            $this->listing_renderer,
            null,
            $this->listings,
            $this->payments,
            $this->template_renderer,
            null,
            $this->request
        );

        $page->details_step();

        Phake::verify( $this->template_renderer )->render_page_template(
            Phake::capture( $page ),
            Phake::capture( $page_template ),
            Phake::capture( $content_template ),
            Phake::capture( $params )
        );

        Phake::verify( $payment_term )->get_regions_allowed();
        Phake::verify( $payment_term, Phake::atLeast( 2 ) )->get_characters_allowed_in_title();

        $this->assertContains( '/frontend/templates/page-place-ad-details-step.tpl.php', $content_template );
    }

    /**
     * @large
     */
    public function test_save_details_step() {
        $this->pause_filter( 'awpcp_before_edit_ad' );
        $this->pause_filter( 'awpcp_edit_ad' );

        add_action( 'awpcp_edit_ad', create_function( '', 'throw new AWPCP_Exception( "ignore me!" );') );

        $this->login_as_administrator();

        $listing = awpcp_tests_create_listing();
        $payment_term = Phake::mock( 'AWPCP_Fee' );

        $listing->post_author = rand() + 1;

        Phake::when( $this->listings )->get->thenReturn( $listing );
        Phake::when( $this->listing_renderer  )->get_payment_term->thenReturn( $payment_term );
        Phake::when( $this->request )->all_post_params()->thenReturn( array(
            'ad_title' => 'Test Listing',
            'ad_details' => 'Test Content',
            'ad_contact_name' => 'John Doe',
            'ad_contact_email' => 'awpcp-john@sharklasers.com',
            'ad_category' => rand() + 1,
            'start_date' => current_time( 'mysql' ),
            'end_date' => awpcp_datetime( 'mysql', current_time( 'timestamp' ) + DAY_IN_SECONDS ),
            'terms-of-service' => true,
        ) );

        $page = new AWPCP_EditAdPage(
            null,
            null,
            null,
            null,
            null,
            $this->listing_renderer,
            $this->listings_logic,
            $this->listings,
            null,
            $this->template_renderer,
            $this->wordpress,
            $this->request
        );

        try {
            $page->save_details_step( null, array() );
        } catch ( AWPCP_Exception $e ) {
            if ( $e->getMessage() != 'ignore me!' ) {
                throw $e;
            }
        }

        Phake::verify( $this->listings_logic )->update_listing( Phake::capture( $listing ), Phake::capture( $post_data ) );

        $this->assertEquals( $listing->post_author, $post_data['post_fields']['post_author'] );
        $this->assertTrue( is_array( $post_data['metadata'] ) );
    }

    /**
     * @since unkwnon
     */
    public function test_access_key_is_not_send_for_incomplete_or_invalid_listings() {
        $this->pause_filter( 'awpcp_menu_items' );

        $first_listing = (object) array();
        $second_listing = (object) array();

        $settings = Phake::mock( 'AWPCP_Settings_API' );

        Phake::when( $settings )->get_option( 'enable-user-panel' )->thenReturn( false );

        Phake::when( $this->listings )->find_listings->thenReturn( array( $first_listing, $second_listing ) );

        Phake::when( $this->listing_renderer )->is_verified( $first_listing )->thenReturn( false );
        Phake::when( $this->listing_renderer )->is_verified( $second_listing )->thenReturn( true );
        Phake::when( $this->listing_renderer )->get_payment_status->thenReturn( AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED );

        Phake::when( $this->request )->post( 'ad_email' )->thenReturn( 'example@example.org' );
        Phake::when( $this->request )->post( 'attempts' )->thenReturn( 0 );

        $page = Phake::partialMock( 'AWPCP_EditAdPage',
            null,
            null,
            null,
            null,
            null,
            $this->listing_renderer,
            null,
            $this->listings,
            null,
            $this->template_renderer,
            null,
            $this->request
        );

        Phake::when( $page )->get_settings->thenReturn( $settings );
        Phake::when( $page )->send_access_keys->thenReturn( true );

        $page->send_access_key_step();

        Phake::verify( $page, Phake::times( 0 ) )->send_access_keys( Phake::anyParameters() );
    }

    /**
     * @since 3.7.7
     */
    public function test_no_errors_are_shown_if_the_form_was_not_submitted() {
        $page = Phake::partialMock( 'AWPCP_EditAdPage' );

        Phake::when( $page )->render->thenReturn( 'nothing!' );

        $page->send_access_key_step();

        Phake::verify( $page )->render( Phake::capture( $template ), Phake::capture( $params ) );

        $this->assertEquals( array(), $params['errors'] );
    }

    /**
     * This was an attempt to write a very simple test for #2178 that failed
     * because the Edit Listing Page class does too much.
     *
     * @since 4.0.0
     */
    public function test_do_send_access_key_step() {
        $awpcp = (object) [
            'container' => [
                'TemplateRenderer' => null,
            ],
        ];

        Functions\when( 'awpcp' )->justReturn( $awpcp );

        $email_address = 'john@example.org';

        $this->request->shouldReceive( 'post' )
            ->with( 'ad_email' )
            ->andReturn( " $email_address " );

        $this->request->shouldReceive( 'post' )
            ->with( 'attempts', 0 )
            ->andReturn( 3 ); // A number greater or equal than 1.

        Functions\expect( 'is_email' )
            ->with( $email_address )
            ->andReturn( true );

        $query_vars_matcher = function( $query_vars ) use ( $email_address ) {
            if ( ! isset( $query_vars['meta_query'][0]['value'] ) ) {
                return false;
            }

            return strcmp( $email_address, $query_vars['meta_query'][0]['value'] ) === 0;
        };

        $this->listings->shouldReceive( 'find_listings' )
            ->once()
            ->withArgs( $query_vars_matcher )
            ->andReturn( [ (object) [] ] );

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'enable-ads-pending-payment' )
            ->andReturn( true );

        $this->listing_renderer->shouldReceive(
            [
                'is_verified'        => true,
                'get_payment_status' => AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED,
            ]
        );

        $arguments = [
            null,
            null,
            null,
            null,
            null,
            null,
            $this->request,
        ];

        $page = Mockery::mock( 'AWPCP_EditListingPage', $arguments )->makePartial();

        $expected_form = [
            'ad_email' => $email_address,
            'attempts' => 3,
        ];

        $page->shouldReceive( 'process_send_access_key_form' )
            ->with( $expected_form )
            ->andReturn( 'process' );

        $page->shouldReceive( 'render_send_access_key_form' )->andReturn( 'render' );

        // Execution and Verification.
        $this->assertEqauls( 'process', $page->do_send_access_key_step() );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_EditListingPage(
            null,
            $this->listing_renderer,
            null,
            $this->listings,
            null,
            $this->settings,
            $this->request
        );
    }
}
