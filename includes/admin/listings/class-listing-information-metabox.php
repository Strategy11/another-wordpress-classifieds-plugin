<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Classified Information metabox.
 */
class AWPCP_ListingInfromationMetabox {

    /**
     * @var ListingsLogic
     */
    private $listings_logic;

    /**
     * @var ListingRenderer
     */
    private $listing_renderer;

    /**
     * @var Payments
     */
    private $payments;

    /**
     * @var TemplateRenderer
     */
    private $template_renderer;

    /**
     * @var Request
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $listings_logic, $listing_renderer, $payments, $template_renderer, $request ) {
        $this->listings_logic    = $listings_logic;
        $this->listing_renderer  = $listing_renderer;
        $this->payments          = $payments;
        $this->template_renderer = $template_renderer;
        $this->request           = $request;
    }

    /**
     * @since 4.0.0
     */
    public function render( $post ) {
        $params = [];

        $params['user_can_change_payment_term'] = awpcp_current_user_is_moderator();

        $params['renewed_date'] = $this->listing_renderer->get_renewed_date_formatted( $post );
        $params['end_date']     = $this->listing_renderer->get_end_date_formatted( $post );
        $params['access_key']   = $this->listing_renderer->get_access_key( $post );

        $payment_term = $this->listing_renderer->get_payment_term( $post );

        $params['payment_term'] = [
            'id'                        => '',
            'name'                      => '',
            'number_of_images'          => '',
            'number_of_regions'         => '',
            'characters_in_title'       => '',
            'characters_in_description' => '',
            'url'                       => '',
        ];

        if ( $payment_term ) {
            $params['payment_term'] = $this->get_payment_term_properties( $payment_term );
        }

        $params['payment_terms'] = $this->get_available_payment_terms( $post->post_author, $payment_term );

        echo $this->template_renderer->render_template( 'admin/listings/listing-information-metabox.tpl.php', $params ); // XSS Ok.
    }

    /**
     * @since 4.0.0
     */
    private function get_available_payment_terms( $post_author, $current_payment_term ) {
        $current_payment_term_included = false;
        $payment_terms                 = [];

        foreach ( $this->payments->get_user_payment_terms( $post_author ) as $type => $terms ) {
            foreach ( $terms as $term ) {
                $payment_terms[] = $this->get_payment_term_properties( $term );

                if ( ! $current_payment_term ) {
                    continue;
                }

                if ( $type === $current_payment_term->type && $term->id === $current_payment_term->id ) {
                    $current_payment_term_included = true;
                }
            }
        }

        if ( $current_payment_term && ! $current_payment_term_included ) {
            array_unshift( $payment_terms, $this->get_payment_term_properties( $current_payment_term ) );
        }

        return $payment_terms;
    }

    /**
     * @since 4.0.0
     */
    private function get_payment_term_properties( $payment_term ) {
        $properties = [
            'id'                        => "{$payment_term->type}-{$payment_term->id}",
            'name'                      => $payment_term->get_name(),
            'number_of_images'          => $payment_term->images,
            'number_of_regions'         => $payment_term->get_regions_allowed(),
            'characters_in_title'       => $payment_term->get_characters_allowed_in_title(),
            'characters_in_description' => $payment_term->get_characters_allowed(),
            'url'                       => $payment_term->get_dashboard_url(),
        ];

        if ( 0 === $properties['characters_in_title'] ) {
            $properties['characters_in_title'] = _x( 'unlimited', 'listing information metabox', 'another-wordpress-classifieds-plugin' );
        }

        if ( 0 === $properties['characters_in_description'] ) {
            $properties['characters_in_description'] = _x( 'unlimited', 'listing information metabox', 'another-wordpress-classifieds-plugin' );
        }

        return $properties;
    }

    /**
     * TODO: What happens when update_listing throws an exception?
     *
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save( $post_id, $post ) {
        if ( ! awpcp_current_user_is_moderator() ) {
            return;
        }

        if ( isset( $this->save_in_progress ) && $this->save_in_progress ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( 'auto-draft' === $post->post_status ) {
            return;
        }

        $this->save_in_progress = true;

        $this->maybe_update_payment_term( $post );

        $this->save_in_progress = false;
    }

    /**
     * @since 4.0.0
     */
    private function maybe_update_payment_term( $post ) {
        $previous_payment_term = $this->listing_renderer->get_payment_term( $post );
        $new_payment_term      = $this->get_selected_payment_term();
        $payment_type          = 'money';

        if ( is_null( $new_payment_term ) ) {
            return;
        }

        if ( $this->payments->payment_terms_are_equals( $new_payment_term, $previous_payment_term ) ) {
            return;
        }

        $transaction = $this->create_transaction( $post, $new_payment_term, $payment_type );

        $this->update_payment_term( $post, $new_payment_term );
        $this->payments->set_transaction_item_from_payment_term( $transaction, $new_payment_term, $payment_type );
        $this->payments->set_transaction_status_to_completed( $transaction, $errors );

        if ( $errors ) {
            $this->update_payment_term( $post, $previous_payment_term );
            $transaction->delete();
            return;
        }

        // Reload payment term objects so that changes made when the transaction
        // was being processed are available to handlers of `awpcp_listing_payment_term_changed`.
        $previous_payment_term = $this->payments->get_payment_term( $previous_payment_term->id, $previous_payment_term->type );
        $new_payment_term      = $this->listing_renderer->get_payment_term( $post );

        do_action( 'awpcp_listing_payment_term_changed', $post, $previous_payment_term, $new_payment_term );
    }

    /**
     * @since 4.0.0
     */
    private function get_selected_payment_term() {
        $selected_payment_term = $this->request->post( 'payment_term' );
        $separator_pos         = strrpos( $selected_payment_term, '-' );
        $payment_term_type     = substr( $selected_payment_term, 0, $separator_pos );
        $payment_term_id       = intval( substr( $selected_payment_term, $separator_pos + 1 ) );

        return $this->payments->get_payment_term( $payment_term_id, $payment_term_type );
    }

    /**
     * @since 4.0.0
     */
    private function create_transaction( $post, $payment_term, $payment_type ) {
        $transaction = $this->payments->create_transaction();

        // TODO: Merge with code from Create Emtpy Listing and Save Listing Information ajax handlers. I think the transaction logic can be extracted.
        $transaction->user_id = $post->post_author;
        $transaction->set( 'context', 'place-ad' );
        $transaction->set( 'ad-id', $post->ID );
        $transaction->set( 'category', $this->listing_renderer->get_categories_ids( $post ) );
        $transaction->set( 'payment-term-type', $payment_term->type );
        $transaction->set( 'payment-term-id', $payment_term->id );
        $transaction->set( 'payment-term-payment-type', $payment_type );
        $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_REQUIRED;

        return $transaction;
    }

    /**
     * @since 4.0.0
     */
    private function update_payment_term( $post, $payment_term ) {
        $post_data = [
            'metadata' => [
                '_awpcp_payment_term_id'   => $payment_term->id,
                '_awpcp_payment_term_type' => $payment_term->type,
            ],
        ];

        $this->listings_logic->update_listing( $post, $post_data );
    }
}
