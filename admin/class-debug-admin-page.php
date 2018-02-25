<?php

function awpcp_debug_admin_page() {
	return new AWPCP_DebugAdminPage( awpcp()->settings, $GLOBALS['wpdb'] );
}

class AWPCP_DebugAdminPage {

    private $settings;
    private $db;

	public function __construct( $settings, $db ) {
        $this->settings = $settings;
        $this->db = $db;
		// TODO: the page is instatiated after init has been executed,
		//  update the router to call a special method in the page object
		//  during template_redirect.
		add_action('init', array($this, 'download'));
	}

	private function sanitize($setting, $value) {
		static $hosts_regexp = '';
		static $email_regexp = '';

		if (empty($hosts_regexp)) {
			$hosts = array_unique(array(parse_url(home_url(), PHP_URL_HOST),
						   				parse_url(site_url(), PHP_URL_HOST)));
			$hosts_regexp = '/' . preg_quote(join('|', $hosts), '/') . '/';
			$email_regexp = '/[_a-z0-9-+]+(\.[_a-z0-9-+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})/';
		}

		$sanitized = (is_object($value) || is_array($value)) ? print_r($value, true) : $value;
		// remove Website domain
		$sanitized = preg_replace($hosts_regexp, '<host>', $sanitized);
		// remove email addresses
		$sanitized = preg_replace($email_regexp, '<email>', $sanitized);

		return $sanitized;
	}

	/**
	 * Renders an HTML page with AWPCP informaiton useful for debugging tasks.
	 *
	 * @since 2.0.7
	 */
	private function render($download=false) {
		global $wp_rewrite;

        $plugin_pages_ids = awpcp_get_plugin_pages_ids();
        $page_objects = get_pages( array( 'include' => array_values( $plugin_pages_ids ) ) );

        $plugin_pages = array();

		foreach ( $page_objects as $page ) {
        	$plugin_pages[ $page->ID ] = $page;
        }

		$params = array(
            'plugin_pages_info' => $plugin_pages_info,
            'debug_info' => $this,
            'options' => $this->filtered_options(),
            'pages' => $plugin_pages,
            'rules' => (array) $wp_rewrite->wp_rewrite_rules(),
        );

        $template = AWPCP_DIR . '/admin/templates/admin-panel-debug.tpl.php';

		return awpcp_render_template( $template, $params );
	}

    private function filtered_options() {
        $safe_options = $this->settings->options;

        $safe_options['awpcp_installationcomplete'] = get_option('awpcp_installationcomplete');
        $safe_options['awpcp_pagename_warning'] = get_option('awpcp_pagename_warning');
        $safe_options['widget_awpcplatestads'] = get_option('widget_awpcplatestads');
        $safe_options['awpcp_db_version'] = get_option('awpcp_db_version');

        foreach ( $this->get_blacklisted_options() as $option_name ) {
            if ( isset( $safe_options[ $option_name ] ) ) {
                unset( $safe_options[ $option_name ] );
            }
        }

        foreach ( $safe_options as $option_name => $option_value ) {
            $safe_options[ $option_name ] = $this->sanitize( $option_name, $option_value );
        }

        return $safe_options;
    }

    private function get_blacklisted_options() {
        // TODO: add other settings from premium modules
        return array(
            'tos',
            'admin-recipient-email',
            'awpcpadminemail',
            'paypalemail',
            '2checkout',
            'smtphost', 'smtpport', 'smtpusername', 'smtppassword',
            'googlecheckoutmerchantID', 'googlecheckoutsandboxseller', 'googlecheckoutbuttonurl',
            'authorize.net-login-id', 'authorize.net-transaction-key',
            'paypal-pro-username', 'paypal-pro-password', 'paypal-pro-signature',
        );
    }

	/**
	 * Allow users to download Debug Info as an HTML file.
	 *
	 * @since 2.0.7
	 */
	public function download() {
		global $pagenow;

		if (!awpcp_current_user_is_admin()) return;

		if ($pagenow == 'admin.php' && awpcp_request_param('page') === 'awpcp-debug'
									&& awpcp_request_param('download') === 'debug-info') {
			$filename = sprintf('awpcp-debug-info-%s.html', date('Y-m-d-Hi', current_time('timestamp')));

			header('Content-Description: File Transfer');
			header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
			header('Content-Disposition: attachment; filename=' . $filename);
	        header("Pragma: no-cache");

			die($this->render(true));
		}
	}

	/**
	 * Handler for the Classifieds->Debug AWPCP Admin page.
	 *
	 * @since unknown
	 */
	public function dispatch() {
		return $this->render();
	}
}
