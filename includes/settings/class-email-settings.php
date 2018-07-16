<?php
/**
 * @package AWPCP\Settings
 */

// phpcs:disable

/**
 * Register Email related settings.
 */
class AWPCP_EmailSettings {

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD)
     */
    public function register_settings( $settings_manager ) {
        $settings_manager->add_settings_group( [
            'name'     => __( 'Email', 'another-wordpress-classifieds-plugin' ),
            'id'       => 'email-settings',
            'priority' => 90,
        ] );

        $settings_manager->add_settings_subgroup( [
            'name'   => __( 'Email', 'another-wordpress-classifieds-plugin' ),
            'id'     => 'email-settings',
            'parent' => 'email-settings',
        ] );

		// Section: General Email Settings

        $group = 'email-settings';
        $key = 'default';

        $settings_manager->add_section($group, __('General Email Settings', 'another-wordpress-classifieds-plugin'), 'default', 20, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'admin-recipient-email', __( 'TO email address for outgoing emails', 'another-wordpress-classifieds-plugin' ), 'textfield', '', __( 'Emails are sent to your WordPress admin email. If you prefer to receive emails in a different address, please enter it here.', 'another-wordpress-classifieds-plugin' ) );

		$settings_manager->add_setting(
			$key,
			'awpcpadminemail',
			__( 'FROM email address for outgoing emails', 'another-wordpress-classifieds-plugin' ),
			'textfield',
			'',
			__( 'Emails go out using your WordPress admin email. If you prefer to use a different email enter it here. Some servers will not process outgoing emails that have an email address from gmail, yahoo, hotmail and other free email services in the FROM field. Some servers will also not process emails that have an email address that is different from the email address associated with your hosting account in the FROM field. If you are with such a webhost you need to make sure your WordPress admin email address is tied to your hosting account.', 'another-wordpress-classifieds-plugin' )
		);

		$setting_label = __( 'Use wordpress@<website-domain> as the FROM email address for outgoing emails.', 'another-wordpress-classifieds-plugin' );
		$setting_label = str_replace( '<website-domain>', awpcp_request()->domain( false ), $setting_label );

		$settings_manager->add_setting(
			$key,
			'sent-emails-using-wordpress-email-address',
			$setting_label,
			'checkbox',
			0,
			__( "That's the address WordPress uses to send its emails. If you are receiving the registration emails and other WordPress notifications succesfully, then you may want to enable this setting to use the same email address for all the outgoing messages. If enabled, the FROM email address for outgoing emails setting is ignored.", 'another-wordpress-classifieds-plugin' )
		);

		$settings_manager->add_setting( $key, 'usesenderemailinsteadofadmin', __( 'Use sender email for reply messages', 'another-wordpress-classifieds-plugin' ), 'checkbox', 0, __( 'Check this to use the name and email of the sender in the FROM field when someone replies to an ad. When unchecked the messages go out with the website name and WP admin email address in the from field. Some servers will not process outgoing emails that have an email address from gmail, yahoo, hotmail and other free email services in the FROM field. Some servers will also not process emails that have an email address that is different from the email address associated with your hosting account in the FROM field. If you are with such a webhost you need to leave this option unchecked and make sure your WordPress admin email address is tied to your hosting account.', 'another-wordpress-classifieds-plugin' ) );

        /* translators: full-email-address=John Doe <john.doe@example.com>, short-email-address=john.doe@example.com */
        $description = __( 'If checked, whenever the name of the recipient is available, emails will be sent to <full-email-address> instead of just <short-email-address>. Some email servers, however, have problems handling email address that include the name of the recipient. If emails sent by the plugin are not being delivered properly, try unchecking this settting.' );
        $description = str_replace( '<full-email-address>', '<strong>' . esc_html( 'John Doe <john.doe@example.com>' ) . '</strong>', $description );
        $description = str_replace( '<short-email-address>', '<strong>' . esc_html( 'john.doe@example.com' ) . '</strong>', $description );

        $settings_manager->add_setting(
            $key,
            'include-recipient-name-in-email-address',
            __( 'Include the name of the recipient in the email address', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            1,
            $description
        );

		$settings_manager->add_setting( $key, 'include-ad-access-key', __( 'Include Ad access key in email messages', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( "Include Ad access key in email notifications. You may want to uncheck this option if you are using the Ad Management panel, but is not necessary.", 'another-wordpress-classifieds-plugin' ) );

		// Section: Ad Posted Message

        $key = 'ad-posted-message';

        $settings_manager->add_section($group, __('Ad Posted Message', 'another-wordpress-classifieds-plugin'), 'ad-posted-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'listingaddedsubject', __( 'Subject for Ad posted notification email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Your Classified Ad listing has been submitted', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when someone posts an Ad', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'listingaddedbody', __( 'Body for Ad posted notification email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'Thank you for submitting your Classified Ad. The details of your ad are shown below.', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when someone posts an Ad', 'another-wordpress-classifieds-plugin' ) );

		// Section: Reply to Ad Message

        $key = 'reply-to-ad-message';

        $settings_manager->add_section($group, __('Reply to Ad Message', 'another-wordpress-classifieds-plugin'), 'reply-to-ad-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'contactformsubjectline', __( 'Subject for Reply to Ad email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Response to your AWPCP Demo Ad', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when someone replies to Ad', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'contactformbodymessage', __( 'Body for Reply to Ad email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'Someone has responded to your AWPCP Demo Ad', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when someone replies to Ad', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'notify-admin-about-contact-message', __( 'Notify admin about contact message', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( 'An email will be sent to the administrator every time a visitor sends a message to one of the Ad posters through the Reply to Ad page.', 'another-wordpress-classifieds-plugin' ) );

		// Section: Request Ad Message

        $key = 'request-ad-message';

        $settings_manager->add_section($group, __('Resend Access Key Message', 'another-wordpress-classifieds-plugin'), 'request-ad-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'resendakeyformsubjectline', __( 'Subject for Request Ad Access Key email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( "The Classified Ad's ad access key you requested", 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when someone requests their ad access key resent', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'resendakeyformbodymessage', __( 'Body for Request Ad Access Key email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( "You asked to have your Classified Ad's access key resent. Below are all the Ad access keys in the system that are tied to the email address you provided", 'another-wordpress-classifieds-plugin' ), __('Message body text for email sent out when someone requests their ad access key resent', 'another-wordpress-classifieds-plugin' ) );

		// Section: Verify Email Message

        $key = 'verify-email-message';

        $settings_manager->add_section($group, __('Verify Email Message', 'another-wordpress-classifieds-plugin'), 'verify-email-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'verifyemailsubjectline', __( 'Subject for Verification email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Verify the email address used for Ad $title', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out to verify the email address.', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'verifyemailbodymessage', __( 'Body for Verification email', 'another-wordpress-classifieds-plugin' ), 'textarea', _x( "Hello \$author_name \n\nYou recently posted the Ad \$title to \$website_name. \n\nIn order to complete the posting process you have to verify your email address. Please click the link below to complete the verification process. You will be redirected to the website where you can see your Ad. \n\n\$verification_link \n\nAfter you verify your email address, the administrator will be notified about the new Ad. If moderation is enabled, your Ad will remain in a disabled status until the administrator approves it.\n\n\$website_name\n\n\$website_url", 'another-wordpress-classifieds-plugin' ), __('You can use the following placeholders to personalize the body of the email: $title, $author_name,$verification_email, $website_name, $website_url.', 'another-wordpress-classifieds-plugin' ) );

		// Section: Incomplete Payment Message

        $key = 'incomplete-payment-message';

        $settings_manager->add_section($group, __('Incomplete Payment Message', 'another-wordpress-classifieds-plugin'), 'incomplete-payment-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'paymentabortedsubjectline', __( 'Subject for Incomplete Payment email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'There was a problem processing your payment', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when the payment processing does not complete', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'paymentabortedbodymessage', __( 'Body for Incomplete Payment email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'There was a problem encountered during your attempt to submit payment. If funds were removed from the account you tried to use to make a payment please contact the website admin or the payment website customer service for assistance.', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when the payment processing does not complete', 'another-wordpress-classifieds-plugin' ) );

		// Section: Renew Ad Message

        $key = 'renew-ad-message';

        $settings_manager->add_section($group, __('Renew Ad Message', 'another-wordpress-classifieds-plugin'), 'renew-ad-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'renew-ad-email-subject', __( 'Subject for Renew Ad email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Your classifieds listing Ad will expire in %d days.', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when an Ad is about to expire.', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'renew-ad-email-body', __( 'Body for Renew Ad email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'This is an automated notification that your Classified Ad will expire in %d days.', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when an Ad is about to expire. Use %d as placeholder for the number of days before the Ad expires.', 'another-wordpress-classifieds-plugin' ) );

		// Section: Ad Renewed Message

        $key = 'ad-renewed-message';

        $settings_manager->add_section($group, __('Ad Renewed Message', 'another-wordpress-classifieds-plugin'), 'ad-renewed-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'ad-renewed-email-subject', __( 'Subject for Ad Renewed email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Your classifieds listing "%s" has been successfully renewed.', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when an Ad is successfully renewed.', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'ad-renewed-email-body', __( 'Body for Renew Ad email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'Your classifieds listing Ad has been successfully renewed. More information below:', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when an Ad is successfully renewed. ', 'another-wordpress-classifieds-plugin' ) );

		// Section: Ad Expired Message

        $key = 'ad-expired-message';

        $settings_manager->add_section($group, __('Ad Expired Message', 'another-wordpress-classifieds-plugin'), 'ad-expired-message', 10, array($settings_manager, 'section'));

		$settings_manager->add_setting( $key, 'adexpiredsubjectline', __( 'Subject for Ad Expired email', 'another-wordpress-classifieds-plugin' ), 'textfield', __( 'Your classifieds listing at %s has expired', 'another-wordpress-classifieds-plugin' ), __( 'Subject line for email sent out when an ad has auto-expired', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'adexpiredbodymessage', __( 'Body for Ad Expired email', 'another-wordpress-classifieds-plugin' ), 'textarea', __( 'This is an automated notification that your Classified Ad has expired.', 'another-wordpress-classifieds-plugin' ), __( 'Message body text for email sent out when an ad has auto-expired', 'another-wordpress-classifieds-plugin' ) );

		// Section: Advanced Email Configuration

        $key = 'advanced';

        $settings_manager->add_section( $group, __( 'Advanced Email Configuration', 'another-wordpress-classifieds-plugin' ), 'advanced', 30, array( $settings_manager, 'section' ) );

		$settings_manager->add_setting( $key, 'usesmtp', __( 'Enable external SMTP server', 'another-wordpress-classifieds-plugin' ), 'checkbox', 0, __( 'Enabled external SMTP server (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'smtphost', __( 'SMTP host', 'another-wordpress-classifieds-plugin' ), 'textfield', 'mail.example.com', __( 'SMTP host (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'smtpport', __( 'SMTP port', 'another-wordpress-classifieds-plugin' ), 'textfield', '25', __( 'SMTP port (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'smtpusername', __( 'SMTP username', 'another-wordpress-classifieds-plugin' ), 'textfield', 'smtp_username', __( 'SMTP username (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );
		$settings_manager->add_setting( $key, 'smtppassword', __( 'SMTP password', 'another-wordpress-classifieds-plugin' ), 'password', '', __( 'SMTP password (if emails not processing normally).', 'another-wordpress-classifieds-plugin' ) );

    }

    public function validate_email_settings( $options ) {
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
     * SMTP Settings checks.
     */
    public function validate_smtp_settings( $options ) {
        // Not sure if this works, but that's what the old code did.
        $setting = 'smtppassword';
        if (isset($options[$setting])) {
            $options[$setting] = md5($options[$setting]);
        }

        return $options;
    }
}
