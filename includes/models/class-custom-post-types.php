<?php

function awpcp_custom_post_types() {
    return new AWPCP_Custom_Post_Types( awpcp_settings_api() );
}

class AWPCP_Custom_Post_Types {

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

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
        $registered_post_type = register_post_type(
            'awpcp_listing',
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
                    'search_items' => __( 'Search Listing', 'another-wordpress-classifieds-plugin' ),
                    'not_found' => __( 'No listings found', 'another-wordpress-classifieds-plugin' ),
                    'not_found_in_trash' => __( 'No listings found in Trash', 'another-wordpress-classifieds-plugin' ),
                ),
                'description' => 'A classified entry.',
                'public' => true,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_ui' => false,
                'show_in_nav_menus' => false,
                // 'show_in_menu' => 'admin.php?page=awpcp.php',
                'show_in_admin_bar' => true,
                // 'menu_position' => null,
                'menu_icon' => MENUICO,
                // 'capability_type' => array( 'awpcp_listing', 'awpcp_listings' ),
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
                    'awpcp_listing_category',
                ),
                'has_archive' => false,
                'rewrite' => array(
                    'slug' => 'listings',
                ),
                'query_var' => 'listing',
            )
        );
    }

    public function register_custom_taxonomies() {
        register_taxonomy(
            'awpcp_listing_category',
             'awpcp_listing',
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
}
