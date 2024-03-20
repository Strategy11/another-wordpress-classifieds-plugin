<?php
/**
 * @package AWPCP\Tests\Plugin
 */

/**
 * Tests for main Container Configuration class.
 */
class AWPCP_ContainerConfigurationTest extends AWPCP_ContainerConfigurationTestCase {

    /**
     * @since 4.0.0
     */
    public function setUp(): void {
        parent::setUp();

        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $GLOBALS['wpdb'] = (object) [];

        // Required by ModulesManager.
        WP_Mock::userFunction( 'awpcp_upgrade_tasks_manager', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp_licenses_manager', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp_modules_updater', [
            'return' => null,
        ] );

        // Used by AWPCP_PaymentsAPI's constructor.
        WP_Mock::userFunction( 'is_admin', [
            'return' => false,
        ] );
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
        WP_Mock::userFunction( 'awpcp_attachment_properties', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp_attachments_collection', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp_facebook_integration', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp', [
            'return' => (object) [ 'settings' => null ],
        ] );

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
