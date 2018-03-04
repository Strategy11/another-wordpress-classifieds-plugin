<?php

function awpcp_url_backwards_compatibility_redirection_helper() {
    return new AWPCP_URL_Backwards_Compatibility_Redirection_Helper(
        'awpcp_listing', // TODO: Get value from container.
        awpcp_categories_registry(),
        awpcp_categories_collection(),
        awpcp_listings_collection(),
        awpcp_query(),
        awpcp_request()
    );
}

class AWPCP_URL_Backwards_Compatibility_Redirection_Helper {

    private $post_type;
    private $categories_registry;
    private $categories;
    private $listings;
    private $query;
    private $request;

    public function __construct( $post_type, $categories_registry, $categories, $listings, $query, $request ) {
        $this->post_type = $post_type;
        $this->categories_registry = $categories_registry;
        $this->categories = $categories;
        $this->listings = $listings;
        $this->query = $query;
        $this->request = $request;
    }

    /**
     * TODO: Find out if this query will show a single listing.
     *
     * @since 4.0.0
     */
    public function maybe_redirect_from_old_listing_url( $query ) {
        $vars = $query->query_vars;

        if ( ! empty( $vars['post_type'] ) && $this->post_type == $vars['post_type'] && ! empty( $vars['p'] ) ) {
            $requested_listing_id = $vars['p'];
        } elseif ( ! empty( $vars['id'] ) && ! empty( $vars['page_id'] ) && $this->get_show_listing_page_id() == $vars['page_id'] ) {
            $requested_listing_id = $vars['id'];
        } else {
            return;
        }

        try {
            $listing = $this->listings->get_listing_with_old_id( $requested_listing_id );
        } catch ( AWPCP_Exception $e ) {
            return;
        }

        return $this->redirect( get_permalink( $listing ) );
    }

    /**
     * @since 4.0.0
     */
    private function get_show_listing_page_id() {
        return awpcp_get_page_id_by_ref( 'show-ads-page-name' );
    }

    /**
     * TODO: Remove this method when maybe_redirect_from_old_listing_url() handles
     *       all necessary cases.
     */
    public function maybe_redirect_frontend_request() {
        if ( $this->request->param( 'awpcp-no-redirect' ) ) {
            return;
        }

        if ( $this->query->is_browse_listings_page() || $this->query->is_browse_categories_page() ) {
            $requested_category_id = $this->request->get_category_id();
            $equivalent_category_id = $this->get_equivalent_category_id( $requested_category_id );

            if ( $requested_category_id != $equivalent_category_id ) {
                $category = $this->categories->get( $equivalent_category_id );
                return $this->redirect( url_browsecategory( $category ) );
            }
        // } else if ( $this->query->is_single_listing_page() ) {
        //     $requested_listing_id = $this->request->get_current_listing_id();

        //     try {
        //         $listing = $this->listings->get_listing_with_old_id( $requested_listing_id );
        //     } catch ( AWPCP_Exception $e ) {
        //         return;
        //     }

        //     return $this->redirect( url_showad( $listing->ID ) );
        } else if ( $this->query->is_renew_listing_page() ) {
            $this->maybe_redirect_frontend_renew_listing_request();
        }
    }

    private function get_equivalent_category_id( $category_id ) {
        $categories_registry = $this->categories_registry->get_categories_registry();

        if ( isset( $categories_registry[ $category_id ] ) ) {
            return $categories_registry[ $category_id ];
        } else {
            return $category_id;
        }
    }

    private function redirect( $redirect_url ) {
        wp_redirect( $redirect_url, 301 );
        exit();
    }

    private function maybe_redirect_frontend_renew_listing_request() {
         return $this->maybe_redirect_renew_listing_request( $this->request->get_current_listing_id() );
    }

    private function maybe_redirect_renew_listing_request( $old_listing_id ) {
        $renew_hash = $this->request->param( 'awpcprah' );

        if ( ! awpcp_verify_renew_ad_hash( $old_listing_id, $renew_hash ) ) {
            return;
        }

        try {
            $listing = $this->listings->get_listing_with_old_id( $old_listing_id );
        } catch ( AWPCP_Exception $e ) {
            return;
        }

        return $this->redirect( awpcp_get_renew_ad_url( $listing->ID ) );
    }

    public function maybe_redirect_admin_request() {
        if ( $this->request->param( 'awpcp-no-redirect' ) ) {
            return;
        }

        if ( strcmp( $this->request->param( 'action' ), 'renew' ) !== 0 ) {
            return;
        }

        return $this->maybe_redirect_renew_listing_request( $this->request->get_current_listing_id() );
    }
}
