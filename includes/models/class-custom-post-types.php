<?php

function awpcp_custom_post_types() {
    return new AWPCP_Custom_Post_Types(
        'awpcp_listing',
        'awpcp_listing_category',
        awpcp_settings_api()
    );
}

class AWPCP_Custom_Post_Types {

    private $settings;

    public function __construct( $listings_post_type, $listings_category_taxonomy, $settings ) {
        $this->listings_post_type = $listings_post_type;
        $this->listings_category_taxonomy = $listings_category_taxonomy;

        $this->settings = $settings;
    }

    /**
     * TODO: Do we really want to do this?
     */
    public function register_custom_post_status() {
        // Draft, Payment, Verification, Published, Disabled, Review
        register_post_status(
            'disabled',
            array(
                'label' => __( 'Disabled', 'another-wordpress-classifieds-plugin' ),
                'public' => false,
                'exclude_from_search' => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop( 'Disabled <span class="count">(%d)</span>', 'Disabled <span class="count">(%d)</span>', 'another-wordpress-classifieds-plugin' ),
            )
        );
    }

    public function register_custom_post_types() {
        $post_type_slug = $this->get_post_type_slug();

        $registered_post_type = register_post_type(
            $this->listings_post_type,
            array(
                'labels' => array(
                    'name' => __( 'Listings', 'another-wordpress-classifieds-plugin' ),
                    'singular_name' => __( 'Listing', 'another-wordpress-classifieds-plugin' ),
                    'all_items' => __( 'All Listings', 'another-wordpress-classifieds-plugin' ),
                    'add_new' => _x( 'Add New', 'awpcp_listing', 'another-wordpress-classifieds-plugin' ),
                    'add_new_item' => __( 'Add New Listing', 'another-wordpress-classifieds-plugin' ),
                    'edit_item' => __( 'Edit Listing', 'another-wordpress-classifieds-plugin' ),
                    'new_item' => __( 'New Listing', 'another-wordpress-classifieds-plugin' ),
                    'view_item' => __( 'View Listing', 'another-wordpress-classifieds-plugin' ),
                    'search_items' => __( 'Search Listings', 'another-wordpress-classifieds-plugin' ),
                    'not_found' => __( 'No listings found', 'another-wordpress-classifieds-plugin' ),
                    'not_found_in_trash' => __( 'No listings found in Trash', 'another-wordpress-classifieds-plugin' ),
                ),
                'description' => __( 'A classifieds listing.', 'another-wordpress-classifieds-plugin' ),
                'public' => true,
                'exclude_from_search' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_nav_menus' => true,
                'show_in_menu' => true,
                'show_in_admin_bar' => true,
                'menu_icon' => MENUICO,
                'supports' => array(
                    'title',
                    'editor',
                    'author',
                    'thumbnail',
                    'excerpt',
                    'custom-fields',
                ),
                'register_meta_box_cb' => null,
                'taxonomies' => array(
                    $this->listings_category_taxonomy,
                ),
                'has_archive' => false,
                'rewrite' => array(
                    'slug' => $post_type_slug,
                    'with_front' => false,
                ),
                'query_var' => 'listing',
            )
        );
    }

    private function get_post_type_slug() {
        $default_slug = _x( 'listings', 'listing post type slug', 'another-wordpress-classifieds-plugin' );

        if ( ! $this->settings->get_option( 'display-listings-as-single-posts' ) ) {
            $show_listings_page = awpcp_get_page_by_ref( 'show-ads-page-name' );

            return $show_listings_page ? get_page_uri( $show_listings_page ) : $default_slug;
        }

        $post_type_slug = $this->settings->get_option( 'listings-slug', $default_slug );

        if ( ! $this->settings->get_option( 'include-main-page-slug-in-listing-url' ) ) {
            return $post_type_slug;
        }

        $main_listings_page = awpcp_get_page_by_ref( 'main-page-name' );

        if ( ! $main_listings_page ) {
            return $post_type_slug;
        }

        return get_page_uri( $main_listings_page ) . '/' . $post_type_slug;
    }

    public function register_custom_taxonomies() {
        register_taxonomy(
            $this->listings_category_taxonomy,
            $this->listings_post_type,
             array(
                'labels' => array(
                    'name' => _x( 'Categories', 'taxonomy general name', 'another-wordpress-classifieds-plugin' ),
                    'singular_name' => _x( 'Category', 'taxonomy general name', 'another-wordpress-classifieds-plugin' ),
                ),
                'hierarchical' => true,
                'query_var' => 'listing-category',
                'rewrite' => array(
                    'slug' => 'listing-category'
                )
            )
        );

        register_taxonomy_for_object_type( 'awpcp_listing_category', 'awpcp_listing' );

        // $terms = get_terms(
        //     'awpcp_listing_category',
        //     array(
        //         'hide_empty' => false,
        //     )
        // );

        // foreach ( $terms as $term ) {
        //     wp_delete_term( $term->term_id, 'awpcp_listing_category' );
        // }
    }

    public function register_custom_image_sizes() {
        add_image_size(
            'awpcp-thumbnail',
            $this->settings->get_option( 'imgthumbwidth' ),
            $this->settings->get_option( 'imgthumbheight' ),
            $this->settings->get_option( 'crop-thumbnails' )
        );

        add_image_size(
            'awpcp-featured',
            $this->settings->get_option( 'primary-image-thumbnail-width' ),
            $this->settings->get_option( 'primary-image-thumbnail-height' ),
            $this->settings->get_option( 'crop-primary-image-thumbnails' )
        );

        add_image_size(
            'awpcp-large',
            $this->settings->get_option( 'imgmaxwidth' ),
            $this->settings->get_option( 'imgmaxheight' ),
            false
        );
    }

    /**
     * TODO: This method probably belongs somewhere else. A class where we create
     * default categories and fee plans, maybe.
     */
    public function create_default_category() {
        try {
            $category_id = awpcp_categories_logic()->create_category( array(
                'name' => __( 'General', 'another-wordpress-classifieds-plugin' ),
            ) );
        } catch ( AWPCP_Exception $e ) {
            return;
        }

        update_option( 'awpcp-main-category-id', $category_id );
    }
}
