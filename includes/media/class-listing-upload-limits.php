<?php

function awpcp_listing_upload_limits() {
    return new AWPCP_ListingUploadLimits( awpcp_payments_api(), awpcp()->settings );
}

class AWPCP_ListingUploadLimits {

    private $payments;
    private $settings;

    public function __construct( $payments, $settings ) {
        $this->payments = $payments;
        $this->settings = $settings;
    }

    public function can_add_file_to_listing( $listing, $file ) {
        $limits = $this->get_listing_upload_limits( $listing );

        $can_add_file = false;
        foreach ( $limits as $file_type => $type_limits ) {
            if ( in_array( $file->get_mime_type(), $type_limits['mime_types'] ) ) {
                $can_add_file = $type_limits['allowed_file_count'] > $type_limits['uploaded_file_count'];
                break;
            }
        }

        // TODO: do we really need this filter?
        return apply_filters( 'awpcp-can-add-file-to-listing', $can_add_file, $listing, $limits );
    }

    public function get_listing_upload_limits( $listing ) {
        $payment_term = $this->payments->get_ad_payment_term( $listing );

        return apply_filters(
            'awpcp-listing-upload-limits-by-file-type',
            array( 'images' => $this->get_upload_limits_for_images( $listing, $payment_term ) ),
            $listing,
            $payment_term
        );
    }

    public function get_listing_upload_limits_by_file_type( $listing, $file_type ) {
        $upload_limits = $this->get_listing_upload_limits( $listing );

        if ( isset( $upload_limits[ $file_type ] ) ) {
            return $upload_limits[ $file_type ];
        } else {
            return array(
                'mime_types' => array(),
                'extensions' => array(),
                'allowed_file_count' => 0,
                'uploaded_file_count' => 0,
                'min_file_size' => 0,
                'max_file_size' => 0,
            );
        }
    }

    private function get_upload_limits_for_images( $listing, $payment_term ) {
        if ( $payment_term && $payment_term->images ) {
            $images_allowed = $payment_term->images;
        } else {
            $images_allowed = $this->settings->get_option( 'imagesallowedfree', 0 );
        }

        $mime_types = $this->settings->get_runtime_option( 'image-mime-types' );
        $extensions = array();

        foreach ( $mime_types as $mime_type ) {
            $extensions[] = str_replace( 'image/', '', $mime_type );
        }

        return array(
            'mime_types' => $mime_types,
            'extensions' => $extensions,
            'allowed_file_count' => $images_allowed,
            'uploaded_file_count' => $listing->count_image_files(),
            'min_file_size' => $this->settings->get_option( 'minimagesize' ),
            'max_file_size' => $this->settings->get_option( 'maximagesize' ),
            'min_image_width' => $this->settings->get_option( 'imgminwidth' ),
            'min_image_height' => $this->settings->get_option( 'imgminheight' ),
        );
    }
}
