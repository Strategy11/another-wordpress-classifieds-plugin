<?php

/**
 * @since 3.5.4
 */
function awpcp_plugin_rewrite_rules() {
    return new AWPCP_Plugin_Rewrite_Rules();
}

/**
 * @since 3.5.4
 */
class AWPCP_Plugin_Rewrite_Rules {

    /**
     * WP_Rewrite is not available when plugins are loaded. The dependency
     * will be hidden in this method until the minimum supported version of
     * WordPress guarantees that flush_rules is not executed before wp_loaded.
     */
    public function rewrite() {
        return $GLOBALS['wp_rewrite'];
    }

    public function add_rewrite_rules( $rules ) {
        $pages = awpcp_pages_with_rewrite_rules();
        $pages_uris = $this->get_pages_uris( $pages );

        $this->add_api_rewrite_rules();
        $this->add_plugin_pages_rewrite_rules( $pages_uris );

        return $rules;
    }

    private function get_pages_uris( $pages ) {
        $uris = array();

        foreach ( $pages as $refname ) {
            if ( $id = awpcp_get_page_id_by_ref( $refname ) ) {
                if ( $page = get_page( $id ) ) {
                    $uris[ $refname ] = get_page_uri( $page->ID );
                }
            }
        }

        return $uris;
    }

    private function add_api_rewrite_rules() {
        // Payments API rewrite rules
        $this->add_rewrite_rule(
            'awpcpx/payments/return/([a-zA-Z0-9]+)',
            'index.php?awpcpx=1&awpcp-module=payments&awpcp-action=return&awpcp-txn=$matches[1]',
            'top'
        );
        $this->add_rewrite_rule(
            'awpcpx/payments/notify/([a-zA-Z0-9]+)',
            'index.php?awpcpx=1&awpcp-module=payments&awpcp-action=notify&awpcp-txn=$matches[1]',
            'top'
        );
        $this->add_rewrite_rule(
            'awpcpx/payments/cancel/([a-zA-Z0-9]+)',
            'index.php?awpcpx=1&awpcp-module=payments&awpcp-action=cancel&awpcp-txn=$matches[1]',
            'top'
        );

        // Ad Email Verification rewrite rules
        $this->add_rewrite_rule(
            'awpcpx/listings/verify/([0-9]+)/([a-zA-Z0-9]+)',
            'index.php?awpcpx=1&awpcp-module=listings&awpcp-action=verify&awpcp-ad=$matches[1]&awpcp-hash=$matches[2]',
            'top'
        );
    }

    /**
     * @see WP's add_rewrite_rule().
     */
    private function add_rewrite_rule( $pattern, $redirect, $position ) {
        $this->rewrite()->add_rule( $this->create_rewrite_rule_regex( $pattern ), $redirect, $position );
    }

    private function create_rewrite_rule_regex( $pattern ) {
        return str_replace( '%pagename%', $pattern, $this->rewrite()->get_page_permastruct() );
    }

    private function add_plugin_pages_rewrite_rules( $pages_uris ) {
        $pages_rules = $this->get_pages_rewrite_rules_definitions();

        foreach ( $pages_rules as $page_ref => $rules ) {
            if ( ! isset( $pages_uris[ $page_ref ] ) ) {
                continue;
            }

            foreach ( $rules as $rule ) {
                $regex = str_replace( '<page-uri>', $pages_uris[ $page_ref ], $rule['regex'] );
                $this->add_rewrite_rule( $regex, $rule['redirect'], $rule['position'] );
            }
        }
    }

    private function get_pages_rewrite_rules_definitions() {
        $view_categories = sanitize_title(get_awpcp_option('view-categories-page-name'));

        return array(
            'show-ads-page-name' => array(
                array(
                    'regex' => '(<page-uri>)/(\d+)' ,
                    'redirect' => 'index.php?pagename=$matches[1]&id=$matches[2]',
                    'position' => 'top'
                ),
            ),
            'reply-to-ad-page-name' => array(
                array(
                    'regex' => '(<page-uri>)/(.+?)/(.+?)' ,
                    'redirect' => 'index.php?pagename=$matches[1]&id=$matches[2]',
                    'position' => 'top'
                ),
            ),
            'edit-ad-page-name' => array(
                array(
                    'regex' => '(<page-uri>)(?:/([0-9]+))?/?$' ,
                    'redirect' => 'index.php?pagename=$matches[1]&id=$matches[2]',
                    'position' => 'top'
                ),
            ),
            'browse-categories-page-name' => array(
                array(
                    'regex' => '(<page-uri>)/(.+?)' ,
                    'redirect' => 'index.php?pagename=$matches[1]&cid=$matches[2]&a=browsecat',
                    'position' => 'top'
                ),
            ),
            'payment-thankyou-page-name' => array(
                array(
                    'regex' => '(<page-uri>)/([a-zA-Z0-9]+)' ,
                    'redirect' => 'index.php?pagename=$matches[1]&awpcp-txn=$matches[2]',
                    'position' => 'top'
                ),
            ),
            'payment-cancel-page-name' => array(
                array(
                    'regex' => '(<page-uri>)/([a-zA-Z0-9]+)' ,
                    'redirect' => 'index.php?pagename=$matches[1]&awpcp-txn=$matches[2]',
                    'position' => 'top'
                ),
            ),
            'main-page-name' => array(
                array(
                    'regex' => '(<page-uri>)/('.$view_categories.')($|[/?])' ,
                    'redirect' => 'index.php?pagename=$matches[1]&layout=2&cid='.$view_categories,
                    'position' => 'top'
                ),
                array(
                    'regex' => '(<page-uri>)/(setregion)/(.+?)/(.+?)' ,
                    'redirect' => 'index.php?pagename=$matches[1]&regionid=$matches[3]&a=setregion',
                    'position' => 'top'
                ),
                array(
                    'regex' => '(<page-uri>)/(classifiedsrss)/(\d+)' ,
                    'redirect' => 'index.php?pagename=$matches[1]&awpcp-action=rss&cid=$matches[3]',
                    'position' => 'top'
                ),
                array(
                    'regex' => '(<page-uri>)/(classifiedsrss)' ,
                    'redirect' => 'index.php?pagename=$matches[1]&awpcp-action=rss',
                    'position' => 'top'
                ),
            ),
        );
    }
}
