<?php
/**
 * @package AWPCP\Tests\Plugin\Settings
 */

/**
 * Tests for Settings Container Configuration.
 */
class AWPCP_SettingsContainerConfigurationTest extends AWPCP_ContainerConfigurationTestCase {

    /**
     * @since 4.0.0
     */
    public function setUp(): void {
        parent::setUp();

        // XXX: The Settings class does some stuff on the constructor.
        WP_Mock::userFunction( 'get_option', [
            'return' => false,
        ] );
    }

    /**
     * @since 4.0.0
     */
    public function class_definitions_provider() {
        return [
            [ 'Settings', [] ],
        ];
    }

    /**
     * @since 4.0.0
     */
    protected function get_test_subject() {
        return new AWPCP_SettingsContainerConfiguration();
    }
}
