<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * @since 3.0
 */
function awpcp_content_placeholders() {
    static $placeholders = null;

    if ( is_array( $placeholders ) ) {
        return $placeholders;
    }

    /* Placeholders available prior to AWPCP 3.0 */

    $legacy_placeholders = array(
        // Single ad placeholders.
        'ad_title'                 => array(
            'callback' => 'awpcp_do_placeholder_title',
        ),
        'ad_categoryurl'           => array(
            'callback' => 'awpcp_do_placeholder_category_url',
        ),
        'ad_categoryname'          => array(
            'callback' => 'awpcp_do_placeholder_category_name',
        ),
        'adcontact_name'           => array(
            'callback' => 'awpcp_do_placeholder_contact_name',
        ),
        'ad_contact_name'          => array(
            'callback' => 'awpcp_do_placeholder_contact_name',
        ),
        'adcontactphone'           => array(
            'callback' => 'awpcp_do_placeholder_contact_phone',
        ),
        'ad_contact_phone'         => array(
            'callback' => 'awpcp_do_placeholder_contact_phone',
        ),
        'adcontactemail'           => array(
            'callback' => 'awpcp_do_placeholder_contact_email',
        ),
        'ad_contact_email'         => array(
            'callback' => 'awpcp_do_placeholder_contact_email',
        ),
        'codecontact'              => array(
            'callback' => 'awpcp_do_placeholder_contact_url',
        ),
        'awpcpvisitwebsite'        => array(
            'callback' => 'awpcp_do_placeholder_website_link',
        ),
        'addetails'                => array(
            'callback' => 'awpcp_do_placeholder_details',
        ),
        'ad_details'               => array(
            'callback' => 'awpcp_do_placeholder_details',
        ),
        'location'                 => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'city'                     => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'state'                    => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'village'                  => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'country'                  => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'aditemprice'              => array(
            'callback' => 'awpcp_do_placeholder_price',
        ),
        'ad_item-price'            => array(
            'callback' => 'awpcp_do_placeholder_price',
        ),
        'ad_startdate'             => array(
            'callback' => 'awpcp_do_placeholder_legacy_dates',
        ),
        'ad_postdate'              => array(
            'callback' => 'awpcp_do_placeholder_legacy_dates',
        ),

        'featureimg'               => array(
            'callback' => 'awpcp_do_placeholder_images',
        ),
        'awpcpshowadotherimages'   => array(
            'callback' => 'awpcp_do_placeholder_images',
        ),

        'awpcpextrafields'         => array(
            'callback' => 'awpcp_do_placeholder_extra_fields',
        ),
        'showadsense1'             => array(
            'callback' => 'awpcp_do_placeholder_adsense',
        ),
        'showadsense2'             => array(
            'callback' => 'awpcp_do_placeholder_adsense',
        ),
        'showadsense3'             => array(
            'callback' => 'awpcp_do_placeholder_adsense',
        ),
        'awpcpadviews'             => array(
            'callback' => 'awpcp_do_placeholder_legacy_views',
        ),

        'flagad'                   => array(
            'callback' => 'awpcp_do_placeholder_flag_link',
        ),

        'tweetbtn'                 => array(
            'callback' => 'awpcp_do_placeholder_twitter_button',
        ),
        'sharebtn'                 => array(
            'callback' => 'awpcp_do_placeholder_facebook_button',
        ),

        // Listings [only] placeholders.
        'url_showad'               => array(
            'callback' => 'awpcp_do_placeholder_url',
        ),
        'addetailssummary'         => array(
            'callback' => 'awpcp_do_placeholder_excerpt',
        ),
        'awpcp_city_display'       => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'awpcp_state_display'      => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'awpcp_country_display'    => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'awpcp_display_price'      => array(
            'callback' => 'awpcp_do_placeholder_price',
        ),
        'awpcpadpostdate'          => array(
            'callback' => 'awpcp_do_placeholder_legacy_dates',
        ),
        'imgblockwidth'            => array(
            'callback' => 'awpcp_do_placeholder_images',
        ),
        'awpcp_image_name_srccode' => array(
            'callback' => 'awpcp_do_placeholder_images',
        ),

        'awpcp_display_adviews'    => array(
            'callback' => 'awpcp_do_placeholder_legacy_views',
        ),
    );

    /* New placeholders added in AWPCP 3.0. */

    $placeholders = array(
        // Common placeholders.
        'url'                  => array(),
        'title'                => array(),
        'title_link'           => array(
            'callback' => 'awpcp_do_placeholder_title',
        ),
        'category_url'         => array(),
        'category_name'        => array(),
        'category_description' => array(),
        'parent_category_url'  => array(),
        'parent_category_name' => array(),
        'categories'           => array(),
        'details'              => array(),
        'excerpt'              => array(),
        'contact_name'         => array(),
        'contact_phone'        => array(
            'callback' => 'awpcp_do_placeholder_contact_phone',
        ),
        'contact_url'          => array(),
        'website_link'         => array(),
        'website_url'          => array(),
        'websiteurl'           => array(),
        'county'               => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'region'               => array(
            'callback' => 'awpcp_do_placeholder_location',
        ),
        'price'                => array(),
        'start_date'           => array(
            'callback' => 'awpcp_do_placeholder_dates',
        ),
        'end_date'             => array(
            'callback' => 'awpcp_do_placeholder_dates',
        ),
        'posted_date'          => array(
            'callback' => 'awpcp_do_placeholder_dates',
        ),
        'posted_date_time'     => array(
            'callback' => 'awpcp_do_placeholder_dates',
        ),
        'posted_time_elapsed'  => array(
            'callback' => 'awpcp_do_placeholder_dates',
        ),
        'last_updated_date'    => array(
            'callback' => 'awpcp_do_placeholder_dates',
        ),
        'renewed_date'         => array(
            'callback' => 'awpcp_do_placeholder_dates',
        ),

        'featured_image'       => array(
            'callback' => 'awpcp_do_placeholder_images',
        ),

        'views'                => array(),

        'extra_fields'         => array(
            'callback' => 'awpcp_do_placeholder_extra_fields',
        ),

        'twitter_button'       => array(),
        'twitter_button_url'   => array(),
        'facebook_button'      => array(),
        'facebook_button_url'  => array(),

        // Single ad [only] placeholders.
        'images'               => array(),
        'adsense'              => array(),
        'flag_link'            => array(),

        // Listings [only] placeholders.
        'thumbnail_width'      => array(
            'callback' => 'awpcp_do_placeholder_images',
        ),
        'ad_actions'           => array(),
    );

    $placeholders = array_merge( $legacy_placeholders, $placeholders );
    $placeholders = apply_filters( 'awpcp-content-placeholders', $placeholders );

    foreach ( $placeholders as $placeholder => $params ) {
        if ( ! isset( $placeholders[ $placeholder ]['callback'] ) ) {
            $placeholders[ $placeholder ]['callback'] = "awpcp_do_placeholder_{$placeholder}";
        }
    }
    krsort( $placeholders );

    return $placeholders;
}

