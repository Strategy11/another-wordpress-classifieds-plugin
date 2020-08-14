<?php

class AWPCP_Test_Set_Attachment_As_Featured_Ajax_Action extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->ajax_handler = Phake::mock( 'AWPCP_Attachment_Action_Ajax_Handler' );
        $this->attachments_properties = Phake::mock( 'AWPCP_Attachment_Properties' );
        $this->attachments_logic = Phake::mock( 'AWPCP_Attachments_Logic' );
    }

    public function test_constructor() {
        $this->assertInstanceOf( 'AWPCP_Attachment_Action_Ajax_Handler', awpcp_set_attachment_as_featured_ajax_handler() );
    }

    public function test_ajax() {
        $listing = awpcp_tests_create_empty_listing();
        $attachment = awpcp_tests_create_attachment( array( 'post_parent' => $listing->ID ) );
        $ajax_handler = Phake::mock( 'AWPCP_Attachment_Action_Ajax_Handler' );

        Phake::when( $this->attachments_properties )->is_image->thenReturn( true );
        Phake::when( $this->attachments_logic )->set_attachment_as_featured->thenReturn( true );

        $action = new AWPCP_Set_Attachment_As_Featured_Ajax_Action(
            $this->attachments_properties,
            $this->attachments_logic
        );

        $return_value = $action->do_action( $ajax_handler, $attachment, $listing );

        Phake::verify( $this->attachments_logic )->set_attachment_as_featured( $attachment );

        $this->assertTrue( $return_value );
    }
}
