<?php
/**
 * @package AWPCP\Tests\Plugin\Media
 */

use Brain\Monkey\Functions;

/**
 * Test for Media Container Configuration.
 */
class AWPCP_MediaContainerConfigurationTest extends AWPCP_ContainerConfigurationTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        // Constructors functions still required by the constructors of the
        // classes registered in this container.
        Functions\when( 'awpcp_file_types' )->justReturn( null );
        Functions\when( 'awpcp_image_file_validator' )->justReturn( null );
        Functions\when( 'awpcp_image_file_processor' )->justReturn( null );
        Functions\when( 'awpcp_image_attachment_creator' )->justReturn( null );
    }

    /**
     * @since 4.0.0
     */
    public function class_definitions_provider() {
        return [
            [ 'AttachmentsLogic' ],
            [ 'AttachmentsCollection' ],
            [ 'FileHandlersManager' ],

            [ 'ImageFileHandler' ],
        ];
    }

    /**
     * @since 4.0.0
     */
    protected function get_test_subject() {
        return new AWPCP_MediaContainerConfiguration();
    }
}
