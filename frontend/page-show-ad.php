<?php

class AWPCP_Show_Ad_Page {

	public function __construct() {
		add_filter('awpcp-ad-details', array($this, 'oembed'));
	}

	/**
	 * Acts on awpcp-ad-details filter to add oEmbed support
	 */
	public function oembed($content) {
		global $wp_embed;

		$usecache = $wp_embed->usecache;
		$wp_embed->usecache = false;
		$content = $wp_embed->run_shortcode($content);
		$content = $wp_embed->autoembed($content);
		$wp_embed->usecache = $usecache;

		return $content;
	}

    /**
     * TODO: Get instances of all necessary objects as constructor arguments.
     */
	public function dispatch() {
        $listings_content_renderer = awpcp()->container['ListingsContentRenderer'];

        $post = awpcp_listings_collection()->get( awpcp_request()->get_current_listing_id() );

        return $listings_content_renderer->render(
            apply_filters( 'the_content', $post->post_content ),
            $post
        );
	}
}


/**
 * @since 3.0
 */
function awpcp_get_ad_location($ad_id, $country=false, $county=false, $state=false, $city=false) {
	$places = array();

	if (!empty($city)) {
		$places[] = $city;
	}
	if (!empty($county)) {
		$places[] = $county;
	}
	if (!empty($state)) {
		$places[] = $state;
	}
	if (!empty($country)) {
		$places[] = $country;
	}

	if (!empty($places)) {
		$location = sprintf('%s: %s', __("Location",'another-wordpress-classifieds-plugin'), join(', ', $places));
	} else {
		$location = '';
	}

	return $location;
}


/**
 * Handles AWPCPSHOWAD shortcode.
 *
 * @param $adid An Ad ID.
 * @param $omitmenu
 * @param $preview true if the function is used to show an ad just after
 *				   it was posted to the website.
 * @param $send_email if true and $preview=true, a success email will be send
 * 					  to the admin and poster user.
 *
 * @return Show Ad page content.
 */
