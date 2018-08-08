<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Ajax handler for the action that saves information for new and existing listings.
 */
class AWPCP_SaveListingInformationAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @var string
     */
    private $listing_category_taxonomy;

    /**
     * @var ListingsAPI
     */
    private $listings_logic;

    /**
     * @var ListingsCollection
     */
    private $listings;

    /**
     * @var Payments
     */
    private $payments;

    /**
     * @var FormFieldsValidator
     */
    private $form_fields_validator;

    /**
     * @var ListingPostedData
     */
    private $posted_data;

    /**
     * @var Request
     */
    private $request;

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct( $listing_category_taxonomy, $listings_logic, $listing_renderer, $listings, $payments, $form_fields_validator, $payment_information_validator, $posted_data, $roles, $settings, $response, $request ) {
        parent::__construct( $response );

        $this->listing_category_taxonomy     = $listing_category_taxonomy;
        $this->listings_logic                = $listings_logic;
        $this->listing_renderer              = $listing_renderer;
        $this->listings                      = $listings;
        $this->payments                      = $payments;
        $this->form_fields_validator         = $form_fields_validator;
        $this->payment_information_validator = $payment_information_validator;
        $this->posted_data                   = $posted_data;
        $this->roles                         = $roles;
        $this->settings                      = $settings;
        $this->request                       = $request;
    }

    /**
     * @since 4.0.0
     */
    public function ajax() {
        // TODO: Throw an error if listing ID is not provided.
        // TODO: How to delete attachments uploaded to listings that were never consolidated?
        // TODO: Allow categories to be updated.
        try {
            return $this->try_to_save_listing_information();
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->getMessage() );
        }
    }

    /**
     * TODO: Validate re-captcha.
     *
     * @since 4.0.0
     * @throws AWPCP_Exception  If current user is not authorized to save the
     *                          listing's information.
     */
    private function try_to_save_listing_information() {
        $listing = $this->listings->get( $this->request->param( 'ad_id' ) );
        $nonce   = $this->request->post( 'nonce' );

        if ( ! wp_verify_nonce( $nonce, "awpcp-save-listing-information-{$listing->ID}" ) ) {
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

        if ( $this->listings_logic->can_payment_information_be_modified_during_submit( $listing ) ) {
            return $this->save_new_listing_information( $listing );
        }

        return $this->save_existing_listing_information( $listing );
    }

    /**
     * @since 4.0.0
     * @throws AWPCP_Exception  If a transaction cannot be found.
     */
    private function save_new_listing_information( $listing ) {
        $transaction = $this->payments->get_transaction();

        if ( is_null( $transaction ) ) {
            $message = __( 'There is no payment transaction associated with this request. Aborting.', 'another-wordpress-classifieds-plugin' );

            throw new AWPCP_Exception( $message );
        }

        // TODO: I believe the post_status is never going to be auto-draft when
        // pay before place ad is enabled.
        //
        // Hence save_information_for_new_listing_pending_payment() is never
        // called.
        //
        // XXX: It could be called if someone tries to bypass payment by returning to
        // the listing-information step passing the newly generated listing_id and
        // transaction_id.
        if ( $this->settings->get_option( 'pay-before-place-ad' ) ) {
            return $this->save_information_for_new_listing_already_paid( $listing, $transaction );
        }

        return $this->save_information_for_new_listing_pending_payment( $listing, $transaction );
    }

    /**
     * TODO: trigger awpcp-before-save-listing action
     * TODO: trigger awpcp-place-listing-listing-data filter
     * TODO: trigger awpcp-save-ad-details action
     * TODO: trigger awpcp_before_edit_ad action.
     * TODO: create payment transaction and redirect to payment page.
     * TODO: Test trying to provide the ID of a listing that hasn't been paid. Does it let the user edit the listing information?
     *
     * @since 4.0.0
     */
    private function save_information_for_new_listing_already_paid( $listing ) {
        $posted_data = $this->posted_data->get_posted_data_for_already_paid_listing( $listing );

        $errors = $this->form_fields_validator->get_validation_errors( $posted_data['post_data'], $listing );

        if ( ! empty( $errors ) ) {
            return $this->multiple_errors_response( $errors );
        }

        $this->listings_logic->update_listing( $listing, $posted_data['post_data'] );

        // TODO: Handle redirects when the listing is still a draft.
        // TODO: Shouldn't this sent the user to the finish step?
        $redirect_params = [
            'step'       => 'finish',
            'listing_id' => $listing->ID,
        ];

        $response = [
            'redirect_url' => add_query_arg( $redirect_params, $posted_data['current_url'] ),
        ];

        return $this->success( $response );
    }

    /**
     * @since 4.0.0
     */
    private function save_information_for_new_listing_pending_payment( $listing, $transaction ) {
        $posted_data = $this->posted_data->get_posted_data_for_listing_pending_payment( $listing );

        $errors = array_merge(
            $this->payment_information_validator->get_validation_errors( $posted_data['post_data'] ),
            $this->form_fields_validator->get_validation_errors( $posted_data['post_data'], $listing )
        );

        if ( ! empty( $errors ) ) {
            return $this->multiple_errors_response( $errors );
        }

        $this->prepare_transaction_for_checkout( $transaction, $posted_data );

        $this->listings_logic->update_listing( $listing, $posted_data['post_data'] );

        // TODO: Redirect to checkout page.
        return $this->redirect_to_checkout_page( $listing, $transaction, $posted_data );
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
                'id' => $listing->ID,
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

    /**
     * TODO: Trigger awpcp_before_edit_ad action.
     * TODO: Trigger awpcp_edit_ad action.
     *
     * @since 4.0.0
     */
    private function save_existing_listing_information( $listing ) {
        $posted_data = $this->posted_data->get_posted_data_for_already_paid_listing( $listing );

        $errors = $this->form_fields_validator->get_validation_errors( $posted_data['post_data'], $listing );

        if ( ! empty( $errors ) ) {
            return $this->multiple_errors_response( $errors );
        }

        $this->listings_logic->update_listing( $listing, $posted_data['post_data'] );

        $redirect_params = [
            'step'       => 'finish',
            'listing_id' => $listing->ID,
            'edit_nonce' => wp_create_nonce( "awpcp-edit-listing-{$listing->ID}" ),
        ];

        $response = [
            'redirect_url' => add_query_arg( $redirect_params, $posted_data['current_url'] ),
        ];

        return $this->success( $response );
    }
}
