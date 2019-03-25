<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Constructor for URL Backwards Compatiblity Redirection Helper.
 */
function awpcp_url_backwards_compatibility_redirection_helper() {
    $container = awpcp()->container;

    return new AWPCP_URL_Backwards_Compatibility_Redirection_Helper(
        $container['listing_post_type'],
        awpcp_categories_registry(),
        $container['CategoriesCollection'],
        $container['ListingsCollection'],
        awpcp_query(),
        $container['Settings'],
        $container['Request']
    );
}

/**
 * Redirect URLs that include IDs used before 4.0 to URLs that use the
 * corresponding ID from listings stored as custom post types.
 *
 * @since 4.0.0
 */
class AWPCP_URL_Backwards_Compatibility_Redirection_Helper {

    private $post_type;
    private $categories_registry;
    private $categories;
    private $listings;
    private $query;
    private $settings;
    private $request;

    public function __construct( $post_type, $categories_registry, $categories, $listings, $query, $settings, $request ) {
        $this->post_type           = $post_type;
        $this->categories_registry = $categories_registry;
        $this->categories          = $categories;
        $this->listings            = $listings;
        $this->query               = $query;
        $this->settings            = $settings;
        $this->request             = $request;
    }

    /**
     * TODO: Find out if this query will show a single listing.
     *
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function maybe_redirect_from_old_listing_url( $query ) {
        $vars                 = $query->query_vars;
        $requested_listing_id = null;

        if ( ! empty( $vars['post_type'] ) && $this->post_type === $vars['post_type'] && ! empty( $vars['p'] ) ) {
            $requested_listing_id = $vars['p'];
        } elseif ( ! empty( $vars['id'] ) && ! empty( $vars['page_id'] ) && $this->get_show_listing_page_id() === intval( $vars['page_id'] ) ) {
            $requested_listing_id = $vars['id'];
        } elseif ( ! empty( $vars['id'] ) && ! empty( $vars['pagename'] ) && $this->get_show_listing_page_uri() === $vars['pagename'] ) {
            $requested_listing_id = $vars['id'];
        }

        if ( ! $requested_listing_id ) {
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
        return intval( $this->settings->get_option( 'show-listing-page' ) );
    }

    /**
     * @since 4.0.0
     */
    private function get_show_listing_page_uri() {
        $page_id = $this->get_show_listing_page_id();

        return $page_id ? get_page_uri( $page_id ) : null;
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
            $requested_category_id  = intval( $this->request->get_category_id() );
            $equivalent_category_id = $this->get_equivalent_category_id( $requested_category_id );

            if ( $requested_category_id !== $equivalent_category_id ) {
                $category = $this->categories->get( $equivalent_category_id );
                return $this->redirect( url_browsecategory( $category ) );
            }
        } elseif ( $this->query->is_renew_listing_page() ) {
            $this->maybe_redirect_frontend_renew_listing_request();
        }
    }

    private function get_equivalent_category_id( $category_id ) {
        $categories_registry = $this->categories_registry->get_categories_registry();

        if ( isset( $categories_registry[ $category_id ] ) ) {
            return intval( $categories_registry[ $category_id ] );
        }

        return intval( $category_id );
    }

    /**
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function redirect( $redirect_url ) {
        if ( wp_safe_redirect( $redirect_url, 301 ) ) {
            exit();
        }
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
