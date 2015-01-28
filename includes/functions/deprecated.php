<?php

/**
 * @deprecated next-release
 */
function awpcp_display_ads($where, $byl, $hidepager, $grouporderby, $adorcat, $before_content='') {
    _deprecated_function( __FUNCTION__, 'next-release', 'awpcp_display_listings' );

    global $wpdb;
    global $awpcp_plugin_path;
    global $hasregionsmodule;

    $output = '';

    $awpcp_browsecats_pageid=awpcp_get_page_id_by_ref('browse-categories-page-name');
    $browseadspageid=awpcp_get_page_id_by_ref('browse-ads-page-name');
    $searchadspageid=awpcp_get_page_id_by_ref('search-ads-page-name');

    // filters to provide alternative method of storing custom layouts (e.g. can be outside of this plugin's directory)
    if ( has_action('awpcp_browse_ads_template_action') || has_filter('awpcp_browse_ads_template_filter') ) {
        do_action('awpcp_browse_ads_template_action');
        $output = apply_filters('awpcp_browse_ads_template_filter');
        return;

    } else if (file_exists("$awpcp_plugin_path/awpcp_display_ads_my_layout.php") &&
               get_awpcp_option('activatemylayoutdisplayads'))
    {
        include("$awpcp_plugin_path/awpcp_display_ads_my_layout.php");

    } else {
        $output .= "<div id=\"classiwrapper\">";

        $uiwelcome=stripslashes_deep(get_awpcp_option('uiwelcome'));

        $output .= apply_filters( 'awpcp-content-before-listings-page', '' );
        $output .= "<div class=\"uiwelcome\">$uiwelcome</div>";
        $output .= awpcp_menu_items();

        if ($hasregionsmodule ==  1) {
            // Do not show Region Control form when showing Search Ads page
            // search result. Changing the current location will redirect the user
            // to the form instead of a filterd version of the form and that's confusing
            if ( is_page( awpcp_get_page_id_by_ref( 'search-ads-page-name' ) ) && isset( $_POST['a']) && $_POST['a'] == 'dosearch' ) {
                // do nothing
            } else {
                $output .= awpcp_region_control_selector();
            }
        }

        $output .= $before_content;

        $tbl_ads = $wpdb->prefix . "awpcp_ads";

        $from="$tbl_ads";

        $ads_exist = ads_exist();

        if (!$ads_exist) {
            $showcategories="<p style=\"padding:10px\">";
            $showcategories.=__("There are currently no ads in the system","AWPCP");
            $showcategories.="</p>";
            $pager1='';
            $pager2='';

        } else {
            $awpcp_image_display_list=array();

            if ($adorcat == 'cat') {
                $tpname = get_permalink($awpcp_browsecats_pageid);
            } elseif ($adorcat == 'search') {
                $tpname = get_permalink($searchadspageid);
            } elseif ( preg_match( '/^custom:/', $adorcat ) ) {
                $tpname = str_replace( 'custom:', '', $adorcat );
            } else {
                $tpname = get_permalink($browseadspageid);
            }

            $results = get_awpcp_option( 'adresultsperpage', 10 );
            $results = absint( awpcp_request_param( 'results', $results ) );
            $offset = absint( awpcp_request_param( 'offset', 0 ) );

            if ( $results === 0 ) {
                $results = 10;
            }

            $args = array(
                'order' => AWPCP_Ad::get_order_conditions( $grouporderby ),
                'offset' => $offset,
                'limit' => $results,
            );
            $ads = AWPCP_Ad::get_enabled_ads( $args, array( $where ) );

            // get_where_conditions() is called from get_enabled_ads(), we need the
            // WHERE conditions here to pass them to create_pager()
            $where = AWPCP_Ad::get_where_conditions( array( $where ) );

            if (!isset($hidepager) || empty($hidepager) ) {
                //Unset the page and action here...these do the wrong thing on display ad
                unset($_GET['page_id']);
                unset($_POST['page_id']);
                //unset($params['page_id']);
                $pager1=create_pager($from, join( ' AND ', $where ),$offset,$results,$tpname);
                $pager2=create_pager($from, join( ' AND ', $where ),$offset,$results,$tpname);
            } else {
                $pager1='';
                $pager2='';
            }

            $items = awpcp_render_listings_items( $ads, 'listings' );

            $opentable = "";
            $closetable = "";

            if (empty($ads)) {
                $showcategories="<p style=\"padding:20px;\">";
                $showcategories.=__("There were no ads found","AWPCP");
                $showcategories.="</p>";
                $pager1='';
                $pager2='';
            } else {
                $showcategories = smart_table($items, intval($results/$results), $opentable, $closetable);
            }
        }

        $show_category_id = absint( awpcp_request_param( 'category_id' ) );

        if (!isset($url_browsecatselect) || empty($url_browsecatselect)) {
            $url_browsecatselect = get_permalink($awpcp_browsecats_pageid);
        }

        if ($ads_exist) {
            $category_id = (int) awpcp_request_param('category_id', -1);
            $category_id = $category_id === -1 ? (int) get_query_var('cid') : $category_id;

            $output .= "<div class=\"changecategoryselect\"><form method=\"post\" action=\"$url_browsecatselect\">";

            $output .= '<div class="awpcp-category-dropdown-container">';
            $dropdown = new AWPCP_CategoriesDropdown();
            $output .= $dropdown->render( array( 'context' => 'search', 'name' => 'category_id', 'selected' => $category_id ) );
            $output .= '</div>';

            $output .= "<input type=\"hidden\" name=\"a\" value=\"browsecat\" />&nbsp;<input class=\"button\" type=\"submit\" value=\"";
            $output .= __("Change Category","AWPCP");
            $output .= "\" /></form></div>";

            $output .= "<div class=\"pager\">$pager1</div><div class=\"fixfloat\"></div>";

            $output .= "<div id='awpcpcatname' class=\"fixfloat\">";

            if ($category_id > 0) {
                $output .= "<h3>" . __("Category: ", "AWPCP") . get_adcatname($category_id) . "</h3>";
            }

            $output .= "</div>";
        }

        $output .= apply_filters('awpcp-display-ads-before-list', '');
        $output .= "$showcategories";

        if ($ads_exist) {
            $output .= "&nbsp;<div class=\"pager\">$pager2</div>";
        }

        if ($byl) {
            if (field_exists($field='removepoweredbysign') && !(get_awpcp_option('removepoweredbysign'))) {
                $output .= "<p><font style=\"font-size:smaller\">";
                $output .= __("Powered by ","AWPCP");
                $output .= "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";

            } elseif (field_exists($field='removepoweredbysign') && (get_awpcp_option('removepoweredbysign'))) {
                // ...

            } else {
                // $output .= "<p><font style=\"font-size:smaller\">";
                // $output .= __("Powered by ","AWPCP");
                // $output .= "<a href=\"http://www.awpcp.com\">Another Wordpress Classifieds Plugin</a> </font></p>";
            }
        }

        $output .= apply_filters( 'awpcp-content-after-listings-page', '' );
        $output .= "</div>";

    }
    return $output;
}