function showad( $adid=null, $omitmenu=false, $preview=false, $send_email=true, $show_messages=true ) {
	global $wpdb;

	awpcp_maybe_add_thickbox();
	wp_enqueue_script('awpcp-page-show-ad');

    $awpcp = awpcp();
    $listing_renderer = awpcp_listing_renderer();

    $awpcp->js->set( 'page-show-ad-flag-ad-nonce', wp_create_nonce('flag_ad') );

    $awpcp->js->localize( 'page-show-ad', array(
        'flag-confirmation-message' => __( 'Are you sure you want to flag this ad?', 'another-wordpress-classifieds-plugin' ),
        'flag-success-message' => __( 'This Ad has been flagged.', 'another-wordpress-classifieds-plugin' ),
        'flag-error-message' => __( 'An error occurred while trying to flag the Ad.', 'another-wordpress-classifieds-plugin' )
    ) );

	$preview = $preview === true || 'preview' == awpcp_array_data('adstatus', '', $_GET);
	$is_moderator = awpcp_current_user_is_moderator();
	$messages = array();

	$permastruc = get_option('permalink_structure');
	if (!isset($adid) || empty($adid)) {
		if (isset($_REQUEST['adid']) && !empty($_REQUEST['adid'])) {
			$adid = $_REQUEST['adid'];
		} elseif (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
			$adid = $_REQUEST['id'];
		} else if (isset($permastruc) && !empty($permastruc)) {
			$adid = get_query_var( 'id' );
		} else {
			$adid = 0;
		}
	}

	$adid = absint( $adid );

	if (!empty($adid)) {
		// filters to provide alternative method of storing custom
		// layouts (e.g. can be outside of this plugin's directory)
		if ( has_action( 'awpcp_single_ad_template_action' ) || has_filter( 'awpcp_single_ad_template_filter' ) ) {
			do_action( 'awpcp_single_ad_template_action' );
			return apply_filters( 'awpcp_single_ad_template_filter' );

		} else {
			try {
				$ad = awpcp_listings_collection()->get( $adid );
			} catch ( AWPCP_Exception $e ) {
				$ad = null;
			}

			if (is_null($ad)) {
				$message = __( 'Sorry, that listing is not available. Please try browsing or searching existing listings.', 'another-wordpress-classifieds-plugin' );
				return '<div id="classiwrapper">' . awpcp_print_error($message) . '</div><!--close classiwrapper-->';
			}

			if ( $ad->post_author > 0 && $ad->post_author == wp_get_current_user()->ID ) {
				$is_ad_owner = true;
			} else {
				$is_ad_owner = false;
			}

			$content_before_page = apply_filters( 'awpcp-content-before-listing-page', '' );
			$content_after_page = apply_filters( 'awpcp-content-after-listing-page', '' );

			$output = '<div id="classiwrapper">%s<!--awpcp-single-ad-layout-->%s</div><!--close classiwrapper-->';
			$output = sprintf( $output, $content_before_page, $content_after_page );

			if (!$is_moderator && !$is_ad_owner && !$preview && $listing_renderer->is_disabled( $ad ) ) {
				$message = __('The Ad you are trying to view is pending approval. Once the Administrator approves it, it will be active and visible.', 'another-wordpress-classifieds-plugin');
				return str_replace( '<!--awpcp-single-ad-layout-->', awpcp_print_error( $message ), $output );
			}

			if ( awpcp_request_param('verified') && $listing_renderer->is_verified( $ad ) ) {
				$messages[] = awpcp_print_message( __( 'Your email address was successfully verified.', 'another-wordpress-classifieds-plugin' ) );
			}

			if ($show_messages && $is_moderator && $listing_renderer->is_disabled( $ad ) ) {
				$message = __('This Ad is currently disabled until the Administrator approves it. Only you (the Administrator) and the author can see it.', 'another-wordpress-classifieds-plugin');
				$messages[] = awpcp_print_error($message);
			} else if ( $show_messages && ( $is_ad_owner || $preview ) && ! $listing_renderer->is_verified( $ad ) ) {
				$message = __('This Ad is currently disabled until you verify the email address used for the contact information. Only you (the author) can see it.', 'another-wordpress-classifieds-plugin');
				$messages[] = awpcp_print_error($message);
			} else if ( $show_messages && ( $is_ad_owner || $preview ) && $listing_renderer->is_disabled( $ad ) ) {
				$message = __('This Ad is currently disabled until the Administrator approves it. Only you (the author) can see it.', 'another-wordpress-classifieds-plugin');
				$messages[] = awpcp_print_error($message);
			}

            $layout = awpcp_get_listing_single_view_layout( $ad );
			$layout = awpcp_do_placeholders( $ad, $layout, 'single' );

			$output = str_replace( '<!--awpcp-single-ad-layout-->', join('', $messages) . $layout, $output );
			$output = apply_filters('awpcp-show-ad', $output, $adid);

			if ( ! awpcp_request()->is_bot() ) {
				awpcp_listings_api()->increase_visits_count( $ad );
			}
		}
	} else {
		$query = array(
            'posts_per_page' => absint( awpcp_request_param( 'results', get_awpcp_option( 'adresultsperpage', 10 ) ) ),
            'offset' => absint( awpcp_request_param( 'offset', 0 ) ),
			'orderby' => get_awpcp_option( 'groupbrowseadsby' ),
		);

		$output = awpcp_display_listings_in_page( $query, 'show-listing' );
	}

	return $output;
}

function awpcp_get_listing_single_view_layout( $listing ) {
    $layout = get_awpcp_option( 'awpcpshowtheadlayout' );

    if ( empty( $layout ) ) {
        $layout = awpcp()->settings->get_option_default_value( 'awpcpshowtheadlayout' );
    }

    $layout = apply_filters( 'awpcp-single-ad-layout', $layout, $listing );

    if ( get_awpcp_option( 'allow-wordpress-shortcodes-in-single-template' ) ) {
        $layout = do_shortcode( $layout );
    }

    return $layout;
}
