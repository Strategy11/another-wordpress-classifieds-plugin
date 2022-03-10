<?php
/**
 * @package AWPCP\Admin\Listings
 */

/**
 * Handle preping and sending emails.
 *
 * @since x.x
 */
class AWPCP_SendEmails {

	/**
	 * @since x.x
	 */
	public static function send_expiring( $ad, $email_info = [] ) {
		if ( empty( $email_info ) ) {
			$email_info = self::get_expiring_email();
		}

		if ( ! $email_info['notify_expiring'] && ! $email_info['notify_admin'] ) {
			return;
		}

		$body = self::expiring_body( $ad, $email_info['bodybase'] );
		$from = awpcp_admin_email_from();

		if ( $email_info['notify_expiring'] ) {
			$renderer = awpcp_listing_renderer();
			$user_email = awpcp_format_recipient_address( $renderer->get_contact_email( $ad) );
			if ( ! empty( $user_email ) ) {
				$email = new AWPCP_Email();

				$email->to      = $user_email;
				$email->from    = $from;
				$email->subject = $email_info['subject'];
				$email->body    = $body;

				$email->send();
			}
		}

		if ( $email_info['notify_admin'] ) {
			$email = new AWPCP_Email();

			$email->to      = awpcp_admin_email_to();
			$email->from    = $from;
			$email->subject = $email_info['subject'];
			$email->body    = $body;

			$email->send();
		}
	}

	/**
	 * @since x.x
	 */
	private static function expiring_body( $listing, $body ) {
		$renderer   = awpcp_listing_renderer();
		$start_date = date( 'D M j Y G:i:s', strtotime( $renderer->get_plain_start_date( $listing ) ) );

		$body.= "\n\n";
		$body.= __( 'Listing Details', 'another-wordpress-classifieds-plugin' );
		$body.= "\n\n";
		$body.= __( 'Ad Title:', 'another-wordpress-classifieds-plugin' );
		$body.= ' ' . $renderer->get_listing_title( $listing );
		$body.= "\n\n";
		$body.= __( 'Posted:', 'another-wordpress-classifieds-plugin' );
		$body.= ' ' . $start_date;
		$body.= "\n\n";

		$body.= __( 'Renew your ad by visiting:', 'another-wordpress-classifieds-plugin' );
		$body.= ' ' . urldecode( awpcp_get_renew_ad_url( $listing->ID ) );
		$body.= "\n\n";

		return $body;
	}

	/**
	 * @since x.x
	 */
	public static function get_expiring_email() {
		$email_info = [
			'notify_admin'    => ! empty( get_awpcp_option( 'notifyofadexpired' ) ),
			'notify_expiring' => ! empty( get_awpcp_option( 'notifyofadexpiring' ) ),
			'bodybase'        => get_awpcp_option( 'adexpiredbodymessage' ),
			'subject'         => self::expiring_subject(),
		];

		return $email_info;
	}

	/**
	 * Allow users to use %s placeholder for the website name in the subject line.
	 */
	private static function expiring_subject() {
		$subject = get_awpcp_option( 'adexpiredsubjectline' );
		return sprintf( $subject, awpcp_get_blog_name() );
	}

	/**
	 * When the user clicks the renew ad link, AWPCP uses
	 * the is_about_to_expire() method to decide if the Ad
	 * can be renewed. We double check here to make
	 * sure users can use the link in the email immediately.
	 *
	 * @since x.x
	 */
	public static function send_renewal( $listing ) {
		$listing_renderer = awpcp_listing_renderer();

		if ( ! $listing_renderer->is_about_to_expire( $listing ) ) {
			return;
		}

		$email = new AWPCP_Email();

		$email->from    = awpcp_admin_email_from();
		$email->to      = awpcp_format_recipient_address( $listing_renderer->get_contact_email( $listing ) );
		$email->subject = self::renewal_subject( $listing );
		$email->body    = self::renewal_body( $listing );

		if ( $email->send() ) {
			awpcp_wordpress()->update_post_meta( $listing->ID, '_awpcp_renew_email_sent', true );
		}
	}

	public static function renewal_subject( $listing ) {
		$subject_template = get_awpcp_option( 'renew-ad-email-subject' );
		$subject_template = str_replace( '%d', '%s', $subject_template );

		return sprintf( $subject_template, self::days_before_listing_expires( $listing ) );
	}

	private static function days_before_listing_expires( $listing ) {
		$listing_renderer = awpcp_listing_renderer();
		$days_left        = $listing_renderer->days_until_expires( $listing );

		if ( $days_left == 0 || $days_left >= 1 ) {
			return floor( $days_left );
		}
		return __( 'less than 1', 'another-wordpress-classifieds-plugin' );
	}

	public static function renewal_body( $listing ) {
		$settings     = awpcp()->settings;
		$introduction = $settings->get_option( 'renew-ad-email-body' );
		if ( strpos( $introduction, '%d' ) !== false ) {
			$days_before_listing_expires = self::days_before_listing_expires( $listing );
			$introduction = sprintf( str_replace( '%d', '%s', $introduction ), $days_before_listing_expires );
		}

		$listing_renderer = awpcp_listing_renderer();
		$listing_title    = $listing_renderer->get_listing_title( $listing );
		$start_date       = $listing_renderer->get_start_date( $listing );
		$end_date         = $listing_renderer->get_end_date( $listing );
		$renew_url        = urldecode( awpcp_get_renew_ad_url( $listing->ID ) );

		ob_start();
		include AWPCP_DIR . '/templates/email/listing-is-about-to-expire-notification.plain.tpl.php';
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
