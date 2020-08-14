<?php
/**
 * @package AWPCP\Tests
 */

/**
 * Unit tests for Remove Listings Attachments Service class.
 */
class AWPCP_RemoveListingAttachmentsServiceTest extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing_post_type = 'awpcp_listing';
        $this->attachments       = Mockery::mock( 'AWPCP_Attachments_Collection' );
        $this->wordpress         = Mockery::mock( 'AWPCP_WordPress' );
    }

    public function test_it_ignores_other_post_types() {
        $post = (object) [
            'ID'        => wp_rand(),
            'post_type' => 'something',
        ];

        $this->wordpress->shouldReceive( 'get_post' )
            ->once()
            ->with( $post->ID )
            ->andReturn( $post );

        $this->wordpress->shouldReceive( 'delete_attachment' )->never();

        $test_subject = $this->get_test_subject();

        $test_subject->enqueue_attachments_to_be_removed( $post->ID );
        $test_subject->remove_attachments( $post->ID );
    }

    private function get_test_subject() {
        return new AWPCP_RemoveListingAttachmentsService(
            $this->listing_post_type,
            $this->attachments,
            $this->wordpress
        );
    }

    public function test_it_remove_attachments() {
        $post = (object) [
            'ID'        => wp_rand(),
            'post_type' => $this->listing_post_type,
        ];

        $this->wordpress->shouldReceive( 'get_post' )
            ->andReturn( $post );

        $attachments_query = [ 'post_parent' => $post->ID ];

        $attachment = (object) [
            'ID' => wp_rand(),
        ];

        $this->attachments->shouldReceive( 'find_uploaded_attachments' )
            ->once()
            ->with( $attachments_query )
            ->andReturn( [ $attachment ] );

        $this->wordpress->shouldReceive( 'delete_attachment' )
            ->once()
            ->with( $attachment->ID );

        $test_subject = $this->get_test_subject();

        $test_subject->enqueue_attachments_to_be_removed( $post->ID );
        $test_subject->remove_attachments( $post->ID );
    }
}
