<?php

function awpcp_listings_finder() {
    return new AWPCP_ListingsFinder( $GLOBALS['wpdb'] );
}

class AWPCP_ListingsFinder {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
    }

    public function find( $user_query ) {
        $query = $this->normalize_query( $user_query );

        $select = $this->build_select_clause( $query );
        $where = $this->build_where_clause( $query );
        $limit = $this->build_limit_clause( $query );
        $order = $this->build_order_clause( $query );

        $sql = "$select $where $order";
        $sql = str_replace( '<listings-table>', AWPCP_TABLE_ADS, $sql );
        $sql = str_replace( '<listing-regions-table>', AWPCP_TABLE_AD_REGIONS, $sql );

        if ( $query['fields'] == 'count' ) {
            // debugp( $sql, $this->db->get_var( $sql ) );
            return $this->db->get_var( $sql );
        } else {
            $items = $this->db->get_results( $sql );

            $results = array();
            foreach( $items as $item ) {
                $results[] = AWPCP_Ad::from_object($item);
            }

            return $results;
        }
    }

    private function normalize_query( $user_query ) {
        $query = wp_parse_args( $user_query, array(
            'fields' => '*',

            'category_id' => null,

            'contact_name' => null,

            'price' => null,
            'min_price' => null,
            'max_price' => null,

            'region' => '',
            'country' => '',
            'state' => '',
            'city' => '',
            'county' => '',
            'regions' => array(),

            'disabled' => null,
            'verified' => null,

            'limit' => 0,
            'offset' => 0,
            'order' => 'default'
        ) );

        $query['regions'] = $this->normalize_regions_query( $query );
        $query['limit'] = $query['limit'] > 0 ? $query['limit'] : get_awpcp_option( 'adresultsperpage', 10 );

        return $query;
    }

    private function normalize_regions_query( $query ) {
        // search for a listing associated with a Region (of any kind) whose
        // name matches the given search value.
        $query['regions'][] = array( 'country' => $query['region'] );
        $query['regions'][] = array( 'state' => $query['region'] );
        $query['regions'][] = array( 'city' => $query['region'] );
        $query['regions'][] = array( 'county' => $query['region'] );

        // search for a listing associated with region hierarchy that matches
        // the given search values.
        $query['regions'][] = array(
            'country' => empty( $query['country'] ) ? '' : array( '=', $query['country'] ),
            'state' => empty( $query['state'] ) ? '' : array( '=', $query['state'] ),
            'city' => empty( $query['city'] ) ? '' : array( '=', $query['city'] ),
            'county' => empty( $query['county'] ) ? '' : array( '=', $query['county'] ),
        );

        return awpcp_array_filter_recursive( $query['regions'] );
    }

    private function build_select_clause( $query ) {
        if ( $query['fields'] == 'count' ) {
            $fields = 'COUNT( DISTINCT listings.`ad_id` )';
        } else {
            $fields = $query['fields'];
        }

        if ( ! empty( $query['regions'] ) ) {
            $tables = '<listings-table> AS listings INNER JOIN <listing-regions-table> AS listing_regions ';
            $tables.= 'ON listings.`ad_id` = listing_regions.`ad_id`';
        } else {
            $tables = '<listings-table> AS listings';
        }

        return "SELECT $fields FROM $tables";
    }

    private function build_where_clause( $query ) {
        $conditions = array(
            $this->build_keyword_condition( $query ),
            $this->build_category_condition( $query ),
            $this->build_contact_condition( $query ),
            $this->build_price_condition( $query ),
            $this->build_regions_condition( $query ),
            $this->build_status_condition( $query )
        );

        $conditions = apply_filters( 'awpcp-find-listings-conditions', $conditions, $query );

        $flattened_conditions = $this->flatten_conditions( $conditions, 'OR' );
        $where_conditions = $this->group_conditions( $flattened_conditions, 'AND' );

        return sprintf( 'WHERE %s', $where_conditions );
    }

    private function build_keyword_condition( $query ) {
        $conditions = array();

        if ( ! empty( $query['keyword'] ) ) {
            $sql = '( ad_title LIKE \'%%%1$s%%\' OR ad_details LIKE \'%%%1$s%%\' )';
            $conditions[] = $this->db->prepare( $sql, $query['keyword'] );
        }

        return apply_filters( 'awpcp-find-listings-keyword-conditions', $conditions, $query );
    }

    private function build_category_condition( $query ) {
        $conditions = array();

        if ( $query['category_id'] ) {
            $sql = '( listings.`ad_category_id` = %1$d OR listings.`ad_category_parent_id` = %1$d )';
            $conditions[] = $this->db->prepare( $sql, $query['category_id'] );
        }

        return $conditions;
    }

    private function build_contact_condition( $query ) {
        $conditions = array();

        if ( ! empty( $query['contact_name'] ) ) {
            $conditions[] = $this->db->prepare( 'listings.`ad_contact_name` = %s', $query['contact_name'] );
        }

        return $conditions;
    }

    private function build_price_condition( $query ) {
        $conditions = array();

        if ( strlen( $query['price'] ) ) {
            $conditions[] = $this->db->prepare( 'listings.`ad_item_price` = %d', $query['price'] * 100 );
        }

        if ( strlen( $query['min_price'] ) ) {
            $conditions[] = $this->db->prepare( 'listings.`ad_item_price` >= %d', $query['min_price'] * 100 );
        }

        if ( strlen( $query['max_price'] ) ) {
            $conditions[] = $this->db->prepare( 'listings.`ad_item_price` <= %d', $query['max_price'] * 100 );
        }

        return $this->group_conditions( $conditions, 'AND' );
    }

    private function build_regions_condition( $query ) {
        $conditions = array();

        foreach ( $query['regions'] as $region ) {
            $region_conditions = array();

            foreach ( $region as $field => $search ) {
                // add support for exact search, passing a search values defined as array( '=', <region-name> ).
                if ( is_array( $search ) && count( $search ) == 2 && $search[0] == '=' ) {
                    $region_conditions[] = $this->db->prepare( "listing_regions.`$field` = %s", trim( $search[1] ) );
                } else if ( ! is_array( $search ) ) {
                    $region_conditions[] = $this->db->prepare( "listing_regions.`$field` LIKE '%%%s%%'", trim( $search ) );
                }
            }

            $conditions[] = $region_conditions;
        }

        return $this->flatten_conditions( $conditions, 'AND' );
    }

    private function build_status_condition( $query ) {
        $conditions = array();

        if ( $query['disabled'] ) {
            $conditions[] = 'disabled = 1';
        } else if ( ! is_null( $query['disabled'] ) ) {
            $conditions[] = 'disabled = 0';
        }

        if ( $query['verified'] ) {
            $conditions[] = 'verified = 1';
        } else if ( ! is_null( $query['verified'] ) ) {
            $conditions[] = 'verified = 0';
        }

        return $this->group_conditions( $conditions, 'AND' );
    }

    private function flatten_conditions( $conditions, $connector = 'OR' ) {
        $flattened_conditions = array();

        foreach ( $conditions as $index => $condition ) {
            if ( ! empty( $condition ) ) {
                $flattened_conditions[] = $this->group_conditions( $condition, $connector );
            }
        }

        return $flattened_conditions;
    }

    private function group_conditions( $conditions, $connector = 'OR' ) {
        $conditions_count = count( $conditions );

        if ( is_array( $conditions ) && $conditions_count >= 1 ) {
            if ( $conditions_count > 1 ) {
                return '( ' . implode( " $connector ", $conditions ) . ' )';
            } else if ( $conditions_count == 1 ) {
                return array_pop( $conditions );
            }
        } else if ( ! is_array( $conditions ) ) {
            return $conditions;
        } else {
            return '';
        }
    }

    private function build_limit_clause( $query ) {
        if ( $query['limit'] > 0 ) {
            return sprintf( 'LIMIT %d, %d', $query['offset'], $query['limit'] );
        } else {
            return '';
        }
    }

    private function build_order_clause( $query ) {
        $basedate = 'CASE WHEN renewed_date IS NULL THEN ad_startdate ELSE GREATEST(ad_startdate, renewed_date) END';
        $is_paid = 'CASE WHEN ad_fee_paid > 0 THEN 1 ELSE 0 END';

        switch ( $query['order'] ) {
            case 1:
                $parts = array( "$basedate DESC" );
                break;
            case 2:
                $parts = array( 'ad_title ASC' );
                break;
            case 3:
                $parts = array( "$is_paid DESC", "$basedate DESC" );
                break;
            case 4:
                $parts = array( "$is_paid DESC", 'ad_title ASC' );
                break;
            case 5:
                $parts = array( 'ad_views DESC', 'ad_title ASC' );
                break;
            case 6:
                $parts = array( 'ad_views DESC', "$basedate DESC" );
                break;
            case 7:
                $parts = array( 'ad_item_price DESC', "$basedate DESC" );
                break;
            case 8:
                $parts = array( 'ad_item_price ASC', "$basedate DESC" );
                break;
            case 9:
                $parts = array( "$basedate ASC" );
                break;
            case 10:
                $parts = array( 'ad_title DESC' );
                break;
            case 11:
                $parts = array( 'ad_views ASC', "ad_title ASC" );
                break;
            case 12:
                $parts = array( 'ad_views ASC', "$basedate ASC" );
                break;
            default:
                $parts = array( 'ad_postdate DESC', 'ad_title ASC' );
                break;
        }

        $parts = array_filter( apply_filters( 'awpcp-ad-order-conditions', $parts, $query['order'] ) );

        return sprintf( 'ORDER BY %s', implode( ', ', $parts ) );
    }

    public function count( $query ) {
        return $this->find( array_merge( $query, array( 'fields' => 'count' ) ) );
    }
}
