<?php

function awpcp_attachments_collection() {
    return new AWPCP_Attachments_Collection();
}

class AWPCP_Attachments_Collection {

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

    /**
     * TODO: Needs tests!
     */
    public function count_attachments( $query ) {
        $attachments = new WP_Query( $this->prepare_attachments_query( $query ) );
        return $attachments->found_posts;
    }

    public function find_visible_attachments( $query ) {
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
            'key' => '_approved',
            'value' => true,
            'compare' => '=',
            'type' => 'BINARY'
        );

        return $query;
    }
}
