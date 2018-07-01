<?php
/**
 * @package AWPCP\Media
 */

/**
 * Constructor for Listing Attachment Creator.
 */
function awpcp_listing_attachment_creator() {
    return new AWPCP_Listing_Attachment_Creator( awpcp_wordpress() );
}

/**
 * Service used to create attachments associated with listings.
 */
class AWPCP_Listing_Attachment_Creator {

    /**
     * @var WordPress
     */
    private $wordpress;

    /**
     * Constructor.
     */
    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    /**
     * Creates an attachment associated with the given listing.
     */
    public function create_attachment( $listing, $file_logic, $allowed_status = AWPCP_Attachment_Status::STATUS_APPROVED ) {
        $attachment_id = $this->wordpress->handle_media_sideload(
            array(
                'name'     => $file_logic->get_real_name(),
                'tmp_name' => $file_logic->get_path(),
            ),
            $listing->ID,
            null // If not null, it will overwrite the post's title.
        );

        $this->wordpress->update_post_meta( $attachment_id, '_awpcp_enabled', true );
        $this->wordpress->update_post_meta( $attachment_id, '_awpcp_allowed_status', $allowed_status );

        return get_post( $attachment_id );
    }
}
