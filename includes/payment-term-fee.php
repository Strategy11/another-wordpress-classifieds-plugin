<?php
/**
 * Model definition for Fee, the main payment term in the plugin.
 *
 * @package AWPCP
 */

/**
 * The default type of payment term used to pay for ads.
 */
class AWPCP_Fee extends AWPCP_PaymentTerm {

    public $type = AWPCP_FeeType::TYPE;

    public $regions;

    public $defaults = array();

    public $private;

    public $featured;

    public static function create_from_db( $object ) {
        switch ( $object->rec_increment ) {
            case 'D':
                $interval = self::INTERVAL_DAY;
                break;
            case 'W':
                $interval = self::INTERVAL_WEEK;
                break;
            case 'M':
                $interval = self::INTERVAL_MONTH;
                break;
            case 'Y':
                $interval = self::INTERVAL_YEAR;
                break;
        }

        $params = array(
            // Standard.
            'id'                => $object->adterm_id,
            'name'              => $object->adterm_name,
            'description'       => $object->description,
            'duration_amount'   => $object->rec_period,
            'duration_interval' => $object->rec_increment,
            'price'             => $object->amount,
            'credits'           => $object->credits,
            'categories'        => array(),
            'featured'          => $object->is_featured_ad_pricing,
            'characters'        => $object->characters_allowed,
            'title_characters'  => $object->title_characters,
            'images'            => $object->imagesallowed,
            'regions'           => $object->regions,
            'ads'               => 1,
            'private'           => $object->private,
            // Custom.
            'buys'              => $object->buys,
        );

        $params = apply_filters( 'awpcp-get-payment-term-fee-params-from-db', $params, $object );

        return new AWPCP_Fee( $params );
    }

    public static function query( $args = array() ) {
        global $wpdb;

        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        extract(
            wp_parse_args(
                $args,
                array(
                    'fields'  => '*',
                    'where'   => '1 = 1',
                    'orderby' => 'adterm_name',
                    'order'   => 'asc',
                    'offset'  => 0,
                    'limit'   => 0,
                )
            )
        );

        $query = 'SELECT %s FROM ' . AWPCP_TABLE_ADFEES . ' ';

        if ( $fields === 'count' ) {
            $query = sprintf( $query, 'COUNT(adterm_id)' );
            $limit = 0;
        } else {
            $fields .= ', CASE rec_increment ';
            $fields .= "WHEN 'D' THEN 1 ";
            $fields .= "WHEN 'W' THEN 2 ";
            $fields .= "WHEN 'M' THEN 3 ";
            $fields .= "WHEN 'Y' THEN 4 END AS _duration_interval ";
            $query   = sprintf( $query, $fields );
        }

        $query .= sprintf( 'WHERE %s ', $where );
        $query .= sprintf( 'ORDER BY %s %s ', $orderby, strtoupper( $order ) );

        if ( $limit > 0 ) {
            $query .= $wpdb->prepare( 'LIMIT %d, %d', $offset, $limit );
        }

        if ( $fields === 'count' ) {
            return $wpdb->get_var( $query );
        }

        $items = $wpdb->get_results( $query );

        $results = array();
        foreach ( $items as $item ) {
            $results[] = self::create_from_db( $item );
        }

        return $results;
    }

    public static function find_by_id( $id ) {
        $args = array( 'where' => sprintf( 'adterm_id = %d', absint( $id ) ) );
        $fees = self::query( $args );
        return ! empty( $fees ) ? array_shift( $fees ) : null;
    }

    public static function delete( $id, &$errors ) {
        global $wpdb;

        $plan = self::find_by_id( $id );
        if ( is_null( $plan ) ) {
            $errors[] = __( "The Fee doesn't exist.", 'another-wordpress-classifieds-plugin' );
            return false;
        }

        $ads = awpcp_listings_collection()->find_listings(
            array(
                'meta_query' => array(
                    array(
                        'key'   => '_awpcp_payment_term_id',
                        'value' => $id,
                    ),
                    array(
                        'key'   => '_awpcp_payment_term_type',
                        'value' => 'fee',
                    ),
                ),
            )
        );

        if ( ! empty( $ads ) ) {
            $errors[] = __( "The Fee can't be deleted because there are active Ads in the system that are associated with the Fee ID.", 'another-wordpress-classifieds-plugin' );
            return false;
        }

        $query  = 'DELETE FROM ' . AWPCP_TABLE_ADFEES . ' WHERE adterm_id = %d';
        $result = $wpdb->query( $wpdb->prepare( $query, $id ) );

        return $result !== false;
    }

