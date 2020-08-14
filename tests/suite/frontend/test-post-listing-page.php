<?php

use function Patchwork\redefine;

class AWPCP_Test_Post_Listing_Page extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->transaction = Phake::mock( 'AWPCP_Payment_Transaction' );

        $this->attachments = Phake::mock( 'AWPCP_Attachments_Collection' );
        $this->listing_upload_limits = Phake::mock( 'AWPCP_ListingUploadLimits' );
        $this->authorization = Phake::mock( 'AWPCP_ListingAuthorization' );
        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->listings_logic = Phake::mock( 'AWPCP_ListingsAPI' );
        $this->listings = Phake::mock( 'AWPCP_ListingsCollection' );
        $this->payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        $this->template_renderer = Phake::mock( 'AWPCP_Template_Renderer' );
        $this->wordpress = Phake::mock( 'AWPCP_WordPress' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
        $this->request = Phake::mock( 'AWPCP_Request' );
    }

    public function test_details_step() {
        $current_action = 'details';

        awpcp()->settings->set_or_update_option( 'requireuserregistration', true );
        awpcp()->settings->set_or_update_option( 'pay-before-place-ad', true );

        $this->login_as_subscriber();

        Phake::when( $this->request )->post( 'step', Phake::ignoreRemaining() )->thenReturn( $current_action );
        Phake::when( $this->payments )->get_transaction->thenReturn( $this->transaction );
        Phake::when( $this->transaction )->get( 'context' )->thenReturn( 'place-ad' );

        $page = new AWPCP_Place_Ad_Page(
            null, null,
            null,
            null,
            $this->authorization,
            null,
            null,
            $this->listings,
            $this->payments,
            $this->template_renderer,
            null,
            $this->request
        );
        $content = $page->dispatch();

        Phake::verify( $this->template_renderer )->render_page_template(
            Phake::capture( $page ),
            Phake::capture( $container_template ),
            Phake::capture( $content_template ),
            Phake::capture( $params )
        );
    }

    /**
     * @large
     */
    public function test_save_details_step() {
        $this->pause_filter( 'awpcp-before-save-listing' );
        $this->pause_filter( 'awpcp-save-ad-details' );

        $current_action = 'save-details';

        awpcp()->settings->set_or_update_option( 'requireuserregistration', true );
        awpcp()->settings->set_or_update_option( 'pay-before-place-ad', true );
        awpcp()->settings->set_or_update_option( 'captcha-enabled-in-place-listing-form', false );

        $this->login_as_subscriber();

        $listing = awpcp_tests_create_empty_listing();
        $payment_term = Phake::mock( 'AWPCP_Fee' );

        Phake::when( $this->request )->post( 'step', Phake::ignoreRemaining() )->thenReturn( $current_action );
        Phake::when( $this->request )->all_post_params()->thenReturn( array(
            'ad_category' => array( 1, 2 ),
            'ad_title' => 'Test Listing',
            'ad_details' => 'Test Content',
            'ad_contact_name' => 'John Doe',
            'ad_contact_email' => 'awpcp-john@sharklasers.com',
            'terms-of-service' => true,
        ) );

        Phake::when( $this->payments )->get_transaction->thenReturn( $this->transaction );
        Phake::when( $this->payments )->get_transaction_payment_term->thenReturn( $payment_term );
        Phake::when( $this->payments )->get_payment_term->thenReturn( $payment_term );
        Phake::when( $this->transaction )->get( 'context' )->thenReturn( 'place-ad' );
        Phake::when( $this->transaction )->get( 'payment-term-type' )->thenReturn( 'fee' );
        Phake::when( $this->transaction )->get( 'payment-term-id' )->thenReturn( 1 );
        Phake::when( $this->wordpress )->insert_post->thenReturn( $listing->ID );
        Phake::when( $this->listings_logic )->get_ad_alerts->thenReturn( array() );
        Phake::when( $this->listings_logic )->create_listing->thenReturn( $listing );
        Phake::when( $this->listing_upload_limits )->get_listing_upload_limits->thenReturn( array() );
        Phake::when( $this->listing_renderer )->get_payment_term->thenReturn( $payment_term );

        $page = new AWPCP_Place_Ad_Page(
            null, null,
            $this->attachments,
            $this->listing_upload_limits,
            $this->authorization,
            $this->listing_renderer,
            $this->listings_logic,
            $this->listings,
            $this->payments,
            $this->template_renderer,
            $this->wordpress,
            $this->request
        );

        $content = $page->dispatch();

        Phake::verify( $this->transaction, Phake::atLeast( 1 ) )->save();
        Phake::verify( $this->listings_logic )->update_listing( $listing, Phake::capture( $listing_data ) );

        $this->assertInternalType( 'int', $listing_data['terms'][ AWPCP_CATEGORY_TAXONOMY ][0] );
    }

    /**
     * @large
     */
    public function test_upload_files_step() {
        $this->login_as_subscriber();

        $listing = awpcp_tests_create_empty_listing();
        $current_action = 'upload-images';
        $media_manager_nonce = wp_create_nonce( 'awpcp-manage-listing-media-' . $listing->ID );
        $media_uploader_nonce = wp_create_nonce( 'awpcp-upload-media-for-listing-' . $listing->ID );

        Phake::when( $this->request )->post( 'step', Phake::ignoreRemaining() )->thenReturn( $current_action );
        Phake::when( $this->request )->post( 'submit-no-images', false )->thenReturn( false );

        Phake::when( $this->payments )->get_transaction->thenReturn( $this->transaction );
        Phake::when( $this->transaction )->get( 'context' )->thenReturn( 'place-ad' );
        Phake::when( $this->listings )->get->thenReturn( $listing );
        Phake::when( $this->listing_upload_limits )->get_listing_upload_limits->thenReturn( array(
            'image' => array(
                'allowed_file_count' => 1,
                'uploaded_file_count' => 0,
            ),
        ) );
        Phake::when( $this->listings_logic )->get_ad_alerts->thenReturn( array() );

        $page = Phake::partialMock( 'AWPCP_Place_Ad_Page',
            null, null,
            $this->attachments,
            $this->listing_upload_limits,
            $this->authorization,
            $this->listing_renderer,
            $this->listings_logic,
            $this->listings,
            $this->payments,
            $this->template_renderer,
            $this->wordpress,
            $this->request
        );

        Phake::when( $page )->get_images_config->thenReturn( array( 'images_allowed' => 3 ) );
        Phake::when( $page )->should_show_upload_files_step->thenReturn( true );

        $content = $page->dispatch();

        Phake::verify( $this->attachments )->find_attachments( array( 'post_parent' => $listing->ID ) );
        Phake::verify( $this->template_renderer )->render_page_template(
            $page, Phake::capture( $page_template ), Phake::capture( $container_template ), Phake::capture( $content_params )
        );

        $this->assertEquals( $media_manager_nonce, $content_params['media_manager_configuration']['nonce'] );
        $this->assertEquals( $listing->ID, $content_params['media_uploader_configuration']['listing_id'] );
        $this->assertEquals( $media_uploader_nonce, $content_params['media_uploader_configuration']['nonce'] );
    }

    public function test_finish_step() {
        $listing = awpcp_tests_create_listing();
        $listing->post_title = 'Whaat?';

        Phake::when( $this->payments )->get_transaction->thenReturn( $this->transaction );
        Phake::when( $this->listings )->get->thenReturn( $listing );
        Phake::when( $this->transaction )->is_completed->thenReturn( false );
        Phake::when( $this->listings_logic )->get_ad_alerts->thenReturn( array() );

        $page = new AWPCP_Place_Ad_Page(
            null, null,
            $this->attachments,
            $this->listing_upload_limits,
            $this->authorization,
            $this->listing_renderer,
            $this->listings_logic,
            $this->listings,
            $this->payments,
            $this->template_renderer,
            $this->wordpress,
            $this->request
        );

        $page->finish_step();

        Phake::verify( $this->payments )->set_transaction_status_to_completed( Phake::anyParameters() );
    }

    public function test_finish_step_template() {
        awpcp()->settings->set_or_update_option( 'seofriendlyurls', true );
        update_option( 'permalink_structure', '/%postname%/' );

        $listing = (object) array(
            'ID' => 10,
            'post_title' => 'Test Title',
        );

        redefine( 'showad', function( $listing_id ) use ( $listing ) {
            $this->assertEquals( $listing->ID, $listing_id );
        } );

        $template = AWPCP_DIR . '/frontend/templates/page-place-ad-finish-step.tpl.php';
        $params = array(
            'edit' => false,
            'messages' => array(),
            'ad' => $listing,
        );

        $template_renderer = awpcp_template_renderer();
        $output = $template_renderer->render_template( $template, $params );
    }

    public function test_cr_characters_are_not_counted_in_listing_details() {
        $this->login_as_subscriber();

        $this->pause_filter( 'awpcp-save-ad-details' );
        $this->pause_filter( 'awpcp_menu_items' );

        $listing_id = rand() + 1;
        $posted_details = "This is\r\nfake content.";
        $expected_details = "This is\nfa";

        $all_post_params = array(
            'ad_id' => null,
            'adterm_id' => null,
            'ad_category' => null,//1,
            'ad_title' => "This is a fake title",
            'ad_details' => $posted_details,
            'ad_contact_name' => 'Buster',
            'ad_contact_phone' => '555-5555',
            'ad_contact_email' => 'buster@example.org',
            'ad_item_price' => null,
            'websiteurl' => 'https://example.org',
            'is_featured_ad' => null,
            'regions' => array(),
            'user_id' => null,
        );

        $characters_allowed = array(
            'characters_allowed_in_title' => 100,
            'remaining_characters_in_title' => null,
            'characters_allowed' => strlen( $expected_details ),
            'remaining_characters' => null,
        );

        $payment_term = new AWPCP_Fee();
        $payment_term->save();

        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );
        $page = Phake::partialMock( 'AWPCP_Place_Ad_Page',
            null,
            null,
            null,
            null,
            null,
            null,
            $this->listings_logic,
            null,
            $this->payments,
            $this->template_renderer,
            null,
            $this->request
        );

        $transaction->id = rand() + 1;

        Phake::when( $this->payments )->get_or_create_transaction->thenReturn( $transaction );
        Phake::when( $this->payments )->get_transaction_payment_term->thenReturn( $payment_term );

        Phake::when( $this->request )->post( 'step', Phake::ignoreRemaining() )->thenReturn( 'save-details' );
        Phake::when( $this->request )->all_post_params->thenReturn( $all_post_params );

        Phake::when( $this->settings )->get_option( 'pay-before-place-ad' )->thenReturn( false );

        Phake::when( $transaction )->get( 'context' )->thenReturn( 'place-ad' );
        Phake::when( $transaction )->get( 'payment-term-id' )->thenReturn( $payment_term->id );
        Phake::when( $transaction )->get( 'payment-term-type' )->thenReturn( $payment_term->type );

        Phake::when( $page )->get_settings->thenReturn( $this->settings );
        Phake::when( $page )->get_characters_allowed->thenReturn( $characters_allowed );
        Phake::when( $page )->validate_details->thenReturn( true );
        Phake::when( $page )->get_regions_allowed->thenReturn( 1 );
        Phake::when( $page )->should_show_upload_files_step->thenReturn( true );
        Phake::when( $page )->upload_images_step->thenReturn( null );

        Phake::when( $this->listings_logic )->create_listing->thenReturn( (object) array( 'ID' => $listing_id ) );

        $page->dispatch( 'save-details' );

        Phake::verify( $transaction )->set( 'ad-id', $listing_id );
        Phake::verify( $this->listings_logic )->update_listing( Phake::capture( $listing ), Phake::capture( $listing_data ) );

        $this->assertEquals( $listing_id, $listing->ID );
        $this->assertEquals( $expected_details, $listing_data['post_fields']['post_content'] );
    }
}
