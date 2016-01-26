<?php

/**
 * @since 3.3
 */
function awpcp_listings_collection() {
    return new AWPCP_ListingsCollection(
        awpcp_listings_finder(),
        awpcp()->settings,
        awpcp_wordpress(),
        $GLOBALS['wpdb']
    );
}

/**
 * @since 3.2.2
 */
class AWPCP_ListingsCollection {

    private $finder;
    private $settings;
    private $wordpress;
    private $db;

    public function __construct( $finder, $settings, $wordpress, $db ) {
        $this->finder = $finder;
        $this->settings = $settings;
        $this->wordpress = $wordpress;
        $this->db = $db;
    }

    /**
     * @since feature/1112 works with custom post types.
     * @since 3.3
     */
    public function get( $listing_id ) {
        if ( $listing_id <= 0 ) {
            $message = __( 'The listing ID must be a positive integer, %d was given.', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( sprintf( $message, $listing_id ) );
        }

        $listing = $this->wordpress->get_post( $listing_id );

        if ( empty( $listing ) ) {
            $message = __( 'No Listing was found with id: %d.', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( sprintf( $message, $listing_id ) );
        }

        return $listing;
    }

    /**
     * @since 3.2.2
     */
    public function find_by_id( $ad_id ) {
        return AWPCP_Ad::find_by_id( $ad_id );
    }

    /**
     * @since 3.3
     */
    public function find_all_by_id( $identifiers ) {
        $identifiers = array_filter( array_map( 'intval', $identifiers ) );

        if ( count( $identifiers ) > 0 ) {
            $where = 'ad_id IN ( ' . implode( ',', $identifiers ) . ' )';
            return AWPCP_Ad::query( array( 'where' => $where ) );
        } else {
            return array();
        }
    }

    /**
     * @since 3.3
     * @since feature/1112  Modified to work with custom post types.
     */
    private function find_valid_listings( $query = array() ) {
        return $this->find_successfully_paid_listings( $this->make_valid_listings_query( $query ) );
    }

    private function make_valid_listings_query( $query ) {
        $query['meta_query'][] = array(
            'key' => '_verified',
            'value' => true,
            'compare' => '=',
            'type' => 'BINARY',
        );

        return $query;
    }

    /**
     * @since 3.3
     */
    private function count_valid_listings( $query = array() ) {
        return $this->count_successfully_paid_listings( $this->make_valid_listings_query( $query ) );
    }

    /**
     * @since feature/1112
     */
    public function find_listings( $query = array() ) {
        $posts = new WP_Query( $this->prepare_listings_query( $query ) );
        return $posts->posts;
    }

    private function prepare_listings_query( $query ) {
        $query['post_type'] = 'awpcp_listing';

        if ( ! isset( $query['post_status'] ) ) {
            $query['post_status'] = array( 'draft', 'pending', 'publish', 'disabled' );
        }

        return $query;
    }

    /**
     * @since 3.3
     */
    public function count_listings( $query = array() ) {
        $posts = new WP_Query();
        $posts->query( $this->prepare_listings_query( $query ) );
        return $posts->found_posts;
    }

    /**
     * @since 3.3
     * @since feature/1112  Modified to work with custom post types.
     */
    public function find_enabled_listings( $query = array() ) {
        return $this->find_valid_listings( $this->make_enabled_listings_query( $query ) );
    }

    private function make_enabled_listings_query( $query ) {
        $query['post_status'] = 'publish';
        return $query;
    }

    private function make_disabled_listings_query( $query ) {
        $query['post_status'] = 'disabled';
        return $query;
    }

    public function find_listings_about_to_expire( $query = array() ) {
        return $this->find_enabled_listings( $this->make_listings_about_to_expire_query( $query ) );
    }

    private function make_listings_about_to_expire_query( $query ) {
        $threshold = intval( get_awpcp_option( 'ad-renew-email-threshold' ) );
        $target_date = strtotime( "+ $threshold days", current_time( 'timestamp' ) );

        $query['meta_query'][] = array(
            'key' => '_end_date',
            'value' => awpcp_datetime( 'mysql', $target_date ),
            'compare' => '<=',
            'type' => 'DATE',
        );

        $query['meta_query'][] = array(
            'key' => '_renew_email_sent',
            'compare' => 'NOT EXISTS',
        );

        return $query;
    }

    public function find_expired_listings_with_query( $query ) {
        return $this->finder->find( $this->make_expired_listings_query( $query ) );
    }

    private function make_expired_listings_query( $query ) {
        $query['end_date'] = array( 'compare' => '<', 'value' => current_time( 'mysql' ) );
        return $this->make_valid_listings_query( $query );
    }

    public function find_listings_awaiting_approval_with_query( $query ) {
        return $this->finder->find( $this->make_listings_awaiting_approval_query( $query ) );
    }

    private function make_listings_awaiting_approval_query( $query ) {
        $query = array_merge( $query, array( 'disabled' => true, 'disabled_date' => 'NULL' ) );
        return $this->make_valid_listings_query( $query );
    }

    public function find_successfully_paid_listings( $query ) {
        return $this->find_listings( $this->make_successfully_paid_listings_query( $query ) );
    }

    private function make_successfully_paid_listings_query( $query ) {
        $enable_listings_pending_payment = $this->settings->get_option( 'enable-ads-pending-payment' );
        $payments_are_enabled = $this->settings->get_option( 'freepay' ) == 1;

        if ( ! $enable_listings_pending_payment && $payments_are_enabled ) {
            $query['meta_query'][] = array(
                'key' => '_payment_status',
                'value' => array( 'Pending', 'Unpaid' ),
                'compare' => 'NOT IN',
                'type' => 'char'
            );
        } else {
            $query['meta_query'][] = array(
                'key' => '_payment_status',
                'value' => 'Unpaid',
                'compare' => '!=',
                'type' => 'char'
            );
        }

        return $query;
    }

    /**
     * @since feature/1112
     */
    public function count_successfully_paid_listings( $query = array() ) {
        return $this->count_listings( $this->make_successfully_paid_listings_query( $query ) );
    }

    public function find_listings_with_query( $query ) {
        return $this->finder->find( $query );
    }

    /**
     * @since feature/1112
     */
    public function find_listings_awaiting_verification( $query ) {
        return $this->find_listings( $this->make_listings_awaiting_verification_query( $query ) );
    }

    /**
     * @since feature/1112
     */
    private function make_listings_awaiting_verification_query( $query ) {
        $query['meta_query'][] = array(
            'key' => '_verification_needed',
            'value' => true,
            'compare' => '=',
            'type' => 'BINARY',
        );

        return $query;
    }

    /**
     * @since 3.3
     * @since feature/1112  Modified to work with custom post types.
     */
    public function count_enabled_listings( $query = array() ) {
        return $this->count_valid_listings( $this->make_enabled_listings_query( $query ) );
    }

    /**
     * @since feature/1112.
     */
    public function count_disabled_listings( $query = array() ) {
        return $this->count_valid_listings( $this->make_disabled_listings_query( $query ) );
    }

    public function count_expired_listings_with_query( $query ) {
        return $this->finder->count( $this->make_expired_listings_query( $query ) );
    }

    public function count_listings_awaiting_approval_with_query( $query ) {
        return $this->finder->count( $this->make_listings_awaiting_approval_query( $query ) );
    }

    public function count_valid_listings_with_query( $query ) {
        return $this->finder->count( $this->make_valid_listings_query( $query ) );
    }

    public function count_listings_with_query( $query ) {
        return $this->finder->count( $query );
    }

    /**
     * @since 3.3
     */
    public function find_user_listings( $user_id, $params = array() ) {
        $params = array_merge( $params, array(
            'conditions' => array( $this->db->prepare( 'user_id = %d', $user_id ) )
        ) );

        return $this->find_valid_listings( $params );
    }

    /**
     * @since 3.3
     */
    public function count_user_listings( $user_id ) {
        $conditions = array( $this->db->prepare( 'user_id = %d', $user_id ) );
        return $this->count_valid_listings( $conditions );
    }

    /**
     * @since 3.3
     */
    public function find_user_enabled_listings( $user_id, $params = array() ) {
        $params = array_merge( $params, array(
            'conditions' => array( $this->db->prepare( 'user_id = %d', $user_id ), 'disabled = 0' )
        ) );

        return $this->find_valid_listings( $params );
    }

    /**
     * @since 3.3
     */
    public function count_user_enabled_listings( $user_id ) {
        $conditions = array( $this->db->prepare( 'user_id = %d', $user_id ), 'disabled = 0' );
        return $this->count_valid_listings( $conditions );
    }

    /**
     * @since 3.3
     */
    public function find_user_disabled_listings( $user_id, $params = array() ) {
        $params = array_merge( $params, array(
            'conditions' => array( $this->db->prepare( 'user_id = %d', $user_id ), 'disabled = 1' )
        ) );

        return $this->find_valid_listings( $params );
    }

    /**
     * @since 3.3
     */
    public function count_user_disabled_listings( $user_id ) {
        $conditions = array( $this->db->prepare( 'user_id = %d', $user_id ), 'disabled = 1' );
        return $this->count_valid_listings( $conditions );
    }

    /**
     * @since 3.3.2
     */
    public function count_enabled_listings_in_category( $category_id ) {
        $category_condition = '( ad_category_id = %1$d OR ad_category_parent_id = %1$d )';

        $conditions = array(
            $this->db->prepare( $category_condition, $category_id ),
            'disabled = 0',
        );

        return $this->count_valid_listings( $conditions );
    }
}
