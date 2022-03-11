<?php
/**
 * @package AWPCP\Helpers
 */

/**
 * A helper class used to clear ads information from Facebook cache so that
 * the social snippets show up to date content when the URLs are shared.
 */
class AWPCP_FacebookCacheHelper {

    public function handle_clear_cache_event_hook( $ad_id ) {
		_deprecated_function( __FUNCTION__, '4.1.8' );
    }
}
