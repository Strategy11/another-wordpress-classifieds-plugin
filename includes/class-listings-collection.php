<?php

/**
 * @since next-release
 */
function awpcp_listings_collection() {
    return new AWPCP_ListingsCollection();
}

/**
 * @since 3.2.2
 */
class AWPCP_ListingsCollection {

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
}
