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
        $group    = $this->request->post( 'group', '' );
        $subgroup = $this->request->post( 'subgroup', '' );

        // Populate array with all plugin options before attempt validation.
        $new_options = array_merge( $this->settings->options, $new_options );

        if ( $subgroup ) {
            $new_options = apply_filters( 'awpcp_validate_settings_subgroup_' . $subgroup, $new_options, $group, $subgroup );
        }

        if ( $group ) {
            $new_options = apply_filters( 'awpcp_validate_settings_' . $group, $new_options, $group, $subgroup );
        }

        $new_options = apply_filters( 'awpcp_validate_settings', $new_options, $group, $subgroup );

        if ( $group ) {
            do_action( 'awpcp_settings_validated_' . $group, $new_options, $group, $subgroup );
        }

        do_action( 'awpcp_settings_validated', $new_options, $group, $subgroup );

        // Filters and actions need to be executed before we update the in-memory
        // options to allow handlers to compare existing values with the ones that
        // are about to be saved.
        $this->settings->options = $new_options;

        return $this->settings->options;
	}

    // phpcs:disable

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