/**
 * @deprecated next-release
 */
function awpcp_render_ads($ads, $context='listings', $config=array(), $pagination=array()) {
    _deprecated_function( __FUNCTION__, 'next-release', 'awpcp_display_listings' );

    $config = shortcode_atts(array('show_menu' => true, 'show_intro' => true), $config);

    if (has_action('awpcp_browse_ads_template_action') || has_filter('awpcp_browse_ads_template_filter')) {
        do_action('awpcp_browse_ads_template_action');
        $output = apply_filters('awpcp_browse_ads_template_filter');
        return;
    } else if (file_exists(AWPCP_DIR . "/awpcp_display_ads_my_layout.php") && get_awpcp_option('activatemylayoutdisplayads')) {
        include(AWPCP_DIR . "/awpcp_display_ads_my_layout.php");
        return;
    }

    $items = awpcp_render_listings_items( $ads, $context );

    $before_content = apply_filters('awpcp-listings-before-content', array(), $context);
    $after_content = apply_filters('awpcp-listings-after-content', array(), $context);
    $pagination_block = is_array( $pagination ) ? awpcp_pagination( $pagination, '' ) : '';

    ob_start();
        include(AWPCP_DIR . '/frontend/templates/listings.tpl.php');
        $output = ob_get_contents();
    ob_end_clean();

    return $output;
}
