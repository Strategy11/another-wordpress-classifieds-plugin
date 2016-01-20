<?php

function awpcp_attachment_properties() {
    return new AWPCP_Attachment_Properties( awpcp_wordpress() );
}

class AWPCP_Attachment_Properties {

    private $wordpress;

    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    public function get_image_url( $attachment, $size ) {
        return $this->wordpress->get_attachment_image_url( $attachment->ID, "awpcp-$size" );
    }
}
