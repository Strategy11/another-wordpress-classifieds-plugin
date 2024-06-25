<?php

namespace {

    define( 'OBJECT', 'OBJECT' );
    define( 'OBJECT_K', 'OBJECT_K' );
    define( 'ARRAY_A', 'ARRAY_A' );
    define( 'ARRAY_N', 'ARRAY_N' );

    define( 'MINUTE_IN_SECONDS', 60 );
    define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
    define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
    define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
    define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
    define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
    define( 'ABSPATH', realpath( __FILE__ . '/../../../../' ) );
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
    define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
    define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
    define( 'WPINC', 'wp-includes' );
    define( 'PCLZIP_OPT_REMOVE_ALL_PATH', 77004 );
    define( 'PCLZIP_OPT_EXTRACT_AS_STRING', 77006 );
    define( 'EP_PAGES', 4096 );

    define( 'COOKIE_DOMAIN', '' );
    define( 'COOKIEPATH', '' );
    define( 'SITECOOKIEPATH', '' );

    define( 'AWPCP_DIR', dirname( __FILE__ ) );
    define( 'AWPCP_FILE', AWPCP_DIR . '/awpcp.php' );
    define( 'AWPCP_URL', 'http://example.com/wp-content/plugins/' . basename( AWPCP_DIR ) . '/awpcp.php' );
    define( 'AWPCP_BASENAME', basename( AWPCP_DIR ) . '/awpcp.php' );

    define( 'AWPCP_TABLE_ADFEES', 'wp_awpcp_adfees' );
    define( 'AWPCP_TABLE_ADS',  'wp_awpcp_ads' );
    define( 'AWPCP_TABLE_AD_REGIONS', 'wp_awpcp_ad_regions' );
    define( 'AWPCP_TABLE_AD_META', 'wp_awpcp_admeta' );
    define( 'AWPCP_TABLE_MEDIA', 'wp_awpcp_media' );
    define( 'AWPCP_TABLE_CATEGORIES', 'wp_awpcp_categories' );
    define( 'AWPCP_TABLE_PAYMENTS', 'wp_awpcp_payments' );
    define( 'AWPCP_TABLE_CREDIT_PLANS', 'wp_awpcp_credit_plans' );
    define( 'AWPCP_TABLE_PAGES', 'wp_awpcp_pages' );
    define( 'AWPCP_TABLE_TASKS', 'wp_awpcp_tasks' );
    define( 'AWPCP_TABLE_ADPHOTOS', 'wp_awpcp_adphotos' );

    //define( 'AWPCP_VERSION', $awpcp_db_version );
    define( 'AWPCP_LISTING_POST_TYPE', 'awpcp_listing' );
    define( 'AWPCP_CATEGORY_TAXONOMY', 'awpcp_listing_category' );
    define( 'AWPCP_LOWEST_FILTER_PRIORITY', 1000000 );

    /**
     * @return AWPCP
     */
    function awpcp() {
    }

    function debugp( $var = false ) {
    }

    function awpcp_activation_failed_notice( $content ) {
    }

    function awpcp_payfast_verify_received_data_with_fsockopen( $content ) {
    }

    /** Add-ons */
    function display_x_fields_data( $adid, $single = true ) {
    }

    /** Integrations */

    function the_seo_framework() {
    }

    class Akismet {
        /**
         * @return string
         */
        public static function get_ip_address() {
        }

        /**
         * @return array
         */
        public static function http_post( $request, $path, $ip=null ) {
        }

        /**
         * @return string
         */
        public static function get_api_key() {
        }
    }

    class PHPMailer {
        public function __construct( $exceptions = null ) {
        }
    }

    /**
     * From https://theandystratton.com/2011/shortcode-autoformatting-html-with-paragraphs-and-line-breaks for
     * old formatting issue. This may not be needed anymore.
     */
    function my_formatter( $content ) {
        $new_content = '';
        $pattern_full = '{(\[raw\].*?\[/raw\])}is';
        $pattern_contents = '{\[raw\](.*?)\[/raw\]}is';
        $pieces = preg_split( $pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE );

        foreach ( $pieces as $piece ) {
            if ( preg_match( $pattern_contents, $piece, $matches ) ) {
                $new_content .= $matches[1];
            } else {
                $new_content .= wptexturize( wpautop( $piece ) );
            }
        }
        return $new_content;
    }
}
