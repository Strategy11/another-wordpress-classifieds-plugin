<?php

class AWPCP_Test_Delete_Attachment_Ajax_Action extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->attachments_logic = Phake::mock( 'AWPCP_Attachments_Logic' );
    }

    /**
     * @large
     */
    public function test_ajax() {
        $listing = awpcp_tests_create_empty_listing();
        $attachment = awpcp_tests_create_attachment( array( 'post_parent' => $listing->ID ) );

        $ajax_handler = Phake::mock( 'AWPCP_Attachment_Action_Ajax_Handler' );

        $handler = new AWPCP_Delete_Attachment_Ajax_Action( $this->attachments_logic );
        $handler->do_action( $ajax_handler, $attachment, $listing );

        Phake::verify( $this->attachments_logic )->delete_attachment( $attachment );
    }
}
