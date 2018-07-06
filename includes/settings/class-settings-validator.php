<?php
/**
 * @package AWPCP\Settings
 */

/**
 * @since 4.0.0
 */
class AWPCP_SettingsValidator {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Request.
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $settings, $request ) {
        $this->settings = $settings;
        $this->request  = $request;
    }

	/**
	 * Validates AWPCP settings before being saved.
	 */
	public function sanitize_settings( $new_options ) {
        $group = $this->request->post( 'group', '' );

        // Populate array with all plugin options before attempt validation.
        $new_options = array_merge( $this->settings->options, $new_options );

        if ( $group ) {
            $new_options = apply_filters( 'awpcp_validate_settings_' . $group, $new_options, $group );
        }

        $new_options = apply_filters( 'awpcp_validate_settings', $new_options, $group );

        if ( $group ) {
            do_action( 'awpcp_settings_validated_' . $group, $new_options, $group );
        }

        do_action( 'awpcp_settings_validated', $new_options, $group );

        // Filters and actions need to be executed before we update the in-memory
        // options to allow handlers to compare existing values with the ones that
        // are about to be saved.
        $this->settings->options = $new_options;

        return $this->settings->options;
	}

    // phpcs:disable

	/**
	 * General Settings checks
	 */
	public function validate_general_settings($options, $group) {
		$this->validate_akismet_settings( $options );
		$this->validate_captcha_settings( $options );

		// Enabling User Ad Management Panel will automatically enable
		// require Registration, if it isn’t enabled. Disabling this feature
		// will not disable Require Registration.
		$setting = 'enable-user-panel';
		if (isset($options[$setting]) && $options[$setting] == 1 && !get_awpcp_option('requireuserregistration')) {
			awpcp_flash(__('Require Registration setting was enabled automatically because you activated the User Ad Management panel.', 'another-wordpress-classifieds-plugin'));
			$options['requireuserregistration'] = 1;
		}

		return $options;
	}

	private function validate_akismet_settings( &$options ) {
		$setting_name = 'use-akismet-in-place-listing-form';
		$is_akismet_enabled_in_place_listing_form = isset( $options[ $setting_name ] ) && $options[ $setting_name ];

		$setting_name = 'use-akismet-in-reply-to-listing-form';
		$is_akismet_enabled_in_reply_to_listing_form = isset( $options[ $setting_name ] ) && $options[ $setting_name ];

		if ( $is_akismet_enabled_in_place_listing_form || $is_akismet_enabled_in_reply_to_listing_form ) {
			$wpcom_api_key = get_option( 'wordpress_api_key' );
			if ( !function_exists( 'akismet_init' ) ) {
				awpcp_flash( __( 'Akismet SPAM control cannot be enabled because Akismet plugin is not installed or activated.', 'another-wordpress-classifieds-plugin' ), 'error' );
				$options[ 'use-akismet-in-place-listing-form' ] = 0;
				$options[ 'use-akismet-in-reply-to-listing-form' ] = 0;
			} else if ( empty( $wpcom_api_key ) ) {
				awpcp_flash( __( 'Akismet SPAM control cannot be enabled because Akismet is not properly configured.', 'another-wordpress-classifieds-plugin' ), 'error' );
				$options[ 'use-akismet-in-place-listing-form' ] = 0;
				$options[ 'use-akismet-in-reply-to-listing-form' ] = 0;
			}
		}
	}

	private function validate_captcha_settings( &$options ) {
	$option_name = 'captcha-enabled-in-place-listing-form';
		$captcha_enabled_in_place_listing_form = isset( $options[ $option_name ] ) && $options[ $option_name ];

		$option_name = 'captcha-enabled-in-reply-to-listing-form';
		$captcha_enabled_in_reply_to_listing_form = isset( $options[ $option_name ] ) && $options[ $option_name ];

		$is_captcha_enabled = $captcha_enabled_in_place_listing_form || $captcha_enabled_in_reply_to_listing_form;

		// Verify reCAPTCHA is properly configured
		if ( $is_captcha_enabled && $options['captcha-provider'] === 'recaptcha' ) {
			if ( empty( $options[ 'recaptcha-public-key' ] ) || empty( $options[ 'recaptcha-private-key' ] ) ) {
				$options['captcha-provider'] = 'math';
			}

			if ( empty( $options[ 'recaptcha-public-key' ] ) && empty( $options[ 'recaptcha-private-key' ] )  ) {
				awpcp_flash( __( "reCAPTCHA can't be used because the public key and private key settings are required for reCAPTCHA to work properly.", 'another-wordpress-classifieds-plugin' ), 'error' );
			} else if ( empty( $options[ 'recaptcha-public-key' ] ) ) {
				awpcp_flash( __( "reCAPTCHA can't be used because the public key setting is required for reCAPTCHA to work properly.", 'another-wordpress-classifieds-plugin' ), 'error' );
			} else if ( empty( $options[ 'recaptcha-private-key' ] ) ){
				awpcp_flash( __( "reCAPTCHA can't be used because the private key setting is required for reCAPTCHA to work properly.", 'another-wordpress-classifieds-plugin' ), 'error' );
			}
		}
	}

	/**
	 * Registration Settings checks
	 */
	public function validate_registration_settings($options, $group) {
		// if Require Registration is disabled, User Ad Management Panel should be
		// disabled as well.
		$setting = 'requireuserregistration';
		if (isset($options[$setting]) && $options[$setting] == 0 && get_awpcp_option('enable-user-panel')) {
			awpcp_flash(__('User Ad Management panel was automatically deactivated because you disabled Require Registration setting.', 'another-wordpress-classifieds-plugin'));
			$options['enable-user-panel'] = 0;
		}

		if (isset($options[$setting]) && $options[$setting] == 0 && get_awpcp_option('enable-credit-system')) {
			awpcp_flash(__('Credit System was automatically disabled because you disabled Require Registration setting.', 'another-wordpress-classifieds-plugin'));
			$options['enable-credit-system'] = 0;
		}

		return $options;
	}

	/**
	 * Payment Settings checks
	 * XXX: Referenced in FAQ: http://awpcp.com/forum/faq/why-doesnt-my-currency-code-change-when-i-set-it/
	 */
	public function validate_payment_settings($options, $group) {
		$setting = 'paypalcurrencycode';

		if ( isset( $options[ $setting ] ) && ! awpcp_paypal_supports_currency( $options[ $setting ] ) ) {
			$currency_codes = awpcp_paypal_supported_currencies();
			$message = __( 'There is a problem with the PayPal Currency Code you have entered. It does not match any of the codes in our list of curencies supported by PayPal.', 'another-wordpress-classifieds-plugin' );
			$message.= '<br/><br/><strong>' . __( 'The available currency codes are', 'another-wordpress-classifieds-plugin' ) . '</strong>:<br/>';
			$message.= join(' | ', $currency_codes);
			awpcp_flash($message);

			$options[$setting] = 'USD';
		}

		$setting = 'enable-credit-system';
		if (isset($options[$setting]) && $options[$setting] == 1 && !get_awpcp_option('requireuserregistration')) {
			awpcp_flash(__('Require Registration setting was enabled automatically because you activated the Credit System.', 'another-wordpress-classifieds-plugin'));
			$options['requireuserregistration'] = 1;
		}

		if (isset($options[$setting]) && $options[$setting] == 1 && !get_awpcp_option('freepay')) {
			awpcp_flash(__('Charge Listing Fee setting was enabled automatically because you activated the Credit System.', 'another-wordpress-classifieds-plugin'));
			$options['freepay'] = 1;
		}

		$setting = 'freepay';
		if (isset($options[$setting]) && $options[$setting] == 0 && get_awpcp_option('enable-credit-system')) {
			awpcp_flash(__('Credit System was disabled automatically because you disabled Charge Listing Fee.', 'another-wordpress-classifieds-plugin'));
			$options['enable-credit-system'] = 0;
		}


		return $options;
	}

	/**
	 * SMTP Settings checks
	 */
	public function validate_smtp_settings($options, $group) {
		// Not sure if this works, but that's what the old code did
		$setting = 'smtppassword';
		if (isset($options[$setting])) {
			$options[$setting] = md5($options[$setting]);
		}

		return $options;
	}

    public function validate_email_settings( $options, $group ) {
        $settings = array(
            'awpcpadminemail' => __( '<new-value> is not a valid email address. Please check the value you entered to use as the FROM email address for outgoing messages.', 'another-wordpress-classifieds-plugin' ),
            'admin-recipient-email' => __( '<new-value> is not a valid email address. Please check the value you entered to use as recipient email address for admin notifications.', 'another-wordpress-classifieds-plugin' ),
        );

        foreach( $settings as $setting_name => $message ) {
            $validated_value = $this->validate_email_setting(
                $options,
                $setting_name,
                $message
            );

            if ( is_null( $validated_value ) ) {
                continue;
            }

            $options[ $setting_name ] = $validated_value;
        }

        return $options;
    }

    private function validate_email_setting( $options, $setting_name, $message ) {
        if ( ! isset( $options[ $setting_name ] ) ) {
            return null;
        }

        if ( empty( $options[ $setting_name ] ) ) {
            return $options[ $setting_name ];
        }

        if ( ! awpcp_is_valid_email_address( $options[ $setting_name ] ) ) {
            $new_value = '<strong>' . esc_html( $options[ $setting_name ] ) . '</strong>';
            $message   = str_replace( '<new-value>', $new_value, $message );

            awpcp_flash( $message, 'notice notice-error' );

            return $this->get_option( $setting_name );
        }

        return $options[ $setting_name ];
    }

    /**
     * Flush rewrite rules when Page settings change.
     *
     * TODO: We should check that the selected page has the corresponding shortcode
     *       and/or update the plugin to show that page's content even if the
     *       shortcode is not set.
     */
    public function page_settings_validated( $options, $group ) {
        update_option( 'awpcp-flush-rewrite-rules', true );
    }
}
