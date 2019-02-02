<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Ajax handler for the action that creates a listing for the selected categories
 * and payment term.
 */
class AWPCP_CreateEmptyListingAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @var AWPCP_Listings_API
     */
    private $listings_logic;

    /**
     * @var AWPCP_PaymentInformationValidator
     */
    private $payment_information_validator;

    /**
     * @var CAPTCHA
     */
    private $captcha;

    /**
     * @var ListingOrderPostedData
     */
    private $posted_data;

    /**
     * @var AWPCP_Request
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $listings_logic, $payment_information_validator, $payments, $roles, $captcha, $response, $settings, $posted_data, $request ) {
        parent::__construct( $response );

        $this->listings_logic                = $listings_logic;
        $this->payment_information_validator = $payment_information_validator;
        $this->payments                      = $payments;
        $this->roles                         = $roles;
        $this->captcha                       = $captcha;
        $this->settings                      = $settings;
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
     * @throws AWPCP_Exception  If current user is not allowed to create empty listings.
     */
    private function process_request() {
        $nonce = $this->request->post( 'nonce' );

        if ( ! wp_verify_nonce( $nonce, 'awpcp-create-empty-listing' ) ) {
            throw new AWPCP_Exception( __( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ) );
        }

        // Only admin users are allowed to post listings.
        if ( $this->settings->get_option( 'onlyadmincanplaceads' ) && ! $this->roles->current_user_is_administrator() ) {
            $message = __( 'You are not authorized to perform this action. Only administrator users are allowed to submit classifieds.', 'another-wordpress-classifieds-plugin' );

            throw new AWPCP_Exception( $message );
        }

        // Only registered users are allowed to place listings.
        if ( $this->settings->get_option( 'requireuserregistration' ) && ! is_user_logged_in() ) {
            $message = __( 'Your are not authorized to perform this action. Only logged in users are allowed to submit classifieds.', 'another-wordpress-classifieds-plugin' );

            throw new AWPCP_Exception( $message );
        }

        $this->captcha->validate();

        $posted_data = $this->posted_data->get_posted_data();
        $transaction = $this->create_transaction( $posted_data );
        $listing     = $this->create_listing( $transaction, $posted_data['post_data'] );

        $this->prepare_transaction_for_checkout( $transaction, $posted_data );

        if ( $this->payments->payments_enabled() && $this->settings->get_option( 'pay-before-place-ad' ) ) {
            return $this->redirect_to_checkout_page( $listing, $transaction, $posted_data );
        }

        $response = [
            'transaction' => $transaction->id,
            'listing'     => [
                'ID' => $listing->ID,
            ],
        ];

        return $this->success( $response );
    }

    /**
     * @since 4.0.0
     * @throws AWPCP_Exception  When an error occurs trying to set the transaction
     *                          status to Open.
     */
    private function create_transaction( $posted_data ) {
        $transaction = $this->payments->create_transaction();
        $errors      = [];

        $transaction->user_id = $posted_data['user_id'];
        $transaction->set( 'context', 'place-ad' );
        $transaction->set( 'redirect', $posted_data['current_url'] );
        $transaction->set( 'redirect-data', [ 'step' => 'payment-completed' ] );
        $transaction->set( 'user-just-logged-in', $this->request->post( 'loggedin', false ) ); // TODO: Is this necessary?

        $this->payments->set_transaction_status_to_open( $transaction, $errors );

        if ( $errors ) {
            throw new AWPCP_Exception( array_shift( $errors ) );
        }

        return $transaction;
    }

    /**
     * TODO: Handle 500 errors on frontend.
     * TODO: Add nonce verificiation.
     * TODO: Return errors.
     * TODO: Show errors on the frontend.
     *
     * @since 4.0.0
     * @throws AWPCP_Exception  When the payment information is not valid.
     */
    private function create_listing( $transaction, $post_data ) {
        $post_data['post_fields']['post_title']  = 'Classified Auto Draft';
        $post_data['post_fields']['post_status'] = 'auto-draft';

        $errors = $this->payment_information_validator->get_validation_errors( $post_data );

        if ( $errors ) {
            throw new AWPCP_Exception( array_shift( $errors ) );
        }

        $listing = $this->listings_logic->create_listing( $post_data );

        $transaction->set( 'ad-id', $listing->ID );
        $transaction->save();

        return $listing;
    }

    /**
     * @since 4.0.0
     */
    private function redirect_to_checkout_page( $listing, $transaction, $posted_data ) {
        $redirect_params = [
            'step'           => 'checkout',
            'listing_id'     => $listing->ID,
            'transaction_id' => $transaction->id,
        ];

        $response = [
            'listing'      => [
                'ID' => $listing->ID,
            ],
            'transaction'  => $transaction->id,
            'redirect_url' => add_query_arg( $redirect_params, $posted_data['current_url'] ),
        ];

        return $this->success( $response );
    }

    /**
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
