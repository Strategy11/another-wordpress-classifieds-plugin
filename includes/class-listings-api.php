<?php

/**
 * @since 3.0.2
 */
function awpcp_listings_api() {
    if ( ! isset( $GLOBALS['awpcp-listings-api'] ) ) {
        $GLOBALS['awpcp-listings-api'] = new AWPCP_ListingsAPI(
            awpcp_attachments_logic(),
            awpcp_attachments_collection(),
            awpcp_listing_renderer(),
            awpcp_listings_metadata(),
            awpcp_request(),
            awpcp()->settings,
            awpcp_wordpress()
        );
    }

    return $GLOBALS['awpcp-listings-api'];
}

class AWPCP_ListingsAPI {

    private $attachments_logic;
    private $attachments;
    private $listing_renderer;
    private $metadata = null;
    private $request = null;
    private $settings = null;
    private $wordpress;

    public function __construct( $attachments_logic, $attachments, $listing_renderer, $metadata, /*AWPCP_Request*/ $request = null, $settings, $wordpress ) {
        $this->attachments_logic = $attachments_logic;
        $this->attachments = $attachments;
        $this->listing_renderer = $listing_renderer;
        $this->metadata = $metadata;
        $this->settings = $settings;
        $this->wordpress = $wordpress;
        $this->request = $request;

        add_action( 'template_redirect', array( $this, 'dispatch' ) );
    }

    /**
     * @since 3.0.2
     * @deprecated 3.4
     */
    public static function instance() {
        _deprecated_function( __FUNCTION__, '3.4', 'awpcp_listings_api' );
        return awpcp_listings_api();
    }

    /**
     * @since 3.0.2
     * @tested
     */
    public function dispatch() {
        $awpcpx = $this->request->get_query_var( 'awpcpx' );
        $module = $this->request->get_query_var( 'awpcp-module', $this->request->get_query_var( 'module' ) );
        $action = $this->request->get_query_var( 'awpcp-action', $this->request->get_query_var( 'action' ) );

        if ( $awpcpx && $module == 'listings' ) {
            switch ( $action ) {
                case 'verify':
                    $this->handle_email_verification_link();
            }
        }
    }

    /**
     * @since 3.0.2
     */
    public function handle_email_verification_link() {
        $ad_id = $this->request->get_query_var( 'awpcp-ad' );
        $hash = $this->request->get_query_var( 'awpcp-hash' );

        $ad = AWPCP_Ad::find_by_id( $ad_id );

        if ( is_null( $ad ) || ! awpcp_verify_email_verification_hash( $ad_id, $hash ) ) {
            wp_redirect( awpcp_get_main_page_url() );
            return;
        }

        $this->verify_ad( $ad );

        wp_redirect( esc_url_raw( add_query_arg( 'verified', true, url_showad( $ad->ad_id ) ) ) );
        return;
    }

    /**
     * API Methods
     */

    public function create_ad() {}
    public function update_ad() {}

    /**
     * @since 3.0.2
     */
    public function consolidate_new_ad( $ad, $transaction ) {
        do_action( 'awpcp-place-ad', $ad, $transaction );

        $this->metadata->set( $ad->ad_id, 'reviewed', false );

        if ( $ad->verified && ! awpcp_current_user_is_moderator() ) {
            $this->send_ad_posted_email_notifications( $ad, array(), $transaction );
        } else if ( ! $ad->verified ) {
            $this->send_verification_email( $ad );
        }

        if ( ! $ad->verified && ! $ad->disabled ) {
            $ad->disable();
        }

        $transaction->set( 'ad-consolidated-at', awpcp_datetime() );
    }

    /**
     * @since 3.0.2
     */
    public function consolidate_existing_ad( $ad ) {
        // if Ad is enabled and should be disabled, then disable it, otherwise
        // do not alter the Ad disabled status.
        if ( ! $ad->disabled && awpcp_should_disable_existing_listing( $ad ) ) {
            $ad->disable();
            $ad->clear_disabled_date();
        } else if ( $ad->disabled ) {
            $ad->clear_disabled_date();
        }

        if ( $ad->verified && ! awpcp_current_user_is_moderator() ) {
            $this->send_ad_updated_email_notifications( $ad );
        }
    }

