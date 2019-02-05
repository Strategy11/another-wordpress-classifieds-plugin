<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * @since 4.0.0
 */
class AWPCP_UpdateListingOrderAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @var ListingsAPI
     */
    private $listings_logic;

    /**
     * @var PaymentInformationValidator
     */
    private $payment_information_validator;

    /**
     * @var ListingsCollection
     */
    private $listings;

    /**
     * @var PaymentsAPI
     */
    private $payments;

    /**
     * @var ListingOrderPostedData
     */
    private $posted_data;

    /**
     * @var Request
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $listings_logic, $payment_information_validator, $listings, $payments, $posted_data, $response, $request ) {
        parent::__construct( $response );

        $this->listings_logic                = $listings_logic;
        $this->payment_information_validator = $payment_information_validator;
        $this->listings                      = $listings;
        $this->payments                      = $payments;
        $this->posted_data                   = $posted_data;
        $this->request                       = $request;
    }

    /**
     * @since 4.0.0
     */
    public function ajax() {
        try {
            return $this->process_request();
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->getMessage() );
        }
    }

    /**
     * @since 4.0.0
     * @throws AWPCP_Exception When user is not authorized to update the ad's order
     *                         data or the information submitted is invalid.
     */
    private function process_request() {
        $nonce = $this->request->post( 'nonce' );

        if ( ! wp_verify_nonce( $nonce, 'awpcp-update-listing-order' ) ) {
            throw new AWPCP_Exception( __( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ) );
        }

        $listing = $this->listings->get( $this->request->param( 'listing_id' ) );

        if ( ! $this->listings_logic->can_payment_information_be_modified_during_submit( $listing ) ) {
            throw new AWPCP_Exception( __( 'The payment information for the specified ad cannot be modified at this time.', 'another-wordpress-classifieds-plugin' ) );
        }

        // The methods tries to load the transaction with ID equal to the
        // value of a transaction_id $_REQUEST parameter.
        $transaction = $this->payments->get_transaction();

        if ( is_null( $transaction ) ) {
            throw new AWPCP_Exception( __( "The specified transaction doesn't exist.", 'another-wordpress-classifieds-plugin' ) );
        }

        $posted_data = $this->posted_data->get_posted_data();
        $post_data   = $posted_data['post_data'];

        $errors = $this->payment_information_validator->get_validation_errors( $post_data );

        if ( $errors ) {
            throw new AWPCP_Exception( array_shift( $errors ) );
        }

        $this->listings_logic->update_listing( $listing, $post_data );
        $this->prepare_transaction_for_checkout( $transaction, $posted_data );

        $response = [
            'transaction' => $transaction->id,
            'listing'     => [
                'ID' => $listing->ID,
            ],
        ];

        return $this->success( $response );
    }

    /**
     * TODO: This is an exact copy of prepare_transaction_for_checkout() in
     * CreateEmptyListing and SaveListingInformation ajax handlers.
     *
     * @since 4.0.0
     * @throws AWPCP_Exception  When an error occurs trying to change the transaction
     *                          status to Checkout.
     */
    private function prepare_transaction_for_checkout( $transaction, $data ) {
        $categories   = $data['categories'];
        $payment_term = $data['payment_term'];
        $payment_type = $data['payment_type'];
        $user_id      = $data['user_id'];

        // phpcs:disable WordPress.NamingConventions.ValidHookName.UseUnderscores
        $number_of_categories_allowed = apply_filters( 'awpcp-number-of-categories-allowed-in-post-listing-order-step', 1, $payment_term );
        // phpcs:enable

        $transaction->user_id = $user_id;
        $transaction->set( 'category', array_slice( $categories, 0, $number_of_categories_allowed ) );
        $transaction->set( 'payment-term-type', $payment_term->type );
        $transaction->set( 'payment-term-id', $payment_term->id );
        $transaction->set( 'payment-term-payment-type', $payment_type );

        $transaction->remove_all_items();
        $transaction->reset_payment_status();

        $this->payments->set_transaction_item_from_payment_term( $transaction, $payment_term, $payment_type );

        // Process transaction to grab Credit Plan information.
        $this->payments->set_transaction_credit_plan( $transaction );

        // Let other parts of the plugin know a transaction is being processed.
        $this->payments->process_transaction( $transaction );

        $this->payments->set_transaction_status_to_ready_to_checkout( $transaction, $transaction_errors );

        if ( $transaction_errors ) {
            throw new AWPCP_Exception( array_shift( $transaction_errors ) );
        }
    }
}
