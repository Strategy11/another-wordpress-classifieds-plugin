<?php

function awpcp_listing_upload_limits() {
    return new AWPCP_ListingUploadLimits( awpcp_listings_collection(), awpcp_payments_api(), awpcp()->settings );
}

class AWPCP_ListingUploadLimits {

    private $listings;
    private $payments;
    private $settings;

    public function __construct( $listings, $payments, $settings ) {
        $this->listings = $listings;
        $this->payments = $payments;
        $this->settings = $settings;
    }

    public function can_add_file_to_listing( $listing_id, $file ) {
        $listing = $this->listings->get( $listing_id );
        $limits = $this->get_upload_limits_by_file_type( $listing );

        $can_add_file = true;
        foreach ( $limits as $file_type => $type_limits ) {
            if ( in_array( $file->get_mime_type(), $type_limits['mime_types'] ) ) {
                $can_add_file = $type_limits['allowed'] > $type_limits['uploaded'];
                break;
            }
        }

        // TODO: do we really need this filter?
        return apply_filters( 'awpcp-can-add-file-to-listing', $can_add_file, $listing, $limits );
    }

    private function get_upload_limits_by_file_type( $listing ) {
        $payment_term = $this->payments->get_ad_payment_term( $listing );

        return apply_filters(
            'awpcp-listing-upload-limits-by-file-type',
            array( 'images' => $this->get_upload_limits_for_images( $listing, $payment_term ) ),
            $listing,
            $payment_term
        );
    }

    private function get_upload_limits_for_images( $listing, $payment_term ) {
        if ( $payment_term && $payment_term->images ) {
            $images_allowed = $payment_term->images;
        } else {
            $images_allowed = $this->settings->get_option( 'imagesallowedfree', 0 );
        }

        return array(
            'mime_types' => $this->settings->get_runtime_option( 'image-mime-types' ),
            'allowed' => $images_allowed,
            'uploaded' => $listing->count_image_files(),
        );
    }
}
