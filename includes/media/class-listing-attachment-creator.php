<?php

function awpcp_listing_attachment_creator() {
    return new AWPCP_Listing_Attachment_Creator( awpcp_wordpress() );
}

class AWPCP_Listing_Attachment_Creator {

    private $wordpress;

    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    public function create_attachment( $listing, $file_logic, $allowed_status ) {
        $attachment_id = $this->wordpress->handle_media_sideload(
            array(
                'name' => $file_logic->get_real_name(),
                'tmp_name' => $file_logic->get_path(),
            ),
            $listing->ID,
            '' // empty attachment description
        );

        $this->wordpress->update_post_meta( $attachment_id, '_enabled', true );
        $this->wordpress->update_post_meta( $attachment_id, '_allowed_status', $allowed_status );

        return get_post( $attachment_id );
    }
}
