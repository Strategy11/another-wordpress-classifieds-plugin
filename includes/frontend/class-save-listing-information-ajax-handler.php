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
     * @var ListingAuthorization
     */
    private $authorization;

    /**
     * @var RolesAndCapabilities
     */
    private $roles;

    /**
     * @var FormFieldsValidator
     */
    private $form_fields_validator;

    /**
     * @var FormFieldsData
     */
    private $form_fields_data;

    /**
     * @var Request
     */
    private $request;

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct( $listing_category_taxonomy, $listings_logic, $listing_renderer, $listings, $payments, $authorization, $roles, $form_fields_validator, $payment_information_validator, $form_fields_data, $settings, $response, $request ) {
        parent::__construct( $response );

        $this->listing_category_taxonomy     = $listing_category_taxonomy;
        $this->listings_logic                = $listings_logic;
        $this->listing_renderer              = $listing_renderer;
        $this->listings                      = $listings;
        $this->payments                      = $payments;
        $this->authorization                 = $authorization;
        $this->roles                         = $roles;
        $this->form_fields_validator         = $form_fields_validator;
        $this->payment_information_validator = $payment_information_validator;
        $this->form_fields_data              = $form_fields_data;
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
            return $this->error_response( $e->getMessage() );
        }
    }

    /**
     * TODO: Validate re-captcha.
     *
     * @since 4.0.0
     */
    private function try_to_save_listing_information() {
        $listing = $this->listings->get( $this->request->param( 'ad_id' ) );

        if ( 'auto-draft' === $listing->post_status ) {
            return $this->save_new_listing_information( $listing );
        }

        return $this->save_existing_listing_information( $listing );
    }

    /**
     * @since 4.0.0
     */
    private function save_new_listing_information( $listing ) {
        $transaction = $this->payments->get_transaction();

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
        $posted_data = $this->get_posted_data_for_already_paid_listing( $listing );

        $errors = $this->form_fields_validator->get_validation_errors( $posted_data['post_data'], $listing );

        if ( ! empty( $errors ) ) {
            return $this->multiple_errors_response( $errors );
        }

        $this->listings_logic->update_listing( $listing, $posted_data['post_data'] );

        // TODO: Handle redirects when the listing is still a draft.
        $response = [
            'redirect_url' => $this->listing_renderer->get_view_listing_url( $listing ),
        ];

        return $this->success( $response );
    }

    /**
     * @since 4.0.0
     */
    private function get_posted_data_for_already_paid_listing( $listing ) {
        $posted_data  = $this->get_common_posted_data( $listing );
        $payment_term = $this->listing_renderer->get_payment_term( $listing );

        return $this->update_posted_data_for_payment_term( $posted_data, $listing, $payment_term );
    }

    /**
     * @since 4.0.0
     */
    private function get_common_posted_data( $listing ) {
        $post_data   = $this->form_fields_data->get_posted_data( $listing );
        $user_id     = $this->request->post( 'user_id' );
        $current_url = $this->request->post( 'current_url' );

        if ( ! $this->roles->current_user_is_moderator() ) {
            $user_id = $this->request->get_current_user_id();
        }

        $now         = current_time( 'mysql' );
        $post_status = 'draft';

        if ( 'auto-draft' !== $listing->post_status ) {
            $post_status = $this->listings_logic->get_modified_listing_post_status( $listing );
        }

        // TODO: Should we validate that the payment term can be used?
        // TODO: For pending payment listings, we should use the payment term info sent with the request.
        $post_data['post_fields'] = array_merge( $post_data['post_fields'], [
            'post_name'         => '',
            'post_author'       => $user_id,
            'post_modified'     => $now,
            'post_modified_gmt' => get_gmt_from_date( $now ),
            // TODO: Use appropriate post status for new and existing listings.
            'post_status'       => $post_status,
            'post_date'         => $now,
            'post_date_gmt'     => get_gmt_from_date( $now ),

        ] );

        // TODO: Make sure users are allowed to change the start/end date fields when authorized.
        if ( empty( $post_data['metadata']['_awpcp_start_date'] ) ) {
            $post_data['metadata']['_awpcp_start_date'] = $now;
        }

        return [
            'listing'     => $listing,
            'user_id'     => $user_id,
            'current_url' => $current_url,
            'post_data'   => $post_data,
        ];
    }

    /**
     * @since 4.0.0
     */
    private function update_posted_data_for_payment_term( $posted_data, $listing, $payment_term ) {
        $posted_data['payment_term'] = $payment_term;

        $post_title   = $posted_data['post_data']['post_fields']['post_title'];
        $post_content = $posted_data['post_data']['post_fields']['post_content'];

        // TODO: Should we validate that the payment term can be used?
        // TODO: For pending payment listings, we should use the payment term info sent with the request.
        $posted_data['post_data']['post_fields']['post_title']   = $this->prepare_title( $post_title, $payment_term->get_characters_allowed_in_title() );
        $posted_data['post_data']['post_fields']['post_content'] = $this->prepare_content( $post_content, $payment_term->get_characters_allowed() );

        if ( $this->authorization->is_current_user_allowed_to_edit_listing_start_date( $listing ) && ! $this->authorization->is_current_user_allowed_to_edit_listing_end_date( $listing ) ) {
            $start_date_timestamp = awpcp_datetime( 'timestamp', $posted_data['post_data']['metadata']['_awpcp_start_date'] );

            $posted_data['post_data']['metadata']['_awpcp_end_date'] = $payment_term->calculate_end_date( $start_date_timestamp );
        }

        // TODO: Save regions.
        $posted_data['post_data']['regions-allowed'] = $payment_term->get_regions_allowed();

        return $posted_data;
    }

    /**
     * @since 4.0.0
     */
    private function prepare_title( $title, $characters_allowed ) {
        if ( $characters_allowed > 0 && awpcp_utf8_strlen( $title ) > $characters_allowed ) {
            $title = awpcp_utf8_substr( $title, 0, $characters_allowed );
        }

        return $title;
    }

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function prepare_content( $content, $characters_allowed ) {
        $allow_html = (bool) get_awpcp_option( 'allowhtmlinadtext' );

        if ( $allow_html ) {
            $content = wp_kses_post( $content );
        } else {
            $content = wp_strip_all_tags( $content );
        }

        if ( $characters_allowed > 0 && awpcp_utf8_strlen( $content ) > $characters_allowed ) {
            $content = awpcp_utf8_substr( $content, 0, $characters_allowed );
        }

        if ( $allow_html ) {
            $content = force_balance_tags( $content );
        } else {
        	$content = esc_html( $content );
        }

        return $content;
    }

    /**
     * @since 4.0.0
     */
    private function save_information_for_new_listing_pending_payment( $listing, $transaction ) {
        $posted_data = $this->get_posted_data_for_listing_pending_payment( $listing );

        $errors = array_merge(
            $this->payment_information_validator->get_validation_errors( $posted_data['post_data'] ),
            $this->form_fields_validator->get_validation_errors( $posted_data['post_data'], $listing )
        );

        if ( ! empty( $errors ) ) {
            return $this->multiple_errors_response( $errors );
        }

        $this->listings_logic->update_listing( $listing, $posted_data['post_data'] );

        // TODO: Redirect to checkout page.
        return $this->redirect_to_checkout_page( $listing, $transaction, $posted_data );
    }

    /**
     * @since 4.0.0
     */
    private function get_posted_data_for_listing_pending_payment( $listing ) {
        $categories        = array_map( 'intval', $this->request->post( 'categories' ) );
        $payment_term_id   = $this->request->post( 'payment_term_id' );
        $payment_term_type = $this->request->post( 'payment_term_type' );

        $payment_term = $this->payments->get_payment_term( $payment_term_id, $payment_term_type );

        $posted_data = $this->get_common_posted_data( $listing );
        $posted_data = $this->update_posted_data_for_payment_term( $posted_data, $listing, $payment_term );

        $posted_data['categories']   = $categories;
        $posted_data['payment_term'] = $payment_term;
        $posted_data['payment_type'] = $this->request->post( 'payment_type' );

        $posted_data['post_data']['metadata']['_awpcp_payment_term_id']   = $payment_term->id;
        $posted_data['post_data']['metadata']['_awpcp_payment_term_type'] = $payment_term->type;

        $posted_data['post_data']['terms'][ $this->listing_category_taxonomy ] = $categories;

        return $posted_data;
    }

    /**
     * @since 4.0.0
     */
    private function redirect_to_checkout_page( $listing, $transaction, $posted_data ) {
        $this->prepare_transaction_for_checkout( $transaction, $posted_data );

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
        $posted_data = $this->get_posted_data_for_already_paid_listing( $listing );

        $errors = $this->form_fields_validator->get_validation_errors( $posted_data['post_data'], $listing );

        if ( ! empty( $errors ) ) {
            return $this->multiple_errors_response( $errors );
        }

        $this->listings_logic->update_listing( $listing, $posted_data['post_data'] );

        $redirect_params = [
            'step'       => 'finish',
            'listing_id' => $listing->ID,
        ];

        $response = [
            'redirect_url' => add_query_arg( $redirect_params, $posted_data['current_url'] ),
        ];

        return $this->success( $response );
    }
}
