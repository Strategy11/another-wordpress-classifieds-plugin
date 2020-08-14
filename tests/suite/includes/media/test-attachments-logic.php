<?php

class AWPCP_Test_Attachments_Logic extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->file_types = Phake::mock( 'AWPCP_FileTypes' );
        $this->attachments = Phake::mock( 'AWPCP_Attachments_Collection' );
        $this->wordpress = Phake::mock( 'AWPCP_WordPress' );
    }

    public function test_approve_attachment() {
        $attachment = awpcp_tests_create_attachment();

        $attachments_logic = new AWPCP_Attachments_Logic(
            $this->file_types,
            $this->attachments,
            $this->wordpress
        );

        $attachments_logic->approve_attachment( $attachment );

        Phake::verify( $this->wordpress )->update_post_meta(
            $attachment->ID, '_awpcp_allowed_status', Phake::capture( $allowed_status )
        );

        $this->assertEquals( AWPCP_Attachment_Status::STATUS_APPROVED, $allowed_status );
    }

    public function test_reject_attachment() {
        $attachment = awpcp_tests_create_attachment();

        $attachments_logic = new AWPCP_Attachments_Logic(
            $this->file_types,
            $this->attachments,
            $this->wordpress
        );

        $attachments_logic->reject_attachment( $attachment );

        Phake::verify( $this->wordpress )->update_post_meta(
            $attachment->ID, '_awpcp_allowed_status', Phake::capture( $allowed_status )
        );

        $this->assertEquals( AWPCP_Attachment_Status::STATUS_REJECTED, $allowed_status );
    }

    public function test_delete_uses_wp_delete_attachment() {
        $attachment = awpcp_tests_create_attachment();

        $attachments_logic = new AWPCP_Attachments_Logic(
            $this->file_types,
            $this->attachments,
            $this->wordpress
        );

        $attachments_logic->delete_attachment( $attachment );

        Phake::verify( $this->wordpress )->delete_attachment( $attachment->ID, true );
    }

    public function test_set_attachment_as_featured() {
        $attachment = awpcp_tests_create_attachment();

        $attachment->post_title = 'test-image.png';
        $attachment->post_mime_type = 'image/png';

        Phake::when( $this->file_types )->get_file_types->thenReturn( array(
            'image' => array(
                'png' => array(
                    'mime_types' => array( 'image/png' ),
                    'extensions' => array( 'invalid value added on purpose to force type detection based on mime_type' ),
                ),
            )
        ) );
        Phake::when( $this->attachments )->find_attachments_of_type->thenReturn( array() );

        $attachments_logic = new AWPCP_Attachments_Logic(
            $this->file_types,
            $this->attachments,
            $this->wordpress
        );

        $attachments_logic->set_attachment_as_featured( $attachment );

        Phake::verify( $this->attachments )->find_attachments_of_type( 'image', Phake::ignoreRemaining() );
    }
}
