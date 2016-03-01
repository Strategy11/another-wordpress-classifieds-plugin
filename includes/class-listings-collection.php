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
    public function find_valid_listings( $query = array() ) {
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
    public function count_valid_listings( $query = array() ) {
        return $this->count_successfully_paid_listings( $this->make_valid_listings_query( $query ) );
    }

    /**
     * @since feature/1112
     */
    public function find_listings( $query = array() ) {
        $query = $this->prepare_listings_query( $query );
        $query = $this->make_listings_query_with_orderby_paramter( $query );

        if ( isset( $query['_meta_order'] ) ) {
            add_filter( 'posts_clauses', array( $this, 'add_orderby_multiple_meta_keys_clause' ), 10, 2 );
        }

        if ( isset( $query['_custom_order'] ) ) {
            add_filter( 'posts_clauses', array( $this, 'add_orderby_unsupported_properties_clause' ), 10, 2 );
        }

        $posts = $this->wordpress->create_posts_query( $query );

        if ( isset( $query['_meta_order'] ) ) {
            remove_filter( 'posts_clauses', array( $this, 'add_orderby_multiple_meta_keys_clause' ), 10, 2 );
        }

        if ( isset( $query['_custom_order'] ) ) {
            remove_filter( 'posts_clauses', array( $this, 'add_orderby_unsupported_properties_clause' ), 10, 2 );
        }

        return $posts->posts;
    }

    private function prepare_listings_query( $query ) {
        $query['post_type'] = 'awpcp_listing';

        if ( ! isset( $query['post_status'] ) ) {
            $query['post_status'] = array(  'disabled', 'draft', 'pending', 'publish' );
        }

        return $query;
    }

    private function make_listings_query_with_orderby_paramter( $query ) {
        $orderby = isset( $query['orderby'] ) ? $query['orderby'] : null;

        $basedate = 'CASE WHEN renewed_date IS NULL THEN ad_startdate ELSE GREATEST(ad_startdate, renewed_date) END';
        $is_paid = 'CASE WHEN ad_fee_paid > 0 THEN 1 ELSE 0 END';

        switch ( $orderby ) {
            case 1:
                // TODO: populate _most_recent_start_date when listing is created.
                $query['meta_key'] = '_most_recent_start_date';
                $query['meta_type'] = 'DATETIME';
                $query['orderby'] = array( 'meta_value' => 'DESC' );
                break;

            case 2:
                $query['orderby'] = array( 'title' => 'ASC' );
                break;

            case 3:
                $query['orderby'] = 'menu_order';
                $query['_meta_order'] = array( '_is_paid' => 'DESC', '_most_recent_start_date' => 'DESC' );
                $query['_meta_type'] = array( '_is_paid' => 'SIGNED', '_most_recent_start_date' => 'DATETIME' );

                $query['meta_query'][] = array(
                    'key' => '_is_paid',
                    'compare' => 'EXISTS',
                );

                $query['meta_query'][] = array(
                    'key' => '_most_recent_start_date',
                    'compare' => 'EXISTS',
                );
                break;

            case 4:
                $query['meta_key'] = '_is_paid';
                $query['meta_type'] = 'SIGNED';
                $query['orderby'] = array( 'meta_value' => 'DESC', 'title' => 'ASC' );
                break;

            case 5:
                $query['meta_key'] = '_views';
                $query['meta_type'] = 'SIGNED';
                $query['orderby'] = array( 'meta_value' => 'DESC', 'title' => 'ASC' );
                break;

            case 6:
                $query['orderby'] = 'menu_order';
                $query['_meta_order'] = array( '_views' => 'DESC', '_most_recent_start_date' => 'DESC' );
                $query['_meta_type'] = array( '_views' => 'SIGNED', '_most_recent_start_date' => 'DATETIME' );

                $query['meta_query'][] = array(
                    'key' => '_views',
                    'compare' => 'EXISTS',
                );

                $query['meta_query'][] = array(
                    'key' => '_most_recent_start_date',
                    'compare' => 'EXISTS',
                );
                break;

            case 7:
                $query['orderby'] = 'menu_order';
                $query['_meta_order'] = array( '_price' => 'DESC', '_most_recent_start_date' => 'DESC' );
                $query['_meta_type'] = array( '_price' => 'SIGNED', '_most_recent_start_date' => 'DATETIME' );

                $query['meta_query'][] = array(
                    'key' => '_price',
                    'compare' => 'EXISTS',
                );

                $query['meta_query'][] = array(
                    'key' => '_most_recent_start_date',
                    'compare' => 'EXISTS',
                );
                break;

            case 8:
                $query['orderby'] = 'menu_order';
                $query['_meta_order'] = array( '_price' => 'ASC', '_most_recent_start_date' => 'DESC' );
                $query['_meta_type'] = array( '_price' => 'SIGNED', '_most_recent_start_date' => 'DATETIME' );

                $query['meta_query'][] = array(
                    'key' => '_price',
                    'compare' => 'EXISTS',
                );

                $query['meta_query'][] = array(
                    'key' => '_most_recent_start_date',
                    'compare' => 'EXISTS',
                );
                break;

            case 9:
                // TODO: populate _most_recent_start_date when listing is created.
                $query['meta_key'] = '_most_recent_start_date';
                $query['meta_type'] = 'DATETIME';
                $query['orderby'] = array( 'meta_value' => 'ASC' );
                break;

            case 10:
                $query['orderby'] = array( 'title' => 'DESC' );
                break;

            case 11:
                $query['meta_key'] = '_views';
                $query['meta_type'] = 'SIGNED';
                $query['orderby'] = array( 'meta_value' => 'ASC', 'title' => 'ASC' );
                break;

            case 12:
                $query['orderby'] = 'menu_order';
                $query['_meta_order'] = array( '_views' => 'ASC', '_most_recent_start_date' => 'ASC' );
                $query['_meta_type'] = array( '_views' => 'SIGNED', '_most_recent_start_date' => 'DATETIME' );

                $query['meta_query'][] = array(
                    'key' => '_views',
                    'compare' => 'EXISTS',
                );

                $query['meta_query'][] = array(
                    'key' => '_most_recent_start_date',
                    'compare' => 'EXISTS',
                );
                break;

            case 'title':
                $query['orderby'] = array( 'title' => $query['order'] );
                break;

            case 'start-date':
                $query['meta_key'] = '_start_date';
                $query['meta_type'] = 'DATETIME';
                $query['orderby'] = array( 'meta_value' => $query['order'] );
                break;

            case 'end-date':
                $query['meta_key'] = '_start_date';
                $query['meta_type'] = 'DATETIME';
                $query['orderby'] = array( 'meta_value' => $query['order'] );
                break;

            case 'renewed-date':
                $query['orderby'] = array( 'menu_order' => 'DESC', 'ID' => $query['order'] );
                $query['_meta_order'] = array( '_most_recent_start_date' => $query['order'], '_renewed_date' => $query['order'] );
                $query['_meta_type'] = array( '_most_recent_start_date' => 'DATETIME', '_renewed_date' => 'DATETIME' );

                $query['meta_query'][] = array(
                    'key' => '_most_recent_start_date',
                    'compare' => 'EXISTS',
                );

                $query['meta_query'][] = array(
                    'key' => '_renewed_date',
                    'compare' => 'EXISTS',
                );
                break;

            case 'status':
                $query['meta_key'] = '_start_date';
                $query['orderby'] = array( 'menu_order' => 'DESC', 'meta_value' => $query['order'], 'ID' => $query['order'] );
                $query['_custom_order'] = array( 'post_status' => $query['order'] );
                break;

            case 'payment-term':
                $query['orderby'] = array( 'menu_order' => 'DESC', 'ID' => $query['order'] );
                $query['_meta_order'] = array( '_payment_term_id' => $query['order'], '_start_date' => $query['order'] );
                $query['_meta_type'] = array( '_payment_term_id' => 'DATETIME', '_start_date' => 'DATETIME' );

                $query['meta_query'][] = array(
                    'key' => '_payment_term_id',
                    'compare' => 'EXISTS',
                );

                $query['meta_query'][] = array(
                    'key' => '_start_date',
                    'compare' => 'EXISTS',
                );
                break;

            case 'payment-status':
                $query['orderby'] = array( 'menu_order' => 'DESC', 'ID' => $query['order'] );
                $query['_meta_order'] = array( '_payment_status' => $query['order'], '_start_date' => $query['order'] );
                $query['_meta_type'] = array( '_payment_status' => 'DATETIME', '_start_date' => 'DATETIME' );

                $query['meta_query'][] = array(
                    'key' => '_payment_status',
                    'compare' => 'EXISTS',
                );

                $query['meta_query'][] = array(
                    'key' => '_start_date',
                    'compare' => 'EXISTS',
                );
                break;

            case 'featured-ad':
                $query['orderby'] = array( 'menu_order' => 'DESC', 'ID' => $query['order'] );
                $query['_meta_order'] = array( '_is_featured' => $query['order'], '_start_date' => $query['order'] );
                $query['_meta_type'] = array( '_is_featured' => 'DATETIME', '_start_date' => 'DATETIME' );

                $query['meta_query'][] = array(
                    'key' => '_is_featured',
                    'compare' => 'EXISTS',
                );

                $query['meta_query'][] = array(
                    'key' => '_start_date',
                    'compare' => 'EXISTS',
                );
                break;
                break;

            case 'owner':
                $query['meta_key'] = '_start_date';
                $query['meta_type'] = 'DATETIME';
                $query['orderby'] = array( 'author' => $query['order'], 'meta_value' => $query['order'], 'ID' => $query['order'] );
                break;

            case 'random':
                $query['orderby'] = 'rand';
                break;

            default:
                $query['orderby'] = array( 'post_date' => 'DESC', 'title' => 'ASC' );
                break;
        }

        // TODO: run 'awpcp-ad-order-conditions' and 'awpcp-find-listings-order-conditions' filters?
        // I think is better to remove these filters and let modules filter the query before is executed.

        return $query;
    }

    /**
     * Based on code found in http://wordpress.stackexchange.com/a/67391
     *
     * See http://www.billerickson.net/wp-query-sort-by-meta/. This function
     * won't be necessary when WP 4.2 becomes the minimum supported version.
     */
    public function add_orderby_multiple_meta_keys_clause( $clauses, $query_object) {
        $orderby = array();

        foreach ( $query_object->query['_meta_order'] as $meta_key => $order ) {
            $regexp = "/([\w_]+)\.meta_key = '" . preg_quote( $meta_key ) . "'/";

            if ( ! preg_match( $regexp, $clauses['where'], $matches ) ) {
                continue;
            }

            $meta_type = $query_object->query['_meta_type'][ $meta_key ];

            $orderby[] = "CAST({$matches[1]}.meta_value AS {$meta_type}) $order";
        }

        if ( ! empty( $orderby ) ) {
            $clauses['orderby'] = preg_replace( '/[\w_]+\.menu_order DESC/', implode( ', ', $orderby ), $clauses['orderby'] );
        }

        return $clauses;
    }

    public function add_orderby_unsupported_properties_clause( $clauses, $query_object ) {
        if ( ! preg_match( '/(\w+)\.menu_order DESC/', $clauses['orderby'], $matches ) ) {
            return $clauses;
        }

        $orderby = array();
        $posts_table = $matches[1];

        foreach ( $query_object->query['_custom_order'] as $property => $order ) {
            switch ( $property ) {
                case 'post_status':
                    $orderby[] = "$posts_table.post_status $order";
            }
        }

        if ( ! empty( $orderby ) ) {
            $clauses['orderby'] = str_replace( "$posts_table.menu_order DESC", implode( ', ', $orderby ), $clauses['orderby'] );
        }

        return $clauses;
    }

    /**
     * @since 3.3
     */
    public function count_listings( $query = array() ) {
        $posts = $this->wordpress->create_posts_query();
        $posts->query( $this->prepare_listings_query( $query ) );
        return $posts->found_posts;
    }

    /**
     * TODO: Consdier order conditions (See Ad::get_order_conditions,
     *       Ad::get_enabled_ads (origin/master) and groupbrowseadsby option).
     *
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

    public function find_disabled_listings( $query = array() ) {
        return $this->find_valid_listings( $this->make_disabled_listings_query( $query ) );
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

    public function find_expired_listings( $query = array() ) {
        return $this->find_valid_listings( $this->make_expired_listings_query( $query ) );
    }

    private function make_expired_listings_query( $query ) {
        $query['meta_query'][] = array(
            'key' => '_end_date',
            'value' => current_time( 'mysql' ),
            'compare' => '<=',
            'type' => 'DATE',
        );

        return $query;
    }

    public function count_expired_listings( $query = array() ) {
        return $this->count_valid_listings( $this->make_expired_listings_query( $query ) );
    }

    public function find_listings_awaiting_approval( $query = array() ) {
        return $this->find_valid_listings( $this->make_listings_awaiting_approval_query( $query ) );
    }

    private function make_listings_awaiting_approval_query( $query ) {
        $query['meta_query'][] = array(
            'key' => '_disabled_date',
            'compare' => 'NOT EXISTS',
        );

        return $this->make_disabled_listings_query( $query );
    }

    public function count_listings_awaiting_approval( $query = array() ) {
        return $this->count_valid_listings( $this->make_listings_awaiting_approval_query( $query ) );
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
    public function find_user_listings( $user_id, $query = array() ) {
        return $this->find_valid_listings( $this->make_user_listings_query( $user_id, $query ) );
    }

    private function make_user_listings_query( $user_id, $query ) {
        $query['author'] = $user_id;
        return $query;
    }

    /**
     * @since 3.3
     * @since feature/1112  Modified to work with custom post types.
     */
    public function count_user_listings( $user_id, $query = array() ) {
        return $this->count_valid_listings( $this->make_user_listings_query( $user_id, $query ) );
    }

    /**
     * @since 3.3
     * @since feature/1112  Modified to work with custom post types.
     */
    public function find_user_enabled_listings( $user_id, $query = array() ) {
        return $this->find_enabled_listings( $this->make_user_listings_query( $user_id, $query ) );
    }

    /**
     * @since 3.3
     * @since feature/1112  Modified to work with custom post types.
     */
    public function count_user_enabled_listings( $user_id, $query = array() ) {
        return $this->count_enabled_listings( $this->make_user_listings_query( $user_id, $query ) );
    }

    /**
     * @since 3.3
     * @since feature/1112  Modified to work with custom post types.
     */
    public function find_user_disabled_listings( $user_id, $query = array() ) {
        return $this->find_disabled_listings( $this->make_user_listings_query( $user_id, $query ) );
    }

    /**
     * @since 3.3
     * @since feature/1112  Modified to work with custom post types.
     */
    public function count_user_disabled_listings( $user_id, $query = array() ) {
        return $this->count_disabled_listings( $this->make_user_listings_query( $user_id, $query ) );
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
