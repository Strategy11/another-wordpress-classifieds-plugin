<?php
/**
 * @package AWPCP\Tests\Plugin\Settings
 */

use Brain\Monkey\Functions;

/**
 * Tests for Settings Container Configuration.
 */
class AWPCP_SettingsContainerConfigurationTest extends AWPCP_ContainerConfigurationTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        // XXX: The Settings class does some stuff on the constructor.
        Functions\when( 'get_option' )->justReturn( false );
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
