<?php

function awpcp_image_attachment_creator() {
    return new AWPCP_Image_Attachment_Creator(
        awpcp_listing_attachment_creator(),
        awpcp()->settings
    );
}

class AWPCP_Image_Attachment_Creator {

    private $attachment_creator;
    private $settings;

    public function __construct( $attachment_creator, $settings ) {
        $this->attachment_creator = $attachment_creator;
        $this->settings = $settings;
    }

    public function create_attachment( $listing, $file_logic ) {
        if ( ! awpcp_current_user_is_moderator() && $this->settings->get_option( 'imagesapprove' ) ) {
            $allowed_status = AWPCP_Attachment_Status::STATUS_AWAITING_APPROVAL;
        } else {
            $allowed_status = AWPCP_Attachment_Status::STATUS_APPROVED;
        }

        $image_attachment = $this->attachment_creator->create_attachment( $listing, $file_logic, $allowed_status );

        return $image_attachment;
    }
}
