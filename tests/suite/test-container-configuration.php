<?php
/**
 * @package AWPCP\Tests\Plugin
 */

use Brain\Monkey\Functions;

/**
 * Tests for main Container Configuration class.
 */
class AWPCP_ContainerConfigurationTest extends AWPCP_ContainerConfigurationTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $GLOBALS['wpdb'] = (object) [];

        // Required by ModulesManager.
        Functions\when( 'awpcp_upgrade_tasks_manager' )->justReturn( null );
        Functions\when( 'awpcp_licenses_manager' )->justReturn( null );
        Functions\when( 'awpcp_modules_updater' )->justReturn( null );

        // Used by AWPCP_PaymentsAPI's constructor.
        Functions\when( 'is_admin' )->justReturn( false );
    }

    /**
     * @since 4.0.0
     */
    public function class_definitions_provider() {
        return [
            [ 'wpdb' ],

            [ 'Uninstaller' ],
            [ 'ModulesManager' ],

            [ 'Request' ],
            [ 'Payments' ],
            [ 'RolesAndCapabilities' ],
            [ 'UsersCollection' ],
            [ 'EmailFactory' ],
            [ 'HTMLRenderer' ],
            [ 'FormFieldsData' ],
            [ 'ListingDetailsFormFieldsRenderer' ],
            [ 'ListingDateFormFieldsRenderer' ],

            // Media.
            [ 'FileTypes' ],

            // Components.
            [ 'MediaCenterComponent' ],
        ];
    }

    /**
     * @since 4.0.0
     */
    protected function get_test_subject() {
        return new AWPCP_ContainerConfiguration();
    }

    /**
     * @since 4.0.0
     */
    public function teset_akismet_wrapper_factory_definition() {
        $this->test_class_definition(
            'AkismetWrapperFactory',
            $this->get_test_subject()
        );
    }

    /**
     * @since 4.0.0
     */
    public function teset_listing_akismet_data_source_definition() {
        $this->test_class_definition(
            'ListingAkismetDataSource',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingRenderer'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_spam_submitter_definition() {
        $this->test_class_definition(
            'SPAMSubmitter',
            $this->get_test_subject(),
            function( $container ) {
                $container['AkismetWrapperFactory']    = null;
                $container['ListingAkismetDataSource'] = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_template_renderer_definition() {
        $this->test_class_definition(
            'TemplateRenderer',
            $this->get_test_subject()
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_send_listing_to_facebook_helper_definition() {
        Functions\when( 'awpcp_attachment_properties' )->justReturn( null );
        Functions\when( 'awpcp_attachments_collection' )->justReturn( null );
        Functions\when( 'awpcp_facebook_integration' )->justReturn( null );
        Functions\when( 'awpcp' )->justReturn( (object) [ 'settings' => null ] );

        $this->test_class_definition(
            'SendListingToFacebookHelper',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingRenderer'] = null;
                $container['WordPress']       = null;
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_forms_fields_validator_definition() {
        $this->test_class_definition(
            'FormFieldsValidator',
            $this->get_test_subject(),
            function( $container ) {
                $container['Settings'] = null;
            }
        );
    }
}
