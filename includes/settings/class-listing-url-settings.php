<?php

function awpcp_listing_url_settings() {
    return new AWPCP_ListingsURLSettings();
}

class AWPCP_ListingsURLSettings {

    public function register_settings( $settings ) {
        $key = $settings->add_section( 'listings-settings', 'Listing URL', 'listing-url', 50, array( $this, 'render_section_header' ) );

        $show_listings_page = awpcp_get_page_by_ref( 'show-ads-page-name' );

        if ( $show_listings_page ) {
            $show_listings_url = awpcp_get_page_link( $show_listings_page->ID );
            $show_listings_link = sprintf( '<a href="%s">%s</a>', $show_listings_url, $show_listings_page->post_title );
        } else {
            $show_listings_link = _x( 'Show Ad', 'page name', 'another-wordpress-classifieds-plugin' );
        }

        $description = __( "Enable this setting to display each listing on its own page, instead of showing the listing's content inside the <show-listing-page> page." );
        $description = str_replace( '<show-listing-page>', $show_listings_link, $description );

        $settings->add_setting(
            $key,
            'display-listings-as-single-posts',
            __( 'Display listings on their own page', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            1,
            $description
        );

        $default_slug = $this->get_default_listings_slug();

        $description = __( "Portion of the URL that appears between the website's domain and the listing's information. Example: <example-slug> in <example-url>.", 'another-wordpress-classifieds-plugin' );
        $description = str_replace( '<example-slug>', '<code>' . $default_slug . '</code>', $description );
        $description = str_replace( '<example-url>', '<code>https://example.com/' . $default_slug . '/id/listing-title/city/state/category/</code>', $description );

        $settings->add_setting(
            $key,
            'listings-slug',
            __( 'Listings slug', 'another-wordpress-classifieds-plugin' ),
            'textfield',
            $default_slug,
            $description
        );

        $main_page_slug = $this->get_main_page_slug();

        if ( $main_page_slug ) {
            $description = __( "Include the slug of the plugin's main page (<main-page-slug>) in the URL that points to the page of an individual listing.", 'another-wordpress-classifieds-plugin' );
            $description = str_replace( '<main-page-slug>', '<code>' . $main_page_slug . '</code>', $description );
        } else {
            $description = __( "Include the slug of the plugin's main page in the URL that points to the page of an individual listing.", 'another-wordpress-classifieds-plugin' );
        }

        $settings->add_setting(
            $key,
            'include-main-page-slug-in-listing-url',
            __( "Include the slug of the plugin's main page in the listing URL", 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            0,
            $description
        );

        $settings->add_setting(
            $key,
            'include-title-in-listing-url',
            __( 'Include the title in the listing URL', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            1,
            __( 'Include the title in the URL that points to the page of an individual listing.', 'another-wordpress-classifieds-plugin' )
        );

        $settings->add_setting(
            $key,
            'include-category-in-listing-url',
            __( 'Include the name of the category in the listing URL', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings->get_option( 'showcategoryinpagetitle' ),
            __( 'Include the name of the category in the URL that points to the page of an individual listing.', 'another-wordpress-classifieds-plugin' )
        );

        $settings->add_setting(
            $key,
            'include-country-in-listing-url',
            __( 'Include the name of the country in the listing URL', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings->get_option( 'showcountryinpagetitle' ),
            __( 'Include the name of the country in the URL that points to the page of an individual listing.', 'another-wordpress-classifieds-plugin' )
        );

        $settings->add_setting(
            $key,
            'include-state-in-listing-url',
            __( 'Include the name of the state in the listing URL', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings->get_option( 'showstateinpagetitle' ),
            __( 'Include the name of the state in the URL that points to the page of an individual listing.', 'another-wordpress-classifieds-plugin' )
        );

        $settings->add_setting(
            $key,
            'include-city-in-listing-url',
            __( 'Include the name of the city in the listing URL', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings->get_option( 'showcityinpagetitle' ),
            __( 'Include the name of the city in the URL that points to the page of an individual listing.', 'another-wordpress-classifieds-plugin' )
        );

        $settings->add_setting(
            $key,
            'include-county-in-listing-url',
            __( 'Include the name of the county in the listing URL', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings->get_option( 'showcountyvillageinpagetitle' ),
            __( 'Include the name of the county in the URL that points to the page of an individual listing.', 'another-wordpress-classifieds-plugin' )
        );
    }

    private function get_main_page_slug() {
        $main_plugin_page = awpcp_get_page_by_ref( 'main-page-name' );

        if ( ! $main_plugin_page ) {
            return null;
        }

        return get_page_uri( $main_plugin_page );
    }

    private function get_default_listings_slug() {
        return _x( 'listings', 'listing post type slug', 'another-wordpress-classifieds-plugin' );
    }

    public function render_section_header() {
        $introduction = _x( 'These settings affect the URL path shown for listings. You can include or remove elements for SEO purposes.', 'listing url settings section', 'another-wordpress-classifieds-plugin' );

        $main_page_slug = $this->get_main_page_slug();
        $default_slug = $this->get_default_listings_slug();

        $example_path = '<code>/' . $main_page_slug . '/' . $default_slug . '/id/listing-title/city/state/category</code>';
        $example_text = _x( 'Example path: <example-path>.', 'listing url settings section', 'another-wordpress-classifieds-plugin' );
        $example_text = str_replace( '<example-path>', $example_path, $example_text );

        echo '<p>' . $introduction . '<br/><br/>' . $example_text . '</p>';
    }

    /**
     * TODO: Refactor register_settings() to store a list of settings that need to
     *       be monitored.
     *
     *       Alternatively, we could add filters and actions for sections and
     *       invidiual settings.
     */
    public function settings_validated( $options, $group ) {
        update_option( 'awpcp-flush-rewrite-rules', true );
    }
}
