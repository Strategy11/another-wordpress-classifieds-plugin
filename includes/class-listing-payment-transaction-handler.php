<?php
/**
 * @package AWPCP\Payments
 */

// phpcs:disable

function awpcp_listing_payment_transaction_handler() {
    return new AWPCP_ListingPaymentTransactionHandler(
        awpcp_listing_renderer(),
        awpcp_listings_collection(),
        awpcp_listings_api(),
        awpcp_wordpress()
    );
}

/**
 * @SuppressWarnings(PHPMD)
 */
class AWPCP_ListingPaymentTransactionHandler {

    private $listing_renderer;
    private $listings;
    private $listings_logic;
    private $wordpress;

    public function __construct( $listing_renderer, $listings, $listings_logic, $wordpress ) {
        $this->listing_renderer = $listing_renderer;
        $this->listings = $listings;
        $this->listings_logic = $listings_logic;
        $this->wordpress = $wordpress;
    }

    public function transaction_status_updated( $transaction ) {
        $this->process_payment_transaction( $transaction );
    }

    public function process_payment_transaction( $transaction ) {
        if ( $transaction->is_payment_completed() || $transaction->is_completed() ) {
            $this->process_completed_transaction( $transaction );
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function process_completed_transaction( $transaction ) {
        if ( strcmp( $transaction->get( 'context' ), 'place-ad' ) !== 0 ) {
            return;
        }

        if ( ! $transaction->get( 'ad-id' ) ) {
            return;
        }

        try {
            $listing = $this->listings->get( $transaction->get( 'ad-id' ) );
        } catch ( AWPCP_Exception $e ) {
            return;
        }

        $listing_had_accepted_payment_status = $this->listing_has_accepted_payment_status( $listing );
        $is_transaction_consolidated = (bool) $transaction->get( 'ad-consolidated-at' );
        $should_trigger_actions = $is_transaction_consolidated;

        $this->update_listing_payment_information( $listing, $transaction );

        if ( $transaction->was_payment_successful() ) {
            if ( ! $listing_had_accepted_payment_status ) {
                $this->listings_logic->update_listing_verified_status( $listing, $transaction );
                $this->listings_logic->set_new_listing_post_status( $listing, $transaction->payment_status, $should_trigger_actions );
            }

            if ( ! $is_transaction_consolidated ) {
                $this->listings_logic->consolidate_new_ad( $listing, $transaction );
            }
        } else if ( $transaction->did_payment_failed() && $listing_had_accepted_payment_status ) {
            if ( $is_transaction_consolidated ) {
                $this->listings_logic->disable_listing( $should_trigger_actions );
            } else {
                $this->listings_logic->disable_listing_without_triggering_actions( $should_trigger_actions );
            }
        }
    }

    private function listing_has_accepted_payment_status( $listing ) {
        $payment_status = $this->listing_renderer->get_payment_status( $listing );

        // TODO: how to remove dependency on AWPCP_Payment_Transaction?
        if ( $payment_status === AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING ) {
            return true;
        } else if ( $payment_status === AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED ) {
            return true;
        } else if ( $payment_status === AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_REQUIRED ) {
            return true;
        }
        return false;
    }

    private function update_listing_payment_information( $listing, $transaction ) {
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_payment_status', $transaction->payment_status );
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_payment_gateway', $transaction->payment_gateway );
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_payer_email', $transaction->payer_email );
    }
}
