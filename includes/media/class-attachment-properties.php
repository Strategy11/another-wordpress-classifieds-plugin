<?php

function awpcp_attachment_properties() {
    return new AWPCP_Attachment_Properties( awpcp_wordpress() );
}

class AWPCP_Attachment_Properties {

    private $wordpress;

    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    public function is_enabled( $attachment ) {
        return $this->wordpress->get_post_meta( $attachment->ID, '_awpcp_enabled', true );
    }

    public function is_featured( $attachment ) {
        return $this->wordpress->get_post_meta( $attachment->ID, '_awpcp_featured', true );
    }

    public function get_allowed_status( $attachment ) {
        return $this->wordpress->get_post_meta( $attachment->ID, '_awpcp_allowed_status', true );
    }

    public function is_awaiting_approval( $attachment ) {
        return $this->get_allowed_status( $attachment ) == AWPCP_Attachment_Status::STATUS_AWAITING_APPROVAL;
    }

    public function is_image( $attachment ) {
        return in_array( $attachment->post_mime_type, awpcp_get_image_mime_types() );
    }

    public function get_image_url( $attachment, $size ) {
        return $this->wordpress->get_attachment_image_url( $attachment->ID, "awpcp-$size" );
    }

    public function get_icon_url( $attachment ) {
        $src = $this->wordpress->get_attachment_image_src( $attachment->ID, "awpcp-thumbnail", true );
        return is_array( $src ) ? $src[0] : null;
    }

    public function get_image( $attachment, $size, $ah, $attributes ) {
        return $this->wordpress->get_attachment_image( $attachment->ID, "awpcp-$size", $ah, $attributes );
    }
}