    protected function prepare_default_properties() {
        parent::prepare_default_properties();

        if ( ! isset( $this->defaults['buys'] ) ) {
            $this->defaults['buys'] = 0;
        }

        $this->defaults = apply_filters( 'awpcp-prepare-payment-term-fee-default-properties', $this->defaults );

        return $this->defaults;
    }

    protected function sanitize( $data ) {
        $data = parent::sanitize( $data );

        $data['ads']  = 1;
        $data['buys'] = (int) $data['buys'];

        return apply_filters( 'awpcp-sanitize-payment-term-fee-data', $data );
    }

    protected function validate( $data, &$errors = array() ) {
        parent::validate( $data, $errors );
        return empty( $errors );
    }

    protected function translate( $_data ) {
        $data['adterm_id']              = absint( $_data['id'] );
        $data['adterm_name']            = stripslashes( $_data['name'] );
        $data['description']            = stripslashes( $_data['description'] );
        $data['amount']                 = $_data['price'];
        $data['credits']                = absint( $_data['credits'] );
        $data['rec_period']             = absint( $_data['duration_amount'] );
        $data['rec_increment']          = $_data['duration_interval'];
        $data['imagesallowed']          = absint( $_data['images'] );
        $data['regions']                = absint( $_data['regions'] );
        $data['title_characters']       = absint( $_data['title_characters'] );
        $data['characters_allowed']     = absint( $_data['characters'] );
        $data['categories']             = $_data['categories'];
        $data['buys']                   = absint( $_data['buys'] );
        $data['private']                = absint( $_data['private'] );
        $data['is_featured_ad_pricing'] = absint( $_data['featured'] );

        if ( $data['adterm_id'] === 0 || is_null( $data['adterm_id'] ) || strlen( $data['adterm_id'] ) === 0 ) {
            unset( $data['adterm_id'] );
        }

        return apply_filters( 'awpcp-translate-payment-term-fee-data', $data, $_data );
    }

    public function save( &$errors = array() ) {
        global $wpdb;

        $data = array();
        foreach ( $this->defaults as $name => $default ) {
            $data[ $name ] = $this->$name;
        }

        $data = $this->sanitize( $data );

        // Categories are saved as a comma separated string, for now.
        if ( is_array( $data['categories'] ) ) {
            $data['categories'] = join( ',', $data['categories'] );
        }

        if ( $this->validate( $data, $errors ) ) {
            $data = $this->translate( $data );

            // Do not save the free listing.
            if ( $this->id === 0 ) {
                $result = true;
            } elseif ( $this->id ) {
                $result = $wpdb->update( AWPCP_TABLE_ADFEES, $data, array( 'adterm_id' => $this->id ) );

                if ( $result !== false ) {
                    do_action( 'awpcp-payment-term-fee-updated', $this );
                }
            } else {
                $result   = $wpdb->insert( AWPCP_TABLE_ADFEES, $data );
                $this->id = $wpdb->insert_id;

                if ( $result !== false ) {
                    do_action( 'awpcp-payment-term-fee-created', $this );
                }
            }
        } else {
            $result = false;
        }

        return $result !== false;
    }

    public function transfer_ads_to( $id, &$errors ) {
        $recipient = self::find_by_id( $id );

        if ( is_null( $recipient ) ) {
            $errors[] = __( "The recipient Fee doesn't exists.", 'another-wordpress-classifieds-plugin' );
        }

        $listings = awpcp_listings_collection()->find_listings(
            array(
                'meta_query' => array(
                    array(
                        'key'     => '_awpcp_payment_term_id',
                        'value'   => $this->id,
                        'compare' => '=',
                        'type'    => 'SIGNED',
                    ),
                    array(
                        'key'   => '_awpcp_payment_term_type',
                        'value' => 'fee',
                    ),
                ),
            )
        );

        $listings_payments = awpcp()->container['ListingsPayments'];
        $success           = true;

        foreach ( $listings as $listing ) {
            $success = $success && $listings_payments->update_listing_payment_term( $listing, $recipient );
        }

        return $success;
    }

    public function get_regions_allowed() {
        return $this->regions;
    }
}
