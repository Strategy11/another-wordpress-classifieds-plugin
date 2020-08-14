<?php

class AWPCP_Test_Attachments_Collection extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->file_types = Phake::mock( 'AWPCP_FileTypes' );
        $this->wordpress = Phake::mock( 'AWPCP_WordPress' );
    }

    public function test_find_attachments() {
        $attachment_id = wp_insert_post(array(
            'post_type' => 'attachment',
        ));

        $attachments = awpcp_attachments_collection()->find_attachments();

        $this->assertEquals( $attachment_id, $attachments[0]->ID );
    }

    public function test_find_visible_attachments_of_type() {
        $image_id = wp_insert_post(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $image_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );
        update_post_meta( $image_id, '_awpcp_enabled', true );

        $pdf_id = wp_insert_post(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'application/pdf',
        ));

        update_post_meta( $pdf_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );
        update_post_meta( $pdf_id, '_awpcp_enabled', true );

        $attachments = awpcp_attachments_collection();

        $images = $attachments->find_visible_attachments_of_type( 'image' );
        $others = $attachments->find_visible_attachments_of_type( 'others' );

        $this->assertEquals( 1, count( $images ) );
        $this->assertEquals( 1, count( $others ) );

        $this->assertEquals( $images[0]->ID, $image_id );
        $this->assertEquals( $others[0]->ID, $pdf_id );
    }

    public function test_count_attachments() {
        wp_insert_post(array(
            'post_type' => 'attachment',
        ));

        wp_insert_post(array(
            'post_type' => 'attachment',
        ));

        $collection = new AWPCP_Attachments_Collection( $this->file_types, $this->wordpress );

        $this->assertEquals( 2, $collection->count_attachments() );
    }

    public function test_count_attachments_of_type() {
        $attachment_id = wp_insert_post(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpg',
        ));

        $attachment_id = wp_insert_post(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'video/mp4',
        ));

        Phake::when( $this->file_types )->get_allowed_file_mime_types_in_group->thenReturn( array( 'image/jpg' ) );

        $collection = new AWPCP_Attachments_Collection( $this->file_types, $this->wordpress );

        $this->assertEquals( 1, $collection->count_attachments_of_type( 'image' ) );
    }

    public function test_get_featured_attachment_of_type() {
        wp_insert_post(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'video/mp4',
        ));

        wp_insert_post(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpg',
        ));

        $featured_attachment_id = wp_insert_post(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $featured_attachment_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );
        update_post_meta( $featured_attachment_id, '_awpcp_enabled', true );
        update_post_meta( $featured_attachment_id, '_awpcp_featured', true );

        $collection = awpcp_attachments_collection();
        $featured_attachment = $collection->get_featured_attachment_of_type( 'image' );

        $this->assertInstanceOf( 'WP_Post', $featured_attachment );
        $this->assertEquals( $featured_attachment_id, $featured_attachment->ID );
    }

    public function test_get_featured_image_when_featured_image_is_available() {
        $listing = awpcp_tests_create_listing();

        $rejected_image_id = wp_insert_post(array(
            'post_type' => 'attachment',
            'post_parent' => $listing->ID,
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $rejected_image_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_REJECTED );
        update_post_meta( $rejected_image_id, '_awpcp_enabled', true );

        $normal_image_id = wp_insert_post(array(
            'post_type' => 'attachment',
            'post_parent' => $listing->ID,
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $normal_image_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );
        update_post_meta( $normal_image_id, '_awpcp_enabled', true );

        $featured_image_id = wp_insert_post(array(
            'post_type' => 'attachment',
            'post_parent' => $listing->ID,
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $featured_image_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );
        update_post_meta( $featured_image_id, '_awpcp_enabled', true );
        update_post_meta( $featured_image_id, '_awpcp_featured', true );

        $collection = awpcp_attachments_collection();
        $featured_image = $collection->get_featured_attachment_of_type( 'image', array( 'post_parent' => $listing->ID ) );

        $this->assertEquals( $featured_image_id, $featured_image->ID );
    }

    public function test_get_featured_image_when_no_featured_image_is_available() {
        $listing = awpcp_tests_create_listing();

        $rejected_image_id = wp_insert_post(array(
            'post_title' => 'rejected image',
            'post_type' => 'attachment',
            'post_parent' => $listing->ID,
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $rejected_image_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_REJECTED );
        update_post_meta( $rejected_image_id, '_awpcp_enabled', true );

        $first_normal_image_id = wp_insert_post(array(
            'post_title' => 'first approved image',
            'post_type' => 'attachment',
            'post_parent' => $listing->ID,
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $first_normal_image_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );
        update_post_meta( $first_normal_image_id, '_awpcp_enabled', true );

        $second_normal_image_id = wp_insert_post(array(
            'post_title' => 'second approved image',
            'post_type' => 'attachment',
            'post_parent' => $listing->ID,
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $second_normal_image_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );
        update_post_meta( $second_normal_image_id, '_awpcp_enabled', true );

        $collection = awpcp_attachments_collection();
        $featured_image = $collection->get_featured_attachment_of_type( 'image', array( 'post_parent' => $listing->ID ) );

        $this->assertEquals( $first_normal_image_id, $featured_image->ID );
    }

    public function test_get_featured_image_when_no_images_are_available() {
        $listing = awpcp_tests_create_listing();

        $rejected_image_id = wp_insert_post(array(
            'post_title' => 'rejected image',
            'post_type' => 'attachment',
            'post_parent' => $listing->ID,
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $rejected_image_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_REJECTED );
        update_post_meta( $rejected_image_id, '_awpcp_enabled', true );

        $normal_image_id = wp_insert_post(array(
            'post_title' => 'first approved image',
            'post_type' => 'attachment',
            'post_parent' => $listing->ID,
            'post_mime_type' => 'image/jpg',
        ));

        update_post_meta( $normal_image_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );

        $collection = awpcp_attachments_collection();
        $featured_image = $collection->get_featured_attachment_of_type( 'image', array( 'post_parent' => $listing->ID ) );

        $this->assertNull( $featured_image );
    }

    public function test_find_visible_attachments() {
        $attachment_id = wp_insert_post(array(
            'post_type' => 'attachment',
        ));

        update_post_meta( $attachment_id, '_awpcp_enabled', true );
        update_post_meta( $attachment_id, '_awpcp_allowed_status', AWPCP_Attachment_Status::STATUS_APPROVED );

        $visible_attachments = awpcp_attachments_collection()->find_visible_attachments();

        $this->assertEquals( 1, count( $visible_attachments ) );
        $this->assertEquals( $attachment_id, $visible_attachments[0]->ID );
    }
}
