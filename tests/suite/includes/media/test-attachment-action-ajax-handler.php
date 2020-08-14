<?php

class AWPCP_Test_Attachment_Action_Ajax_Handler extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->attachment_action = Phake::mock( 'AWPCP_Attachment_Ajax_Action' );
        $this->attachments = Phake::mock( 'AWPCP_Attachments_Collection' );
        $this->listings = Phake::mock( 'AWPCP_ListingsCollection' );
        $this->request = Phake::mock( 'AWPCP_Request' );
        $this->response = Phake::mock( 'AWPCP_AjaxResponse' );
    }

    public function test_constructor() {
        $handler = awpcp_attachment_action_ajax_handler( null );
        $this->assertInstanceOf( 'AWPCP_Attachment_Action_Ajax_Handler', $handler );
    }

    /**
     * @large
     */
    public function test_ajax() {
        $listing = awpcp_tests_create_empty_listing();
        $attachment = awpcp_tests_create_attachment( array( 'post_parent' => $listing->ID ) );
        $nonce = wp_create_nonce( 'awpcp-manage-listing-media-' . $listing->ID );

        Phake::when( $this->listings )->get->thenReturn( $listing );
        Phake::when( $this->attachments )->get->thenReturn( $attachment );
        Phake::when( $this->request )->post( 'nonce' )->thenReturn( $nonce );

        $handler = new AWPCP_Attachment_Action_Ajax_Handler(
            $this->attachment_action,
            $this->attachments,
            $this->listings,
            $this->request,
            $this->response
        );

        $handler->ajax();

        Phake::verify( $this->attachment_action )->do_action( Phake::anyParameters() );
    }
}