    public function update_listing_verified_status( $listing, $transaction ) {
        if ( $listing->verified ) {
            return;
        } else if ( $this->should_mark_listing_as_verified( $listing, $transaction ) ) {
            $listing->verified = true;
            $listing->verified_at = awpcp_datetime();
        } else {
            $listing->verified = false;
            $listing->verified_at = null;
        }
    }

    private function should_mark_listing_as_verified( $listing, $transaction ) {
        if ( ! $this->settings->get_option( 'enable-email-verification' ) ) {
            return true;
        } else if ( is_user_logged_in() ) {
            return true;
        } else if ( $transaction->payment_is_completed() || $transaction->payment_is_pending() ) {
            return true;
        }
        return false;
    }

    /**
     * @since 3.0.2
     * @tested
     */
    public function verify_ad( $ad ) {
        if ( $this->listing_renderer->is_verified( $ad ) ) {
            return;
        }

        $payment_term = $this->listing_renderer->get_payment_term( $ad );
        $payment_status = $this->listing_renderer->get_payment_status( $ad );

        $timestamp = current_time( 'timestamp' );
        $now = current_time( 'mysql' );

        $this->wordpress->delete_post_meta( $ad->ID, '_verification_needed' );
        $this->wordpress->update_post_meta( $ad->ID, '_verification_date', $now );
        $this->wordpress->update_post_meta( $ad->ID, '_start_date', $now );
        $this->wordpress->update_post_meta( $ad->ID, '_end_date', $payment_term->calculate_end_date( $timestamp ) );

        $listing_is_disabled = $this->listing_renderer->is_disabled( $ad );
        $should_enable_listing = awpcp_should_enable_new_listing_with_payment_status( $ad, $payment_status );

        if ( $listing_is_disabled && $should_enable_listing ) {
            $this->enable_listing_without_triggering_actions( $ad );
        }

        if ( ! awpcp_current_user_is_moderator() ) {
            $this->send_ad_posted_email_notifications( $ad );
        }
    }

    /**
     * @since feature/1112
     */
    public function enable_listing( $listing ) {
        $this->enable_listing_without_triggering_actions( $listing );

        do_action( 'awpcp_approve_ad', $listing );

        return true;
    }

    /**
     * @since feature/1112
     */
    public function enable_listing_without_triggering_actions( $listing ) {
        $images_must_be_approved = $this->settings->get_option( 'imagesapprove', false );

        // TODO: this is kind of useles... if images don't need to be approved,
        // they are likely already enabled...
        //
        // Also, why don't we disable images when the
        // listing is disabled?
        if ( ! $images_must_be_approved ) {
            $images = $this->attachments->find_attachments_of_type_awaiting_approval( 'image', array( 'post_parent' => $listing->ID, ) );

            foreach ( $images as $image ) {
                $this->attachments_logic->approve_attachment( $image );
            }
        }

        $this->wordpress->update_post( array( 'ID' => $listing->ID, 'post_status' => 'publish' ) );
        $this->wordpress->delete_post_meta( $listing->ID, '_disabled_date' );
    }

    public function disable_listing( $listing ) {
        $this->disable_listing_without_triggering_actions( $listing );

        do_action( 'awpcp_disable_ad', $listing );

        return true;
    }

    public function disable_listing_without_triggering_actions( $listing ) {
        $this->wordpress->update_post( array( 'ID' => $listing->ID, 'post_status' => 'disabled' ) );
        $this->wordpress->update_post_meta( $listing->ID, '_disabled_date', current_time( 'mysql' ) );
    }

