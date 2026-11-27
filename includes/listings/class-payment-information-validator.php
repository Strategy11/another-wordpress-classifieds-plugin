<?php
/**
 * @package AWPCP\Listings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Validator for categories, user and payment term properties selection for
 * listings.
 */
class AWPCP_PaymentInformationValidator {

    /**
     * @var string
     */
    private $listing_category_taxonomy;

    /**
     * @var AWPCP_Categories_Collection
     */
    private $categories;

    /**
     * @var AWPCP_PaymentsAPI
     */
    private $payments;

    /**
     * @var AWPCP_RolesAndCapabilities
     */
    private $roles;

    /**
     * @since 4.0.0
     */
    public function __construct( $listing_category_taxonomy, $categories, $payments, $roles ) {
        $this->listing_category_taxonomy = $listing_category_taxonomy;
        $this->categories                = $categories;
        $this->payments                  = $payments;
        $this->roles                     = $roles;
    }

    /**
     * @since 4.0.0
     */
    public function get_validation_errors( $data ) {
        $categories_errors   = $this->validate_selected_categories( $data );
        $payment_term_errors = $this->validate_selected_payment_term( $data );
        $user_errors         = $this->validate_selected_user( $data );

        // The union keeps the categories selection error over the categories limit one.
        $errors = $categories_errors + array_merge( $payment_term_errors, $user_errors );
        $errors = apply_filters( 'awpcp-validate-post-listing-order', $errors, $data );

        return $errors;
    }

    /**
     * @since 4.0.0
     */
    private function validate_selected_payment_term( $data ) {
        $errors = [];

        // Can't use empty for term ID, because 0 is the ID of the Free Fee.
        if ( 0 === strlen( $data['metadata']['_awpcp_payment_term_id'] ) || empty( $data['metadata']['_awpcp_payment_term_type'] ) ) {
            $errors['payment-term'] = __( 'You should choose one of the available Payment Terms.', 'another-wordpress-classifieds-plugin' );
        }

        $payment_term_id   = $data['metadata']['_awpcp_payment_term_id'];
        $payment_term_type = $data['metadata']['_awpcp_payment_term_type'];

        $payment_term = $this->payments->get_payment_term( $payment_term_id, $payment_term_type );

        if ( is_null( $payment_term ) || $this->is_unavailable_free_payment_term( $payment_term ) ) {
            $errors['payment-term'] = __( 'You should choose one of the available Payment Terms.', 'another-wordpress-classifieds-plugin' );
        } elseif ( ! $this->roles->current_user_is_administrator() && $payment_term->private ) {
            $errors['payment-term'] = __( 'The Payment Term you selected is not available for non-administrator users.', 'another-wordpress-classifieds-plugin' );
        } else {
            $errors = array_merge( $errors, $this->validate_number_of_categories( $data, $payment_term ) );
        }

        return $errors;
    }

    /**
     * Makes sure the listing doesn't have more categories than the selected
     * payment term allows. The browser enforces the same limit, but the
     * request can be modified.
     *
     * @since x.x
     *
     * @param array  $data         Listing data.
     * @param object $payment_term The selected payment term.
     *
     * @return array
     */
    private function validate_number_of_categories( $data, $payment_term ) {
        $allowed    = absint( apply_filters( 'awpcp-number-of-categories-allowed-in-post-listing-order-step', 1, $payment_term ) );
        $categories = array_unique( array_filter( array_map( 'absint', $this->get_selected_categories( $data ) ) ) );

        // Zero means the payment term allows an unlimited number of categories.
        if ( 0 === $allowed || count( $categories ) <= $allowed ) {
            return [];
        }

        return [
            'categories' => sprintf(
                // translators: %d is the number of categories allowed by the payment term.
                _n(
                    'The Payment Term you selected allows %d category only.',
                    'The Payment Term you selected allows up to %d categories.',
                    $allowed,
                    'another-wordpress-classifieds-plugin'
                ),
                $allowed
            ),
        ];
    }

    /**
     * The Free Listing payment term (fee with ID 0) is only offered when
     * payments are disabled. It can still be found by ID so that existing
     * listings keep working, but non-administrator users must not be able to
     * select it on paid boards. Administrators skip payment steps anyway.
     *
     * @since 4.4.11
     *
     * @param object $payment_term The selected payment term.
     *
     * @return bool
     */
    private function is_unavailable_free_payment_term( $payment_term ) {
        if ( AWPCP_FeeType::TYPE !== $payment_term->type || 0 !== absint( $payment_term->id ) ) {
            return false;
        }

        return $this->payments->payments_enabled() && ! $this->roles->current_user_is_administrator();
    }

    /**
     * @since 4.0.0
     */
    private function validate_selected_user( $data ) {
        $errors = [];

        if ( $this->roles->current_user_is_moderator() && empty( $data['post_fields']['post_author'] ) ) {
            $errors['user'] = __( 'You should select an owner for this Ad.', 'another-wordpress-classifieds-plugin' );
        }

        return $errors;
    }

    /**
     * Migrated from Place Ad page.
     *
     * @since 4.0.0
     */
    private function validate_selected_categories( $data ) {
        $categories = $this->get_selected_categories( $data );
        $errors     = [];

        if ( empty( $categories ) ) {
            $errors['categories'] = __( 'Ad Category field is required', 'another-wordpress-classifieds-plugin' );
        }

        if ( ! get_awpcp_option( 'noadsinparentcat' ) ) {
            return $errors;
        }

        $hierarchy = $this->categories->get_hierarchy();

        foreach ( $categories as $category_id ) {
            if ( isset( $hierarchy[ $category_id ] ) ) {
                $errors['categories'] = __( 'You cannot list your Ad in top level categories.', 'another-wordpress-classifieds-plugin' );

                return $errors;
            }
        }

        return $errors;
    }

    /**
     * @since x.x
     *
     * @param array $data Listing data.
     *
     * @return array IDs of the selected categories.
     */
    private function get_selected_categories( $data ) {
        if ( isset( $data['terms'][ $this->listing_category_taxonomy ] ) ) {
            return (array) $data['terms'][ $this->listing_category_taxonomy ];
        }

        return [];
    }
}
