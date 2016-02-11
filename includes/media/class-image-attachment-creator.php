<?php

function awpcp_image_attachment_creator() {
    return new AWPCP_Image_Attachment_Creator(
        awpcp_image_dimensions_generator(),
        awpcp_listing_attachment_creator(),
        awpcp()->settings
    );
}

class AWPCP_Image_Attachment_Creator {

    private $image_dimensions_generator;
    private $attachment_creator;
    private $settings;

    public function __construct( $image_dimensions_generator, $attachment_creator, $settings ) {
        $this->image_dimensions_generator = $image_dimensions_generator;
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

        // // TODO: Do we need to calculate dimensions for attachements as well?
        // $this->image_dimensions_generator->set_image_dimensions( $image_attachment );

        return $image_attachment;
    }
}
