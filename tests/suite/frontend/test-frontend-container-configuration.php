<?php
/**
 * @package AWPCP\Tests\Plugin\Frontend
 */

use Brain\Monkey\Functions;

/**
 * Tests for main Container Configuration class.
 */
class AWPCP_FrontendContainerConfigurationTest extends AWPCP_ContainerConfigurationTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $awpcp = (object) [
            'container' => [
                // Hardcoded as a dependency in SubmitListingPage.
                'TemplateRenderer' => null,
            ],
        ];

        Functions\when( 'awpcp' )->justReturn( $awpcp );
    }

    /**
     * @since 4.0.0
     */
    public function class_definitions_provider() {
        return [
            [ 'Query' ],
            [ 'SubmitListingPage' ],
            [ 'ShowListingPage' ],
        ];
    }

    /**
     * @since 4.0.0
     */
    protected function get_test_subject() {
        return new AWPCP_FrontendContainerConfiguration();
    }
}
