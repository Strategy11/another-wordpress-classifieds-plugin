<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for Listing Fields metabox.
 */
class AWPCP_ListingFieldsMetaboxTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->post = (object) array(
            'ID'          => wp_rand() + 1,
            'post_type'   => 'post_type',
            'post_status' => 'draft',
        );

        $this->post_type             = 'post_type';
        $this->listings_logic        = Mockery::mock( 'AWPCP_Listings_API' );
        $this->form_fields_data      = Mockery::mock( 'AWPCP_FormFields' );
        $this->form_fields_validator = Mockery::mock( 'AWPCP_FormFieldsValidator' );
        $this->form_fields           = null;
        $this->date_form_fields      = Mockery::mock( 'AWPCP_FormFieldsRenderer' );
        $this->media_center          = Mockery::mock( 'AWPCP_MediaCenter' );
        $this->template_renderer     = Mockery::mock( 'AWPCP_TemplateRenderer' );
        $this->wordpress             = Mockery::mock( 'AWPCP_WordPress' );
        $this->listing_authorization = Mockery::mock( 'AWPCP_ListingAuthorization' );
    }

    /**
     * @since 4.0.0
     */
    public function test_render() {
        $data = array(
            'ad_contact_name' => 'John Doe',
        );

        $errors = array();

        $context = array(
            'category' => null,
            'action'   => 'normal',
        );

        $output = 'form-fields';

        $this->form_fields_data = Mockery::mock( 'AWPCP_FormFieldsData' );
        $this->form_fields      = Mockery::mock( 'AWPCP_FormFields' );

        $this->form_fields_data->shouldReceive( 'get_stored_data' )
            ->once()
            ->with( $this->post )
            ->andReturn( $data );

        $this->form_fields->shouldReceive( 'render_fields' )
            ->once()
            ->with( $data, $errors, $this->post, $context )
            ->andReturn( $output );

        $this->date_form_fields->shouldReceive( 'render_fields' )
            ->once()
            ->with( $data, $errors, $this->post, $context )
            ->andReturn( $output );

        $this->listing_authorization->shouldReceive( 'is_current_user_allowed_to_edit_listing_start_date' )
                                    ->andReturn( true );

        $this->media_center->shouldReceive( 'render' )
            ->andReturn( $output );

        $this->template_renderer->shouldReceive( 'render_template' )
            ->andReturn( $output );

        Functions\expect( 'wp_create_nonce' )
            ->with( 'save-listing-fields-metabox' )
            ->andReturn( 'nonce' );

        Functions\expect( 'get_post_meta' )
            ->with( $this->post->ID, '__awpcp_admin_editor_validation_errors', true )
            ->andReturn( [] );

        Functions\expect( 'get_post_meta' )
            ->with( $this->post->ID, '__awpcp_admin_editor_save_errors', true )
            ->andReturn( [] );

        // Verification.
        $this->expectOutputString( $output );

        // Execution.
        $this->get_test_subject()->render( $this->post );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListingFieldsMetabox(
            $this->post_type,
            $this->listings_logic,
            $this->form_fields_data,
            $this->form_fields_validator,
            $this->form_fields,
            $this->date_form_fields,
            $this->media_center,
            $this->template_renderer,
            $this->wordpress,
            $this->listing_authorization
        );
    }

    /**
     * @since 4.0.0
     * @since 4.0.2 Modified to use a Data Provider method.
     *
     * @dataProvider save_data_provider
     */
    public function test_save( $successful_save, $data, $errors ) {
        $_POST['awpcp_listing_fields_nonce'] = 'nonce';

        Functions\when( 'sanitize_key' )->returnArg();

        Functions\expect( 'wp_verify_nonce' )
            ->once()
            ->with( 'nonce', 'save-listing-fields-metabox' )
            ->andReturn( true );

        $this->form_fields_data->shouldReceive( 'get_posted_data' )
            ->andReturn( $data );

        $this->form_fields_validator->shouldReceive( 'get_validation_errors' )
            ->once()
            ->with( $data, $this->post )
            ->andReturn( $errors );

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $this->post->ID, '_awpcp_access_key', true )
            ->andReturn( 'something' );

        $this->listings_logic->shouldReceive( 'update_listing' )
            ->times( $successful_save ? 1 : 0 )
            ->with( $this->post, $data );

        // Expect editor metadata to be deleted on successful save.
        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->times( $successful_save ? 1 : 0 )
            ->with( $this->post->ID, '__awpcp_admin_editor_pending_data' );

        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->times( $successful_save ? 1 : 0 )
            ->with( $this->post->ID, '__awpcp_admin_editor_validation_errors' );

        $this->wordpress->shouldReceive( 'delete_post_meta' )
            ->times( $successful_save ? 1 : 0 )
            ->with( $this->post->ID, '__awpcp_admin_editor_save_errors' );

        // Expect editor metadata to be stored on failed save.
        $this->wordpress->shouldReceive( 'update_post_meta' )
            ->times( $successful_save ? 0 : 1 )
            ->with( $this->post->ID, '__awpcp_admin_editor_pending_data', $data );

        $this->wordpress->shouldReceive( 'update_post_meta' )
            ->times( $successful_save ? 0 : 1 )
            ->with( $this->post->ID, '__awpcp_admin_editor_validation_errors', $errors );

        // Execution.
        $this->get_test_subject()->save( $this->post->ID, $this->post );
    }

    /**
     * @since 4.0.2
     */
    public function save_data_provider() {
        return [
            [
                'successful_save' => true,
                'data'            => [
                    'metadata' => [],
                ],
                'errors'          => [

                    /*
                     * Errors and data for for ad_title and ad_details should be
                     * ignored by the metabox's save() method.
                     */
                    'ad_title'   => 'error message',
                    'ad_details' => 'error message',
                ],
            ],
            [
                'successful_save' => false,
                'data'            => [],
                'errors'          => [
                    'another_field' => 'error message',
                ],
            ],
        ];
    }
}
