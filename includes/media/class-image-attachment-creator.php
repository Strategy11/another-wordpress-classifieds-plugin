<?php
/**
 * @package AWPCP\Media
 */

/**
 * Constructor function for Image Attachment Creator.
 */
function awpcp_image_attachment_creator() {
    return new AWPCP_Image_Attachment_Creator(
        awpcp_listing_attachment_creator(),
        awpcp_listings_api(),
        awpcp()->settings
    );
}

/**
 * Creates listing image attachments.
 */
class AWPCP_Image_Attachment_Creator {

    /**
     * @var object
     */
    private $attachment_creator;

    /**
     * @var object
     */
    private $listings_logic;

    /**
     * @var object
     */
    private $settings;

    /**
     * Constructor.
     *
     * @param object $attachment_creator    An instance of Listing Attachment Creator.
     * @param object $listings_logic        An instance of Listings API.
     * @param object $settings              An instance of Settings API.
     */
    public function __construct( $attachment_creator, $listings_logic, $settings ) {
        $this->attachment_creator = $attachment_creator;
        $this->listings_logic     = $listings_logic;
        $this->settings           = $settings;
    }

    /**
     * @param object $listing       An instance of WP_Post.
     * @param object $file_logic    An instance of File Logic.
     */
    public function create_attachment( $listing, $file_logic ) {
        $allowed_status = AWPCP_Attachment_Status::STATUS_APPROVED;

        if ( ! awpcp_current_user_is_moderator() && $this->settings->get_option( 'imagesapprove' ) ) {
            $allowed_status = AWPCP_Attachment_Status::STATUS_AWAITING_APPROVAL;
        }

        $image_attachment = $this->attachment_creator->create_attachment( $listing, $file_logic, $allowed_status );

        if ( $image_attachment && AWPCP_Attachment_Status::STATUS_AWAITING_APPROVAL === $allowed_status ) {
            $this->listings_logic->mark_as_having_images_awaiting_approval( $listing );
        }

        return $image_attachment;
    }
}
