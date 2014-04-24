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
}
