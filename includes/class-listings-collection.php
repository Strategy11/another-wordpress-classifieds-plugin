<?php

/**
 * @since next-release
 */
function awpcp_listings_collection() {
    return new AWPCP_ListingsCollection( $GLOBALS['wpdb'] );
}

/**
 * @since 3.2.2
 */
class AWPCP_ListingsCollection {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
    }

    /**
     * @since next-release
     */
    public function get( $listing_id ) {
        $listing = AWPCP_Ad::find_by_id( $listing_id );

        if ( is_null( $listing ) ) {
            $message = __( 'No Ad was found with id: %d', 'AWPCP' );
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
     * @since next-release
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

    private function find_valid_listings( $conditions = array() ) {
        $conditions = AWPCP_Ad::get_where_conditions_for_valid_ads( $conditions );
        return AWPCP_Ad::query( array( 'where' => implode( ' AND ', $conditions ) ) );
    }

    private function count_valid_listings( $conditions = array() ) {
        $conditions = AWPCP_Ad::get_where_conditions_for_valid_ads( $conditions );
        return AWPCP_Ad::count( implode( ' AND ', $conditions ) );
    }

    /**
     * @since next-release
     */
    public function find_user_listings( $user_id ) {
        $conditions = array( $this->db->prepare( 'user_id = %d', $user_id ) );
        return $this->find_valid_listings( $conditions );
    }

    /**
     * @since next-release
     */
    public function count_user_listings( $user_id ) {
        $conditions = array( $this->db->prepare( 'user_id = %d', $user_id ) );
        return $this->count_valid_listings( $conditions );
    }

    /**
     * @since next-release
     */
    public function find_user_enabled_listings( $user_id ) {
        $conditions = array( $this->db->prepare( 'user_id = %d', $user_id ), 'disabled = 0' );
        return $this->find_valid_listings( $conditions );
    }

    /**
     * @since next-release
     */
    public function count_user_enabled_listings( $user_id ) {
        $conditions = array( $this->db->prepare( 'user_id = %d', $user_id ), 'disabled = 0' );
        return $this->count_valid_listings( $conditions );
    }

    /**
     * @since next-release
     */
    public function find_user_disabled_listings( $user_id ) {
        $conditions = array( $this->db->prepare( 'user_id = %d', $user_id ), 'disabled = 1' );
        return $this->find_valid_listings( $conditions );
    }

    /**
     * @since next-release
     */
    public function count_user_disabled_listings( $user_id ) {
        $conditions = array( $this->db->prepare( 'user_id = %d', $user_id ), 'disabled = 1' );
        return $this->count_valid_listings( $conditions );
    }
}
