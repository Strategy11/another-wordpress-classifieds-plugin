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

		if ( $email_info['notify_expiring'] == false && $email_info['notify_admin'] == false ) {
			return;
		}

		$renderer    = awpcp_listing_renderer();
		$adstartdate = date( 'D M j Y G:i:s', strtotime( $renderer->get_plain_start_date( $ad ) ) );

		$body = $email_info['bodybase'];
		$body.= "\n\n";
		$body.= __( 'Listing Details', 'another-wordpress-classifieds-plugin' );
		$body.= "\n\n";
		$body.= __( 'Ad Title:', 'another-wordpress-classifieds-plugin' );
		$body.= ' ' . $renderer->get_listing_title( $ad );
		$body.= "\n\n";
		$body.= __( 'Posted:', 'another-wordpress-classifieds-plugin' );
		$body.= " $adstartdate";
		$body.= "\n\n";

		$body.= __( 'Renew your ad by visiting:', 'another-wordpress-classifieds-plugin' );
		$body.= ' ' . urldecode( awpcp_get_renew_ad_url( $ad->ID ) );
		$body.= "\n\n";

		$from = awpcp_admin_email_from();
		if ( $email_info['notify_expiring'] ) {
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
	public static function get_expiring_email() {
		$email_info = [
			'notify_admin'    => get_awpcp_option( 'notifyofadexpired' ),
			'notify_expiring' => get_awpcp_option( 'notifyofadexpiring' ),
			'bodybase'        => get_awpcp_option( 'adexpiredbodymessage' ),
		];

		// allow users to use %s placeholder for the website name in the subject line
		$subject                = get_awpcp_option( 'adexpiredsubjectline' );
		$email_info['subject']  = sprintf( $subject, awpcp_get_blog_name() );

		return $email_info;
	}
}
