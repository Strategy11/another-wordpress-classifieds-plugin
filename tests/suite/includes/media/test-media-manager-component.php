<?php

class AWPCP_Media_Manager_Component extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->attachment_properties = Phake::mock( 'AWPCP_Attachment_Properties' );
        $this->javascript = Phake::mock( 'AWPCP_JavaScript' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
    }

    public function test_render() {
        $attachments = array(
            awpcp_tests_create_attachment(),
        );

        $options = array();

        $component = new AWPCP_MediaManagerComponent(
            $this->attachment_properties,
            $this->javascript,
            $this->settings
        );

        $component->render( $attachments, $options );

        Phake::verify( $this->attachment_properties )->is_image( $attachments[0] );
    }
}