    /**
     * @since 3.0.2
     */
    public function get_ad_alerts( $ad ) {
        $alerts = array();

        if ( ! $ad->verified ) {
            $alerts[] = __( 'You need to verify the email address used as the contact email address for this Ad. The Ad will remain in a disabled status until you verify you address. A verification email has been sent to you.', 'another-wordpress-classifieds-plugin' );
        }

        if ( get_awpcp_option( 'adapprove' ) == 1 && $ad->disabled ) {
            $alerts[] = get_awpcp_option( 'notice_awaiting_approval_ad' );
        }

        if ( get_awpcp_option( 'imagesapprove' ) == 1 ) {
            $alerts[] = __( "If you have uploaded images your images will not show up until an admin has approved them.", 'another-wordpress-classifieds-plugin' );
        }

        return $alerts;
    }

    /**
     * @since 3.0.2
     */
    public function send_ad_posted_email_notifications( $ad, $messages = array(), $transaction = null ) {
        $messages = array_merge( $messages, $this->get_ad_alerts( $ad ) );

        awpcp_send_listing_posted_notification_to_user( $ad, $transaction, join( "\n\n", $messages ) );
        awpcp_send_listing_posted_notification_to_moderators( $ad, $transaction, join( "\n\n", $messages ) );

        $moderate_listings = get_awpcp_option( 'adapprove' );
        $moderate_images = get_awpcp_option('imagesapprove') == 1;

        if ( ( $moderate_listings || $moderate_images ) && $ad->disabled ) {
            awpcp_send_listing_awaiting_approval_notification_to_moderators(
                $ad, $moderate_listings, $moderate_images
            );
        }
    }

    /**
     * @since 3.0.2
     */
    public function send_ad_updated_email_notifications( $ad, $messages = array() ) {
        $messages = array_merge( $messages, $this->get_ad_alerts( $ad ) );

        awpcp_send_listing_updated_notification_to_user( $ad, join( "\n\n", $messages ) );
        awpcp_send_listing_updated_notification_to_moderators( $ad, join( "\n\n", $messages ) );

        $moderate_modifications = get_awpcp_option( 'disable-edited-listings-until-admin-approves' );
        $moderate_images = get_awpcp_option('imagesapprove') == 1;

        if ( ( $moderate_modifications || $moderate_images ) && $ad->disabled ) {
            awpcp_send_listing_awaiting_approval_notification_to_moderators(
                $ad, $moderate_modifications, $moderate_images
            );
        }
    }

    /**
     * @since 3.0.2
     */
    public function send_verification_email( $ad ) {
        $mail = new AWPCP_Email;
        $mail->to[] = awpcp_format_email_address( $ad->ad_contact_email, $ad->ad_contact_name );
        $mail->subject = sprintf( __( 'Verify the email address used for Ad "%s"', 'another-wordpress-classifieds-plugin' ), $ad->get_title() );

        $verification_link = awpcp_get_email_verification_url( $ad->ad_id );

        $template = AWPCP_DIR . '/frontend/templates/email-ad-awaiting-verification.tpl.php';
        $mail->prepare( $template, array(
            'contact_name' => $ad->ad_contact_name,
            'ad_title' => $ad->get_title(),
            'verification_link' => $verification_link
        ) );

        if ( $mail->send() ) {
            $emails_sent = intval( awpcp_get_ad_meta( $ad->ad_id, 'verification_emails_sent', true ) );
            awpcp_update_ad_meta( $ad->ad_id, 'verification_email_sent_at', awpcp_datetime() );
            awpcp_update_ad_meta( $ad->ad_id, 'verification_emails_sent', $emails_sent + 1 );
        }
    }

    /**
     * @since 3.4
     */
    public function flag_listing( $listing ) {
        $listing->flagged = true;

        if ( $result = $listing->save() ) {
            awpcp_send_listing_was_flagged_notification( $listing );
        }

        return $result;
    }

    /**
     * @since 3.4
     */
    public function unflag_listing( $listing ) {
        $listing->flagged = false;
        return $listing->save();
    }

    public function increase_visits_count( $listing ) {
        update_post_meta( $listing->ID, '_views', 1 + get_post_meta( $listing->ID, '_views', true ) );
    }
}
