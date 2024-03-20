<?php
/**
 * @package AWPCP\Tests\Plugin\Media
 */

/**
 * Test for Media Container Configuration.
 */
class AWPCP_MediaContainerConfigurationTest extends AWPCP_ContainerConfigurationTestCase {

    /**
     * @since 4.0.0
     */
    public function setUp(): void {
        parent::setUp();

        // Constructors functions still required by the constructors of the
        // classes registered in this container.
        WP_Mock::userFunction( 'awpcp_file_types', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp_image_file_validator', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp_image_file_processor', [
            'return' => null,
        ] );
        WP_Mock::userFunction( 'awpcp_image_attachment_creator', [
            'return' => null,
        ] );
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
