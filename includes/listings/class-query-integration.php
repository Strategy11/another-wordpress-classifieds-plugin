<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Class that integrates with WP_Query.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class AWPCP_QueryIntegration {

    /**
     * @var string
     */
    private $listing_post_type;

    /**
     * @var string
     */
    private $categories_taxonomy;

    /**
     * @var object
     */
    private $settings;

    /**
     * @param string $listing_post_type     The identifier for the Listings post type.
     * @param array  $categories_taxonomy   The identifier for the Listing Category taxonomy.
     * @param object $settings              An instance of Settings API.
     * @since 4.0.0
     */
    public function __construct( $listing_post_type, $categories_taxonomy, $settings ) {
        $this->listing_post_type   = $listing_post_type;
        $this->categories_taxonomy = $categories_taxonomy;
        $this->settings            = $settings;
    }

    /**
     * @param object $query     An instance of WP_Query.
     * @since 4.0.0
     */
    public function pre_get_posts( $query ) {
        if ( ! isset( $query->query_vars['classifieds_query'] ) ) {
            return;
        }

        $query_vars = $query->query_vars;

        $query_vars = $this->normalize_query_vars( $query_vars );
        $query_vars = $this->process_query_parameters( $query_vars );

        $query->query_vars = $query_vars;
    }

    /**
     * TODO: Do we need to set a context for the query? Listings Collection defined
     *       context = 'defualt'.
     *
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function normalize_query_vars( $query_vars ) {
        $query_vars = $this->set_default_query_paramaters( $query_vars );
        $query_vars = $this->normalize_region_query_parameters( $query_vars );

        // These groups of listings must be valid listings as well.
        $must_be_valid = array(
            'is_new',
            'is_expired',
            'is_about_to_expire',
            'is_enabled',
            'is_disabled',
            'is_awaiting_approval',
            'is_featured',
        );

        if ( array_intersect( $must_be_valid, array_keys( $query_vars['classifieds_query'] ) ) ) {
            $query_vars['classifieds_query']['is_valid'] = true;
        }

        // Valid listings are listings that have been verified and paid for.
        if ( isset( $query_vars['classifieds_query']['is_valid'] ) ) {
            $query_vars['classifieds_query']['is_verified']          = true;
            $query_vars['classifieds_query']['is_successfully_paid'] = true;
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    private function set_default_query_paramaters( $query_vars ) {
        if ( isset( $query_vars['classifieds_query'] ) ) {
            $query_vars['post_type'] = $this->listing_post_type;
        }

        if ( ! isset( $query_vars['post_status'] ) ) {
            $query_vars['post_status'] = array( 'disabled', 'draft', 'pending', 'publish' );
        }

        if ( ! isset( $query_vars['order'] ) ) {
            $query_vars['order'] = 'DESC';
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    private function normalize_region_query_parameters( $query_vars ) {
        $regions_query = array();

        if ( isset( $query_vars['classifieds_query']['regions'] ) ) {
            $regions_query = $query_vars['classifieds_query']['regions'];
        }

        // The 'region' parameter can be used to find listings that are associated
        // with a region of that name, regardless of the type of the region.
        if ( ! empty( $query_vars['classifieds_query']['region'] ) ) {
            $regions_query[] = array(
                'country' => $query_vars['classifieds_query']['region'],
            );

            $regions_query[] = array(
                'state' => $query_vars['classifieds_query']['region'],
            );

            $regions_query[] = array(
                'city' => $query_vars['classifieds_query']['region'],
            );

            $regions_query[] = array(
                'county' => $query_vars['classifieds_query']['region'],
            );
        }

        $single_region = array();

        // Search for a listing associated with region hierarchy that matches
        // the given search values.
        if ( ! empty( $query_vars['classifieds_query']['country'] ) ) {
            $single_region['country'] = $query_vars['classifieds_query']['country'];
        }

        if ( ! empty( $query_vars['classifieds_query']['state'] ) ) {
            $single_region['state'] = $query_vars['classifieds_query']['state'];
        }

        if ( ! empty( $query_vars['classifieds_query']['city'] ) ) {
            $single_region['city'] = $query_vars['classifieds_query']['city'];
        }

        if ( ! empty( $query_vars['classifieds_query']['county'] ) ) {
            $single_region['county'] = $query_vars['classifieds_query']['county'];
        }

        if ( ! empty( $single_region ) ) {
            $regions_query[] = $single_region;
        }

        $query_vars['classifieds_query']['regions'] = $regions_query;

        // Remove other region parameters.
        unset( $query_vars['classifieds_query']['region'] );
        unset( $query_vars['classifieds_query']['country'] );
        unset( $query_vars['classifieds_query']['state'] );
        unset( $query_vars['classifieds_query']['city'] );
        unset( $query_vars['classifieds_query']['county'] );

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_query_parameters( $query_vars ) {
        $query_vars = $this->process_is_verified_query_parameter( $query_vars );
        $query_vars = $this->process_is_successfully_paid_query_parameter( $query_vars );
        $query_vars = $this->process_is_new_query_parameter( $query_vars );
        $query_vars = $this->process_is_disabled_query_parameter( $query_vars );
        $query_vars = $this->process_is_enabled_query_parameter( $query_vars );
        $query_vars = $this->process_is_about_to_expire_query_parameter( $query_vars );
        $query_vars = $this->process_is_expired_query_parameter( $query_vars );
        $query_vars = $this->process_is_awaiting_approval_query_parameter( $query_vars );
        $query_vars = $this->process_is_awaiting_verification_query_parameter( $query_vars );
        $query_vars = $this->process_is_featured_query_parameter( $query_vars );

        $query_vars = $this->process_previous_id_query_parameter( $query_vars );

        $query_vars = $this->process_category_query_parameter( $query_vars );
        $query_vars = $this->process_category__not_in_query_parameter( $query_vars );
        $query_vars = $this->process_category__exclude_children_query_parameter( $query_vars );

        // TODO: Add support for verified, have_media_awaiting_approval.
        // TODO: What other parameters are missing?
        // TODO: Remove unused methods.
        $query_vars = $this->process_contact_name_query_parameter( $query_vars );
        $query_vars = $this->process_price_query_parameter( $query_vars );
        $query_vars = $this->process_min_price_query_parameter( $query_vars );
        $query_vars = $this->process_max_price_query_parameter( $query_vars );
        $query_vars = $this->process_payment_status_query_parameter( $query_vars );
        $query_vars = $this->process_payment_status__not_in_query_parameter( $query_vars );
        $query_vars = $this->process_payer_email_query_parameter( $query_vars );

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_verified_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['is_verified'] ) ) {
            // TODO: Can this be done with an EXISTS comparator? I think so.
            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_verified',
                'value'   => true,
                'compare' => '=',
                'type'    => 'BINARY',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_successfully_paid_query_parameter( $query_vars ) {
        if ( ! isset( $query_vars['classifieds_query']['is_successfully_paid'] ) ) {
            return $query_vars;
        }

        $payments_are_enabled = $this->settings->get_option( 'freepay' ) === 1;

        if ( ! $this->settings->get_option( 'enable-ads-pending-payment' ) && $payments_are_enabled ) {
            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_payment_status',
                'value'   => array( 'Pending', 'Unpaid' ),
                'compare' => 'NOT IN',
                'type'    => 'char',
            );

            return $query_vars;
        }

        $query_vars['meta_query'][] = array(
            'key'     => '_awpcp_payment_status',
            'value'   => 'Unpaid',
            'compare' => '!=',
            'type'    => 'char',
        );

        return $query_vars;
    }

    /**
     * TODO: Use EXISTS comparator instead.
     *
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_new_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['is_new'] ) ) {
            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_content_needs_review',
                'value'   => true,
                'compare' => '=',
                'type'    => 'BINARY',
            );
        }

        return $query_vars;
    }

    /**
     * TODO: Is it really a good idea to use a custom post status?
     *
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_disabled_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['is_disabled'] ) ) {
            $query_vars['post_status'] = 'disabled';
        }

        return $query_vars;
    }

    /**
     * TODO: Consdier order conditions (See Ad::get_order_conditions,
     *       Ad::get_enabled_ads (origin/master) and groupbrowseadsby option).
     *
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_enabled_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['is_enabled'] ) ) {
            $query_vars['post_status'] = 'publish';

            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_start_date',
                'value'   => current_time( 'mysql' ),
                'compare' => '<',
                'type'    => 'DATE',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_about_to_expire_query_parameter( $query_vars ) {
        if ( ! isset( $query_vars['classifieds_query']['is_about_to_expire'] ) ) {
            return $query_vars;
        }

        $threshold   = intval( $this->settings->get_option( 'ad-renew-email-threshold' ) );
        $target_date = strtotime( "+ $threshold days", current_time( 'timestamp' ) );

        $query_vars['meta_query'][] = array(
            'key'     => '_awpcp_end_date',
            'value'   => awpcp_datetime( 'mysql', $target_date ),
            'compare' => '<=',
            'type'    => 'DATE',
        );

        $query_vars['meta_query'][] = array(
            'key'     => '_awpcp_renew_email_sent',
            'compare' => 'NOT EXISTS',
        );

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_expired_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['is_expired'] ) ) {
            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_end_date',
                'value'   => current_time( 'mysql' ),
                'compare' => '<=',
                'type'    => 'DATE',
            );
        }

        return $query_vars;
    }

    /**
     * TODO: Should we handle this with a single meta parameter that is removed
     *       when the classified no longer needs to be approved?
     *
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_awaiting_approval_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['is_awaiting_approval'] ) ) {
            $query_vars['post_status'] = 'disabled';

            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_disabled_date',
                'compare' => 'NOT_EXISTS',
            );
        }

        return $query_vars;
    }


    /**
     * TODO: Convert this into an EXISTS. I think there is no need to compare.
     *
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_awaiting_verification_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['is_awaiting_verification'] ) ) {
            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_verification_needed',
                'value'   => true,
                'compare' => '=',
                'type'    => 'BINARY',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_is_featured_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['is_featured'] ) ) {
            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_is_featured',
                'value'   => true,
                'compare' => '=',
                'type'    => 'BINARY',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_previous_id_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['previous_id'] ) ) {
            $previous_id = intval( $query_vars['classifieds_query']['previous_id'] );

            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_old_id',
                'value'   => $previous_id,
                'compare' => '=',
                'type'    => 'NUMERIC',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_category_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['category'] ) ) {
            $terms = $this->sanitize_terms( $query_vars['classifieds_query']['category'] );

            $query_vars['tax_query'][] = array(
                'taxonomy'         => $this->categories_taxonomy,
                'field'            => 'term_id',
                'terms'            => $terms,
                'include_children' => true,
            );
        }

        return $query_vars;
    }

    /**
     * @param mixed $terms  An integer or array of terms IDs.
     * @since 4.0.0
     */
    private function sanitize_terms( $terms ) {
        if ( ! is_array( $terms ) ) {
            $terms = array( $terms );
        }

        return array_filter( array_map( 'intval', $terms ) );
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_category__not_in_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['category__not_in'] ) ) {
            $terms = $this->sanitize_terms( $query_vars['classifieds_query']['category__not_in'] );

            $query_vars['tax_query'][] = array(
                'taxonomy'         => $this->categories_taxonomy,
                'field'            => 'term_id',
                'terms'            => $terms,
                'include_children' => true,
                'operator'         => 'NOT IN',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_category__exclude_children_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['category__exclude_children'] ) ) {
            $terms = $this->sanitize_terms( $query_vars['classifieds_query']['category__exclude_children'] );

            $query_vars['tax_query'][] = array(
                'taxonomy'         => $this->categories_taxonomy,
                'field'            => 'term_id',
                'terms'            => $terms,
                'include_children' => false,
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_contact_name_query_parameter( $query_vars ) {
        if ( ! empty( $query_vars['classifieds_query']['contact_name'] ) ) {
            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_contact_name',
                'value'   => $query_vars['classifieds_query']['contact_name'],
                'compare' => '=',
                'type'    => 'CHAR',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_price_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['price'] ) ) {
            $price = $this->sanitize_price( $query_vars['classifieds_query']['price'] );

            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_price',
                'value'   => $price,
                'compare' => '=',
                'type'    => 'SIGNED',
            );
        }

        return $query_vars;
    }

    /**
     * @param mixed $price  The price provided for the query.
     * @since 4.0.0
     */
    private function sanitize_price( $price ) {
        return round( floatval( $price ) * 100 );
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_min_price_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['min_price'] ) ) {
            $price = $this->sanitize_price( $query_vars['classifieds_query']['min_price'] );

            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_price',
                'value'   => $price,
                'compare' => '>=',
                'type'    => 'SIGNED',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_max_price_query_parameter( $query_vars ) {
        if ( isset( $query_vars['classifieds_query']['max_price'] ) ) {
            $price = $this->sanitize_price( $query_vars['classifieds_query']['max_price'] );

            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_price',
                'value'   => $price,
                'compare' => '<=',
                'type'    => 'SIGNED',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_payment_status_query_parameter( $query_vars ) {
        if ( ! empty( $query_vars['classifieds_query']['payment_status'] ) ) {
            $payment_status = $query_vars['classifieds_query']['payment_status'];

            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_payment_status',
                'value'   => $payment_status,
                'compare' => is_array( $payment_status ) ? 'IN' : '=',
                'type'    => 'CHAR',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_payment_status__not_in_query_parameter( $query_vars ) {
        if ( ! empty( $query_vars['classifieds_query']['payment_status__not_in'] ) ) {
            $payment_status = (array) $query_vars['classifieds_query']['payment_status__not_in'];

            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_payment_status',
                'value'   => $payment_status,
                'compare' => 'NOT IN',
                'type'    => 'CHAR',
            );
        }

        return $query_vars;
    }

    /**
     * @param array $query_vars     An array of query vars.
     * @since 4.0.0
     */
    public function process_payer_email_query_parameter( $query_vars ) {
        if ( ! empty( $query_vars['classifieds_query']['payer_email'] ) ) {
            $query_vars['meta_query'][] = array(
                'key'     => '_awpcp_payer_email',
                'value'   => $query_vars['classifieds_query']['payer_email'],
                'compare' => '=',
                'type'    => 'CHAR',
            );
        }

        return $query_vars;
    }
}
