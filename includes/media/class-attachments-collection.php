<?php

function awpcp_attachments_collection() {
    return new AWPCP_Attachments_Collection( awpcp_wordpress() );
}

class AWPCP_Attachments_Collection {

    private $wordpress;

    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    public function get( $attachment_id ) {
        return $this->wordpress->get_post( $attachment_id );
    }

    public function get_featured_attachment_of_type( $type, $query = array() ) {
        $query = $this->make_attachments_of_type_query( $type, $query );
        $query = $this->make_featured_attachment_query( $query );

        $attachments = $this->find_attachments( $query );

        return array_shift( $attachments );
    }

    private function make_featured_attachment_query( $query ) {
        $query['posts_per_page'] = 1;

        $query['meta_query'][] = array(
            'key' => '_featured',
            'value' => true,
            'comparator' => '=',
            'type' => 'BINARY',
        );

        return $query;
    }

    public function find_attachments( $query = array() ) {
        $attachments = new WP_Query();
        return $attachments->query( $this->prepare_attachments_query( $query ) );
    }

    private function prepare_attachments_query( $query ) {
        $query['post_type'] = 'attachment';

        if ( ! isset( $query['post_status'] ) ) {
            $query['post_status'] = 'any';
        }

        if ( ! isset( $query['posts_per_page'] ) ) {
            $query['posts_per_page'] = -1;
        }

        return $query;
    }

    public function count_attachments( $query = array() ) {
        $attachments = new WP_Query( $this->prepare_attachments_query( $query ) );
        return $attachments->found_posts;
    }

    public function count_attachments_of_type( $type, $query = array() ) {
        return $this->count_attachments( $this->make_attachments_of_type_query( $type, $query ) );
    }

    private function make_attachments_of_type_query( $type, $query ) {
        if ( $type == 'image' ) {
            $query['post_mime_type'] = awpcp_get_image_mime_types();
        } else {
            throw new AWPCP_Exception( sprintf( 'Attachment type not supported: %s', $type ) );
        }

        return $query;
    }

    public function find_visible_attachments( $query = array() ) {
        return $this->find_attachments( $this->make_visible_attachments_query( $query ) );
    }

    private function make_visible_attachments_query( $query ) {
        $query['meta_query'][] = array(
            'key' => '_enabled',
            'value' => true,
            'compare' => '=',
            'type' => 'BINARY'
        );

        $query['meta_query'][] = array(
            'key' => '_allowed_status',
            'value' => AWPCP_Attachment_Status::STATUS_APPROVED,
            'compare' => '=',
            'type' => 'CHAR'
        );

        return $query;
    }

    public function find_attachments_awaiting_approval( $query = array() ) {
        return $this->find_attachments( $this->make_attachments_awaiting_approval_query( $query ) );
    }

    private function make_attachments_awaiting_approval_query( $query ) {
        $query['meta_query'][] = array(
            'key' => '_allowed_status',
            'value' => AWPCP_Attachment_Status::STATUS_AWAITING_APPROVAL,
        );

        return $query;
    }

    public function find_attachments_of_type( $type, $query = array() ) {
        return $this->find_attachments( $this->make_attachments_of_type_query( $type, $query ) );
    }

    public function find_attachments_of_type_awaiting_approval( $type, $query = array() ) {
        return $this->find_attachments_awaiting_approval( $this->make_attachments_of_type_query( $type, $query ) );
    }
}
