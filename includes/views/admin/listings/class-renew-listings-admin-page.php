<?php

function awpcp_renew_listings_admin_page() {
    return new AWPCP_RenewListingsAdminPage(
        awpcp_listings_api(),
        awpcp_listing_renderer(),
        awpcp_listings_collection(),
        awpcp_payments_api(),
        awpcp_request()
    );
}

class AWPCP_RenewListingsAdminPage extends AWPCP_ListingActionAdminPage {

    private $listings_logic;
    private $listing_renderer;
    private $payments;

    public $successful = 0;
    public $failed = 0;
    public $errors = array();

    public function __construct( $listings_logic, $listing_renderer, $listings, $payments, $request ) {
        parent::__construct( $listings, $request );

        $this->listings_logic = $listings_logic;
        $this->listing_renderer = $listing_renderer;
        $this->payments = $payments;
    }

    public function dispatch() {
        foreach ( $this->get_selected_listings() as $listing ) {
            $this->try_to_renew_listing( $listing );
        }

        $this->show_results();
    }

    private function try_to_renew_listing( $listing ) {
        try {
            $this->renew_listing( $listing );
        } catch ( AWPCP_Exception $e ) {
            $message = __( 'There was an error trying to renew Ad %s.', 'another-wordpress-classifieds-plugin' );
            $message = sprintf( $message, '<strong>' . $this->listing_renderer->get_listing_title( $listing ) . '</strong>' );

            $this->errors[] = $message . ' ' . $e->format_errors();
            $this->failed = $this->failed + 1;
        }
    }

    private function renew_listing( $listing ) {
        $listing_expired = $this->listing_renderer->has_expired( $listing );
        $listing_is_about_to_expire = $this->listing_renderer->is_about_to_expire( $listing );

        if ( ! $listing_expired && ! $listing_is_about_to_expire ) {
            throw new AWPCP_Exception( __( "The Ad hasn't expired yet and is not about to expire.", 'another-wordpress-classifieds-plugin' ) );
        }

        $term = $this->listing_renderer->get_payment_term( $listing );

        if ( ! is_object( $term ) ) {
            throw new AWPCP_Exception( __( "We couldn't find a valid payment term associated with this Ad.", 'another-wordpress-classifieds-plugin' ) );
        }

        if ( ! $term->ad_can_be_renewed( $listing ) ) {
            throw new AWPCP_Exception( $term->ad_cannot_be_renewed_error( $listing ) );
        }

        $this->listings_logic->renew_listing( $listing );

        awpcp_send_ad_renewed_email( $listing );
        $this->successful = $this->successful + 1;

        // MOVE inside Ad::renew() ?
        do_action( 'awpcp-renew-ad', $listing->ID, null );
    }

    private function show_results() {
        if ( $this->successful == 0 && $this->failed == 0 ) {
            awpcp_flash( __( 'No Ads were selected', 'another-wordpress-classifieds-plugin' ), 'error' );
        } else {
            $success_message = _n( '%d Ad was renewed', '%d Ads were renewed', $this->successful, 'another-wordpress-classifieds-plugin' );
            $success_message = sprintf( $success_message, $this->successful );
            $error_message = sprintf( __('there was an error trying to renew %d Ads', 'another-wordpress-classifieds-plugin'), $this->failed );

            $this->show_bulk_operation_result_message( $this->successful, $this->failed, $success_message, $error_message );
        }

        foreach ( $this->errors as $error ) {
            awpcp_flash( $error, 'error' );
        }
    }
}