/**
 * @since 3.0
 */
function awpcp_do_placeholders( $ad, $content, $context ) {
    $placeholders       = awpcp_content_placeholders();
    $placeholders_names = array_keys( $placeholders );

    return awpcp_replace_placeholders( $placeholders_names, $ad, $content, $context );
}

/**
 * @since 3.6.4
 */
function awpcp_replace_placeholders( $placeholders, $listing, $content, $context ) {
    $original_content = $content;

    // Remove old $quers/ placeholders.
    $content = str_replace( '$quers/', '', $content );

    $pattern = sprintf( '\$%s', join( '|\$', array_map( 'preg_quote', $placeholders ) ) );

    preg_match_all( "/$pattern/s", $content, $matches );

    $available_placeholders = awpcp_content_placeholders();
    $processed_placeholders = array();
    rsort( $matches[0] );

    foreach ( $matches[0] as $match ) {
        if ( isset( $processed_placeholders[ $match ] ) ) {
            continue;
        }

        $placeholder = trim( $match, '$' );
        $callback    = $available_placeholders[ $placeholder ]['callback'];

        if ( is_callable( $callback ) ) {
            $replacement = call_user_func( $callback, $listing, $placeholder, $context );
            if ( is_null( $replacement ) ) {
                $replacement = '';
            }

            $content                          = str_replace( $match, $replacement, $content );
            $processed_placeholders[ $match ] = true;
        }
    }

    return $content;
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_url( $ad, $placeholder ) {
    return esc_url( awpcp_listing_renderer()->get_view_listing_url( $ad ) );
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_title( $ad, $placeholder ) {
    $listing_renderer = awpcp_listing_renderer();

    $title = $listing_renderer->get_listing_title( $ad );
    $url   = $listing_renderer->get_view_listing_url( $ad );

    $title_link = sprintf( '<a href="%s">%s</a>', esc_attr( $url ), esc_html( $title ) );
    $title_link = apply_filters( 'awpcp_title_link_placeholder', $title_link, $ad, $title, $url );

    $replacements['title']      = esc_html( $title );
    $replacements['ad_title']   = $title_link;
    $replacements['title_link'] = $title_link;

    return $replacements[ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_category_name( $ad, $placeholder ) {
    return esc_html( stripslashes( awpcp_listing_renderer()->get_category_name( $ad ) ) );
}

/**
 * @since 4.1
 */
function awpcp_do_placeholder_category_description( $ad, $placeholder ) {
    return esc_html( stripslashes( awpcp_listing_renderer()->get_category_description( $ad ) ) );
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_category_url( $ad, $placeholder ) {
    return awpcp_get_browse_category_url_from_id( awpcp_listing_renderer()->get_category_id( $ad ) );
}

/**
 * @since 3.2
 */
function awpcp_do_placeholder_parent_category_name( $ad, $placeholder ) {
    $category        = awpcp_listing_renderer()->get_category( $ad );
    $parent_category = null;
    if ( $category && $category->parent > 0 ) {
        $parent_category = get_term( $category->parent );
        $parent_category = $parent_category->name;
    }

    return esc_html( stripslashes( $parent_category ) );
}

/**
 * @since 3.2
 */
function awpcp_do_placeholder_parent_category_url( $ad, $placeholder ) {
    return awpcp_get_browse_category_url_from_id( $ad->ad_category_parent_id );
}

/**
 * @since 3.3
 */
function awpcp_do_placeholder_categories( $listing, $placeholder ) {
    $categories = awpcp_categories_collection()->find_by_listing_id( $listing->ID );
    $links      = array(
        'parent-category' => '',
        'category'        => '',
    );

    foreach ( $categories as $category ) {
        if ( $category->parent ) {
            $category_type = 'parent-category';
        } else {
            $category_type = 'category';
        }

        $link = '<a href="<category-url>"><category-name></a>';
        $link = str_replace( '<category-url>', esc_attr( url_browsecategory( $category ) ), $link );
        $link = str_replace( '<category-name>', esc_html( $category->name ), $link );

        $links[ $category_type ] = $link;
    }

    $output = '<span class="awpcp-listing-categories"><categories></span>';
    $output = str_replace( '<categories>', implode( ' / ', array_filter( $links ) ), $output );

    return $output;
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_details( $ad, $placeholder ) {
    static $replacements = array();

    if ( isset( $replacements[ $ad->ID ] ) ) {
        return $replacements[ $ad->ID ][ $placeholder ];
    }

    $placeholders['addetails'] = apply_filters( 'awpcp-ad-details', stripslashes_deep( $ad->post_content ) );

    if ( get_awpcp_option( 'hyperlinkurlsinadtext' ) ) {
        $placeholders['addetails'] = make_clickable( $placeholders['addetails'] );
        if ( ! get_awpcp_option( 'visitwebsitelinknofollow' ) ) {
            // Remove the nofollow attr.
            $placeholders['addetails'] = str_replace( ' rel="nofollow"', '', $placeholders['addetails'] );
        }
    }

    $placeholders['addetails'] = wpautop( $placeholders['addetails'] );
    $placeholders['details']   = $placeholders['addetails'];

    $replacements[ $ad->ID ] = $placeholders;

    return $replacements[ $ad->ID ][ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_excerpt( $ad, $placeholder ) {
    $word_count = get_awpcp_option( 'words-in-listing-excerpt' );
    $details    = stripslashes( $ad->post_content );

    if ( get_awpcp_option( 'allowhtmlinadtext' ) ) {
        $excerpt = awpcp_trim_html_content( $details, $word_count );

        $replacements['addetailssummary'] = $excerpt;
        $replacements['excerpt']          = $excerpt;
    } else {
        $replacements['addetailssummary'] = wp_trim_words( $details, $word_count, '' );
        $replacements['excerpt']          = wp_trim_words( $details, $word_count );
    }

    return $replacements[ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_contact_name( $ad, $placeholder ) {
    // Should we hide the contact name from anonyumous users?
    if ( intval( get_awpcp_option( 'hidelistingcontactname' ) ) === 1 && ! is_user_logged_in() ) {
        $contact_name = __( 'Seller', 'another-wordpress-classifieds-plugin' );
    } else {
        $contact_name = awpcp_listing_renderer()->get_contact_name( $ad );
    }

    return esc_html( stripslashes( $contact_name ) );
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_website_url( $ad, $placeholder ) {
    return awpcp_listing_renderer()->get_website_url( $ad );
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_website_link( $ad, $placeholder ) {
    $website_url = awpcp_listing_renderer()->get_website_url( $ad );

    // Whether the website field should be shown to registered users only.
    $login_required = intval( get_awpcp_option( 'displaywebsitefieldreqpriv' ) );

    if ( ( ! $login_required || is_user_logged_in() ) && ! empty( $website_url ) ) {
        $nofollow      = get_awpcp_option( 'visitwebsitelinknofollow' ) ? 'rel="nofollow"' : '';
        $escaped_label = esc_html( __( 'Visit Website', 'another-wordpress-classifieds-plugin' ) );
        $escaped_url   = awpcp_esc_attr( $website_url );

        $content                           = '<br/><a %s href="%s" target="_blank">%s</a>';
        $content                           = sprintf( $content, $nofollow, $escaped_url, $escaped_label );
        $replacements['awpcpvisitwebsite'] = $content;

        $content                      = '<a %s href="%s" target="_blank">%s</a>';
        $content                      = sprintf( $content, $nofollow, $escaped_url, $escaped_label );
        $replacements['website_link'] = $content;
    } else {
        $replacements['awpcpvisitwebsite'] = '';
        $replacements['website_link']      = '';
    }

    return $replacements[ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_price( $ad, $placeholder ) {
    $listing_renderer = awpcp_listing_renderer();

    $price = intval( $listing_renderer->get_price( $ad ) ) / 100;

    $show_price_field = intval( get_awpcp_option( 'displaypricefield' ) ) === 1;

    // The price field can be restricted to registered users only.
    $user_can_see_price_field = is_user_logged_in() || intval( get_awpcp_option( 'price-field-is-restricted' ) ) === 0;

    if ( get_awpcp_option( 'hide-price-field-if-empty' ) && $price <= 0 ) {
        $show_price_field = false;
    }

    $replacements = array();

    if ( $show_price_field && $user_can_see_price_field && $price >= 0 ) {
        $escaped_label    = esc_html( __( 'Price', 'another-wordpress-classifieds-plugin' ) );
        $escaped_currency = esc_html( awpcp_format_money( $price, 'free' ) );

        // Single ad.
        $content                     = '<div class="showawpcpadpage"><label>%s:</label> <strong>%s</strong></div>';
        $replacements['aditemprice'] = sprintf( $content, $escaped_label, $escaped_currency );

        // Listings.
        $replacements['awpcp_display_price'] = sprintf( '%s: %s', $escaped_label, $escaped_currency );

        $replacements['price'] = $escaped_currency;
    }

    return awpcp_array_data( $placeholder, '', $replacements );
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_dates( $ad, $placeholder ) {
    $listing_renderer = awpcp_listing_renderer();

    $replacements['start_date']        = $listing_renderer->get_start_date( $ad );
    $replacements['end_date']          = $listing_renderer->get_end_date( $ad );
    $replacements['posted_date']       = $listing_renderer->get_posted_date_formatted( $ad );
    $replacements['posted_date_time']  = $listing_renderer->get_posted_date_and_time_formatted( $ad );
    $replacements['last_updated_date'] = $listing_renderer->get_last_updated_date_formatted( $ad );

    $verification_date = $listing_renderer->get_verification_date( $ad );

    if ( ! empty( $verification_date ) ) {
        $replacements['posted_time_elapsed'] = awpcp_datetime( 'time-elapsed', $verification_date );
    } else {
        $replacements['posted_time_elapsed'] = '';
    }

    $renewed_date = $listing_renderer->get_renewed_date( $ad );

    if ( ! empty( $renewed_date ) ) {
        $replacements['renewed_date'] = $listing_renderer->get_renewed_date_formatted( $ad );
    } else {
        $replacements['renewed_date'] = $listing_renderer->get_posted_date_formatted( $ad );
    }

    return $replacements[ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_images( $ad, $placeholder ) {
    return awpcp_image_placeholders()->do_image_placeholders( $ad, $placeholder );
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_views( $ad, $placeholder ) {
    return $ad->ad_views;
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_legacy_dates( $ad, $placeholder ) {
    $listing_renderer = awpcp_listing_renderer();

    $replacements['ad_startdate']    = $listing_renderer->get_start_date( $ad );
    $replacements['ad_postdate']     = $listing_renderer->get_posted_date_formatted( $ad );
    $replacements['awpcpadpostdate'] = sprintf( '%s<br/>', $replacements['ad_postdate'] );

    return $replacements[ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_location( $ad, $placeholder ) {
    if ( ! is_plugin_active( 'awpcp-region-control/awpcp_region_control_module.php' ) ) {
        return '';
    }

    $regions = awpcp_listing_renderer()->get_regions( $ad );

    $cities    = array();
    $states    = array();
    $villages  = array();
    $countries = array();
    $places    = array();

    $replacements = array();

    if ( get_awpcp_option( 'show-city-field-before-county-field' ) ) {
        $order = array( 'county', 'city', 'state', 'country' );
    } else {
        $order = array( 'city', 'county', 'state', 'country' );
    }

    foreach ( $regions as $region ) {
        if ( ! empty( $region['city'] ) ) {
            $cities[] = stripslashes_deep( $region['city'] );
        }
        if ( ! empty( $region['county'] ) ) {
            $villages[] = stripslashes_deep( $region['county'] );
        }
        if ( ! empty( $region['state'] ) ) {
            $states[] = stripslashes_deep( $region['state'] );
        }
        if ( ! empty( $region['country'] ) ) {
            $countries[] = stripslashes_deep( $region['country'] );
        }

        $place = array();
        foreach ( $order as $field ) {
            if ( ! empty( $region[ $field ] ) ) {
                $place[] = stripslashes_deep( $region[ $field ] );
            }
        }

        $places[] = $place;
    }

    if ( ! empty( $cities ) ) {
        $replacements['city'] = join( ', ', $cities );
    }
    if ( ! empty( $states ) ) {
        $replacements['state'] = join( ', ', $states );
    }
    if ( ! empty( $villages ) ) {
        $replacements['county']  = join( ', ', $villages );
        $replacements['village'] = $replacements['county'];
    }
    if ( ! empty( $countries ) ) {
        $replacements['country'] = join( ', ', $countries );
    }

    $location = array();
    foreach ( $places as $place ) {
        $location[] = join( ', ', $place );
    }
    $location = join( '; ', $location );

    if ( ! empty( $location ) ) {
        $replacements['location'] = sprintf( '<br/><label>%s:</label> %s', __( 'Location', 'another-wordpress-classifieds-plugin' ), $location );
        $replacements['region']   = $location;
    } else {
        $replacements['location'] = '';
        $replacements['region']   = '';
    }

    if ( ! empty( $replacements['city'] ) ) {
        $replacements['awpcp_city_display'] = sprintf( '%s<br/>', $replacements['city'] );
    } else {
        $replacements['awpcp_city_display'] = '';
    }

    if ( ! empty( $replacements['state'] ) ) {
        $replacements['awpcp_state_display'] = sprintf( '%s<br/>', $replacements['state'] );
    } else {
        $replacements['awpcp_state_display'] = '';
    }

    if ( ! empty( $replacements['country'] ) ) {
        $replacements['awpcp_country_display'] = sprintf( '%s<br/>', $replacements['country'] );
    } else {
        $replacements['awpcp_country_display'] = '';
    }

    return $replacements[ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_legacy_views( $ad, $placeholder ) {
    if ( get_awpcp_option( 'displayadviews' ) ) {
        $views = get_numtimesadviewd( $ad->ID );

        // Single ad.
        /* translators: %d is the number of views for this ad. */
        $text                         = _n( 'This Ad has been viewed %d time.', 'This Ad has been viewed %d times.', $views, 'another-wordpress-classifieds-plugin' );
        $replacements['awpcpadviews'] = sprintf( '<div class="adviewed">%s</div>', sprintf( $text, $views ) );

        // Listings.
        /* translators: %d is the number of views for this ad. */
        $content                               = sprintf( __( 'Total views: %d', 'another-wordpress-classifieds-plugin' ), $views );
        $replacements['awpcp_display_adviews'] = sprintf( '%s<br/>', $content );
    } else {
        $replacements['awpcpadviews']          = '';
        $replacements['awpcp_display_adviews'] = '';
    }

    return $replacements[ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_extra_fields( $ad, $placeholder, $context ) {
    global $hasextrafieldsmodule;

    if ( intval( $hasextrafieldsmodule ) === 1 ) {
        $single                           = $context === 'single' ? true : false;
        $replacements['awpcpextrafields'] = display_x_fields_data( $ad->ID, $single );
    } else {
        $replacements['awpcpextrafields'] = '';
    }

    $replacements['extra_fields'] = $replacements['awpcpextrafields'];

    return $replacements[ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_contact_url( $ad, $placeholder ) {
    return awpcp_get_reply_to_ad_url( $ad->ID, awpcp_listing_renderer()->get_listing_title( $ad ) );
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_contact_phone( $ad, $placeholder ) {
    $phone = awpcp_listing_renderer()->get_contact_phone( $ad );

    // Whether the phone number field should be shown to registered users only.
    $login_required = intval( get_awpcp_option( 'displayphonefieldpriv' ) );

    if ( ! empty( $phone ) ) {
        if ( $login_required && ! is_user_logged_in() ) {
            $allowed = intval( strlen( $phone ) * 0.4 );
            $phone   = substr( $phone, 0, $allowed ) . str_repeat( 'X', strlen( $phone ) - $allowed );
        }

        $content = sprintf(
            '<br/><label>%s:</label> %s',
            __( 'Phone', 'another-wordpress-classifieds-plugin' ),
            $phone
        );

        $replacements['adcontactphone'] = $content;
        $replacements['contact_phone']  = $phone;
    } else {
        $replacements['adcontactphone'] = '';
        $replacements['contact_phone']  = '';
    }

    return $replacements[ $placeholder ];
}

/**
 * @since 4.0.11
 */
function awpcp_do_placeholder_contact_email( $ad, $placeholder ) {
    return esc_html( stripslashes( awpcp_listing_renderer()->get_contact_email( $ad ) ) );
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_adsense( $ad, $placeholder ) {
    static $replacements = array();

    if ( isset( $replacements[ $ad->ID ] ) ) {
        return $replacements[ $ad->ID ][ $placeholder ];
    }

    if ( get_awpcp_option( 'useadsense' ) ) {
        $content                 = '<div class="cl-adsense">%s</div>';
        $placeholders['adsense'] = sprintf( $content, get_awpcp_option( 'adsense' ) );
    } else {
        $placeholders['adsense'] = '';
    }

    $placeholders['showadsense1'] = '';
    $placeholders['showadsense2'] = '';
    $placeholders['showadsense3'] = '';

    switch ( get_awpcp_option( 'adsenseposition' ) ) {
        case 1:
            $placeholders['showadsense1'] = $placeholders['adsense'];
            break;
        case 2:
            $placeholders['showadsense2'] = $placeholders['adsense'];
            break;
        case 3:
            $placeholders['showadsense3'] = $placeholders['adsense'];
            break;
    }

    $replacements[ $ad->ID ] = $placeholders;

    return $replacements[ $ad->ID ][ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_flag_link( $ad, $placeholder ) {
    $title = __( 'Flag Ad', 'another-wordpress-classifieds-plugin' );

    $content = '<a class="awpcp-flag-listing-link" href="#" data-ad="%d" title="%s"><i class="fa fa-flag"></i></a>';

    $replacements['flagad']    = sprintf( $content, esc_attr( $ad->ID ), esc_attr( $title ) );
    $replacements['flag_link'] = $replacements['flagad'];

    return $replacements[ $placeholder ];
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_twitter_button( $ad, $placeholder ) {
    $title = __( 'Tweet This', 'another-wordpress-classifieds-plugin' );
    $url   = awpcp_do_placeholder_twitter_button_url( $ad, 'twitter_button_url' );

    $button  = '<a class="awpcp-social-button tw_button awpcp_tweet_button_div" href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '" target="_blank" rel="nofollow noopener">';
    $button .= '<span class="twitter-share-button">';
    $button .= '<i class="' . awpcp_add_font_awesome_style_class_for_brands( 'fa-twitter-square' ) . '"></i>';
    $button .= '</span>';
    $button .= '</a>';

    return $button;
}

/**
 * @since 3.2.2
 */
function awpcp_do_placeholder_twitter_button_url( $ad, $placeholder ) {
    $listing_renderer = awpcp_listing_renderer();

    $title = $listing_renderer->get_listing_title( $ad );
    $url   = $listing_renderer->get_view_listing_url( $ad );

    return add_query_arg(
        array(
            'url'  => rawurlencode( $url ),
            'text' => rawurlencode( $title ),
        ),
        'https://twitter.com/share'
    );
}

/**
 * @since 3.0
 */
function awpcp_do_placeholder_facebook_button( $ad, $placeholder ) {
    $title = __( 'Share on Facebook', 'another-wordpress-classifieds-plugin' );
    $href  = awpcp_do_placeholder_facebook_button_url( $ad, 'facebook_button_url' );

    $button  = '<a class="awpcp-social-button tw_button awpcp_tweet_button_div" href="%s" class="facebook-share-button" title="%s" target="_blank" rel="nofollow noopener">';
    $button .= '<span class="facebook-share-button">';
    $button .= '<i class="' . awpcp_add_font_awesome_style_class_for_brands( 'fa-facebook-square' ) . '"></i>';
    $button .= '</span>';
    $button .= '</a>';

    return sprintf( $button, esc_url( $href ), esc_attr( $title ) );
}

/**
 * @since 3.2.2
 */
function awpcp_do_placeholder_facebook_button_url( $ad, $placeholder ) {
    $info = awpcp_get_ad_share_info( $ad->ID );
    return sprintf( 'https://www.facebook.com/sharer/sharer.php?u=%s', rawurlencode( $info['url'] ) );
}

/**
 * TODO: Remove this placeholder, already added to ad actions in edit mode.
 *
 * @since 4.0
 */
function awpcp_do_placeholder_ad_actions( $ad, $placeholder ) {
    $is_owner = (int) $ad->post_author && get_current_user_id() === (int) $ad->post_author;

    if ( $is_owner && awpcp_listing_renderer()->has_expired_or_is_about_to_expire( $ad ) ) {
        $renew_url = awpcp_get_renew_ad_url( $ad->ID );
        $label     = _x( 'Renew', 'listing row action', 'another-wordpress-classifieds-plugin' );
        return '<div class="awpcp-user-renew"><a class="button" href="' . esc_url( $renew_url ) . '">' . esc_html( $label ) . '</a></div>';
    }
}

/**
 * @since 3.0
 */
function awpcp_replace_content_placeholders( $content, $replacements ) {
    $placeholders = awpcp_content_placeholders();

    // make sure placeholders with longer names appear first.
    krsort( $replacements );

    foreach ( $replacements as $placeholder => $value ) {
        if ( ! isset( $placeholders[ $placeholder ] ) ) {
            continue;
        }

        foreach ( $placeholders[ $placeholder ]['aliases'] as $alias ) {
            $content = str_replace( "\${$alias}", "$value", $content );
        }
    }

    return $content;
}
