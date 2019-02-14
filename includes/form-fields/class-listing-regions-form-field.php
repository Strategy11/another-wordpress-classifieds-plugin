<?php

function awpcp_listing_regions_form_field( $slug ) {
    return new AWPCP_ListingRegionsFormField(
        $slug,
        awpcp_listing_renderer(),
        awpcp_payments_api(),
        awpcp()->settings
    );
}

class AWPCP_ListingRegionsFormField extends AWPCP_FormField {

    private $region_selector;
    private $listing_renderer;
    private $payments;
    private $settings;

    public function __construct( $slug, $listing_renderer, $payments, $settings ) {
        parent::__construct( $slug );

        $this->listing_renderer = $listing_renderer;
        $this->payments = $payments;
        // $this->region_selector = $region_selector;
        $this->settings = $settings;
    }

    public function get_name() {
        return _x( 'Regions', 'listing form field', 'another-wordpress-classifieds-plugin' );
    }

    protected function is_read_only() {
        if ( awpcp_current_user_is_moderator() ) {
            return false;
        }

        if ( $this->settings->get_option( 'allow-regions-modification' ) ) {
            return false;
        }

        // ugly hack to figure out if we are editing or creating a list...
        if ( $transaction = $this->payments->get_transaction() ) {
            return false;
        }

        return true;
    }

    public function render( $value, $errors, $listing, $context ) {
        $options = array(
            'showTextField' => true,
            'maxRegions' => $this->get_allowed_regions_for_listing( $listing ),
            'disabled' => $this->is_read_only(),
        );

        $region_selector = awpcp_multiple_region_selector( $value, $options );

        return $region_selector->render( 'details', array(), $errors );
    }

    private function get_allowed_regions_for_listing( $listing ) {
        if ( is_object( $listing ) ) {
            $payment_term = $this->listing_renderer->get_payment_term( $listing );
        } else if ( $transaction = $this->payments->get_transaction() ) {
            $payment_term = $this->payments->get_transaction_payment_term( $transaction );
        } else {
            $payment_term = null;
        }

        if ( ! is_null( $payment_term ) ) {
            $allowed_regions = $payment_term->get_regions_allowed();
        } else {
            $allowed_regions = 0;
        }

        return $allowed_regions;
    }
}
