<?php

function awpcp_url_backwards_compatibility_redirection_helper() {
    return new AWPCP_URL_Backwards_Compatibility_Redirection_Helper(
        awpcp_categories_registry(),
        awpcp_categories_collection(),
        awpcp_listings_collection(),
        awpcp_query(),
        awpcp_request()
    );
}

class AWPCP_URL_Backwards_Compatibility_Redirection_Helper {

    private $categories_registry;
    private $categories;
    private $listings;
    private $query;
    private $request;

    public function __construct( $categories_registry, $categories, $listings, $query, $request ) {
        $this->categories_registry = $categories_registry;
        $this->categories = $categories;
        $this->listings = $listings;
        $this->query = $query;
        $this->request = $request;
    }

    public function maybe_redirect() {
        if ( $this->query->is_browse_listings_page() || $this->query->is_browse_categories_page() ) {
            $requested_category_id = $this->request->get_category_id();
            $equivalent_category_id = $this->get_equivalent_category_id( $requested_category_id );

            if ( $requested_category_id != $equivalent_category_id ) {
                $category = $this->categories->get( $equivalent_category_id );
                return $this->redirect( url_browsecategory( $category ) );
            }
        } else if ( $this->query->is_single_listing_page() ) {
            $requested_listing_id = $this->request->get_current_listing_id();

            try {
                $listing = $this->listings->get_listing_with_old_id( $requested_listing_id );
            } catch ( AWPCP_Exception $e ) {
                return;
            }

            return $this->redirect( url_showad( $listing->ID ) );
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
}
