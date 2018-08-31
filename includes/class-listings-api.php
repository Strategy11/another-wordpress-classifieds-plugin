<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Listings logic.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class AWPCP_ListingsAPI {

    // phpcs:disable Generic,Squiz,WordPress,PSR2,PEAR

    private $attachments_logic;
    private $attachments;
    private $listing_renderer;
    private $listings;
    private $roles;
    private $request = null;
    private $settings = null;
    private $wordpress;
    private $db;

    public function __construct( $attachments_logic, $attachments, $listing_renderer, $listings, $roles, /*AWPCP_Request*/ $request = null, $settings, $wordpress, $db ) {
        $this->attachments_logic = $attachments_logic;
        $this->attachments = $attachments;
        $this->listing_renderer = $listing_renderer;
        $this->listings = $listings;
        $this->roles             = $roles;
        $this->settings = $settings;
        $this->wordpress = $wordpress;
        $this->request = $request;
        $this->db = $db;

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

        try {
            $ad = $this->listings->get( $ad_id );
        } catch ( AWPCP_Exception $e ) {
            $ad = null;
        }

        if ( is_null( $ad ) || ! awpcp_verify_email_verification_hash( $ad_id, $hash ) ) {
            wp_redirect( awpcp_get_main_page_url() );
            return;
        }

        $this->verify_ad( $ad );

        wp_redirect( esc_url_raw( add_query_arg( 'verified', true, url_showad( $ad->ID ) ) ) );
        return;
    }

    /**
     * API Methods
     */

    /**
     * TODO: Do we need to provide default values for start_date and end_date?
     */
    public function create_listing( $listing_data ) {
        $now = current_time( 'mysql' );

        $post_fields = wp_parse_args( $listing_data['post_fields'], array(
            'post_type' => AWPCP_LISTING_POST_TYPE,
            'post_status' => 'disabled',
            'post_date' => $now,
            'post_date_gmt' => get_gmt_from_date( $now ),
        ) );

        $listing_id = $this->wordpress->insert_post( $post_fields , true );

        if ( is_wp_error( $listing_id ) ) {
            $message = __( 'There was an unexpected error trying to save the listing details. Please try again or contact an administrator.', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( $message );
        }

        $listing = $this->listings->get( $listing_id );

        $this->update_listing_terms( $listing, $listing_data['terms'] );
        $this->update_listing_metadata( $listing, $this->get_default_listing_metadata( $listing_data['metadata'] ) );

        return $listing;
    }

    /**
     * @since 4.0.0
     */
    private function update_listing_terms( $listing, $terms ) {
        foreach ( $terms as $taxonomy => $taxonomy_terms ) {
            $this->wordpress->set_object_terms( $listing->ID, $taxonomy_terms, $taxonomy );
        }
    }

    /**
     * @since 4.0.0
     */
    private function update_listing_metadata( $listing, $metadata ) {
        if ( isset( $metadata['_awpcp_start_date'] ) ) {
            $metadata['_awpcp_most_recent_start_date'] = $metadata['_awpcp_start_date'];
        }

        foreach ( $metadata as $field_name => $field_value ) {
            $this->wordpress->update_post_meta( $listing->ID, $field_name, $field_value );
        }
    }

    /**
     * @since 4.0.0
     */
    public function get_default_listing_metadata( $metadata ) {
        $metadata = wp_parse_args( $metadata, array(
            '_awpcp_payment_status'         => 'Unpaid',
            // TODO: Do we need to alter this when someones changes the start date?
            '_awpcp_most_recent_start_date' => current_time( 'mysql' ),
            '_awpcp_renewed_date'           => '',
        ) );

        if ( ! isset( $metadata['_awpcp_access_key'] ) || empty( $metadata['_awpcp_access_key'] ) ) {
            $metadata['_awpcp_access_key'] = $this->generate_access_key();
        }

        return $metadata;

    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function update_listing( $listing, $listing_data ) {
        $listing_data = wp_parse_args( $listing_data, array(
            'post_fields' => array(),
            'terms' => array(),
            'metadata' => array(),
        ) );

        $post_fields = wp_parse_args( $listing_data['post_fields'], array(
            'ID' => $listing->ID,
        ) );

        $listing_id = $this->wordpress->update_post( $post_fields, true );

        if ( is_wp_error( $listing_id ) ) {
            $error_message = $listing_id->get_error_message();

            if ( $error_message ) {
                $message = __( 'There was an error trying to save your listing details:', 'another-wordpress-classifieds-plugin' );
                $message.= '<br/><br/>';
                $message.= $error_message;
            } else {
                $message = __( 'There was an unexpected error trying to save your listing details. Please try again or contact an administrator.', 'another-wordpress-classifieds-plugin' );
            }

            throw new AWPCP_Exception( $message );
        }

        $this->update_listing_terms( $listing, $listing_data['terms'] );
        $this->update_listing_metadata( $listing, $listing_data['metadata'] );

        if ( isset( $listing_data['regions'] ) && isset( $listing_data['regions-allowed'] ) ) {
            awpcp_basic_regions_api()->update_ad_regions( $listing, $listing_data['regions'], $listing_data['regions-allowed'] );
        }

        return $this->listings->get( $listing_id );
    }

    /**
     * @since 3.0.2
     */
    public function consolidate_new_ad( $ad, $transaction ) {
        do_action( 'awpcp-place-ad', $ad, $transaction );

        $this->wordpress->update_post_meta( $ad->ID, '_awpcp_content_needs_review', true );

        $is_listing_verified = $this->listing_renderer->is_verified( $ad );

        if ( $is_listing_verified && ! awpcp_current_user_is_moderator() ) {
            $this->send_ad_posted_email_notifications( $ad, array(), $transaction );
        } else if ( ! $is_listing_verified ) {
            $this->send_verification_email( $ad );
        }

        if ( ! $is_listing_verified && $this->listing_renderer->is_public( $ad ) ) {
            $this->disable_listing( $ad );
        }

        $transaction->set( 'ad-consolidated-at', current_time( 'mysql' ) );
    }

    /**
     * @since 3.0.2
     */
    public function consolidate_existing_ad( $ad ) {
		$should_disable_listing = awpcp_should_disable_existing_listing( $ad );

        // if Ad is enabled and should be disabled, then disable it, otherwise
        // do not alter the Ad disabled status.
        if ( $should_disable_listing && $this->listing_renderer->is_public( $ad )  ) {
            $this->disable_listing( $ad );
            $this->wordpress->delete_post_meta( $ad->ID, '_awpcp_disabled_date' );
        } else if ( $should_disable_listing ) {
            $this->wordpress->delete_post_meta( $ad->ID, '_awpcp_disabled_date' );
        }

        $is_listing_verified = $this->listing_renderer->is_verified( $ad );

        if ( $is_listing_verified && ! awpcp_current_user_is_moderator() ) {
            $this->send_ad_updated_email_notifications( $ad );
        }
    }

    /**
     */
    public function update_listing_verified_status( $listing, $transaction ) {
        if ( $this->listing_renderer->is_verified( $listing ) ) {
            return;
        }

        if ( $this->should_mark_listing_as_verified( $listing, $transaction ) ) {
            return $this->mark_listing_as_verified( $listing );
        }

        $this->mark_listing_as_needs_verification( $listing );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
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

    private function mark_listing_as_verified( $listing ) {
        $this->wordpress->delete_post_meta( $listing->ID, '_awpcp_verification_needed' );
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_verified', true );
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_verification_date', current_time( 'mysql' ) );
    }

    private function mark_listing_as_needs_verification( $listing ) {
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_verification_needed', true );
        $this->wordpress->delete_post_meta( $listing->ID, '_awpcp_verified' );
        $this->wordpress->delete_post_meta( $listing->ID, '_awpcp_verification_date' );
    }

    /**
     * @since 3.0.2
     * @tested
     */
    public function verify_ad( $ad ) {
        if ( $this->listing_renderer->is_verified( $ad ) ) {
            return;
        }

        $payment_term   = $this->listing_renderer->get_payment_term( $ad );
        $payment_status = $this->listing_renderer->get_payment_status( $ad );

        $timestamp = current_time( 'timestamp' );
        $now       = current_time( 'mysql' );

        $this->mark_listing_as_verified( $ad );

        $this->wordpress->update_post_meta( $ad->ID, '_awpcp_start_date', $now );
        $this->wordpress->update_post_meta( $ad->ID, '_awpcp_end_date', $payment_term->calculate_end_date( $timestamp ) );

        $this->set_new_listing_post_status( $ad, $payment_status, true );

        if ( ! awpcp_current_user_is_moderator() ) {
            $this->send_ad_posted_email_notifications( $ad );
        }
    }

    /**
     * @since 4.0.0
     */
    public function get_modified_listing_post_status( $listing ) {
        if ( $this->roles->current_user_is_moderator() ) {
            return $listing->post_status;
        }

        if ( $this->settings->get_option( 'disable-edited-listings-until-admin-approves' ) ) {
            return 'pending';
        }

        return $listing->post_status;
    }

    /**
     * @since 4.0.0
     */
    public function set_new_listing_post_status( $listing, $payment_status, $trigger_actions ) {
        if ( $this->roles->current_user_is_moderator() ) {
            return $this->enable_listing_maybe_triggering_actions( $listing, $trigger_actions );
        }

        if ( $this->settings->get_option( 'adapprove' ) ) {
            return $this->mark_listing_as_awating_approval( $listing );
        }

        if ( $payment_status == AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING && ! $this->settings->get_option( 'enable-ads-pending-payment' ) ) {
            return $this->mark_listing_as_awating_approval( $listing );
        }

        return $this->enable_listing_maybe_triggering_actions( $listing, $trigger_actions );
    }

    /**
     * @since 4.0.0
     */
    public function set_modified_listing_post_status( $listing ) {
        $post_status = $this->get_modified_listing_post_status( $listing );

        if ( $post_status === $listing->post_status ) {
            return true;
        }

        if ( 'pending' === $post_status ) {
            return $this->mark_listing_as_awating_approval( $listing );
        }

        if ( 'publish' === $post_status ) {
            return $this->enable_listing( $listing );
        }

        return true;
    }

    /**
     * @since 4.0.0
     */
    private function enable_listing_maybe_triggering_actions( $listing, $trigger_actions ) {
        if ( $trigger_actions ) {
            return $this->enable_listing( $listing );
        }

        return $this->enable_listing_without_triggering_actions( $listing );
    }

    /**
     * @since 4.0.0
     */
    public function mark_listing_as_awating_approval( $listing ) {
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_awaiting_approval', true );

        $listing->post_status = 'pending';

        $post_data = [
            'ID'          => $listing->ID,
            'post_status' => $listing->post_status,
        ];

        return $this->wordpress->update_post( $post_data );
    }

    /**
     * @since 4.0.0
     */
    public function mark_as_having_images_awaiting_approval( $listing ) {
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_has_images_awaiting_approval', true );
    }

    /**
     * @since 4.0.0
     */
    public function remove_having_images_awaiting_approval_mark( $listing ) {
        return $this->wordpress->delete_post_meta( $listing->ID, '_awpcp_has_images_awaiting_approval' );
    }

    /**
     * @since 4.0.0
     */
    public function enable_listing( $listing ) {
        if ( $this->enable_listing_without_triggering_actions( $listing ) ) {
            do_action( 'awpcp_approve_ad', $listing );
            return true;
        }

        return false;
    }

    /**
     * @since 4.0.0
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

        $listing->post_status = 'publish';

        $this->wordpress->update_post( array( 'ID' => $listing->ID, 'post_status' => $listing->post_status ) );
        $this->wordpress->delete_post_meta( $listing->ID, '_awpcp_disabled_date' );

        return true;
    }

    public function disable_listing( $listing ) {
        $this->disable_listing_without_triggering_actions( $listing );

        do_action( 'awpcp_disable_ad', $listing );

        return true;
    }

    public function disable_listing_without_triggering_actions( $listing ) {
        $listing->post_status = 'disabled';

        $this->wordpress->update_post( array( 'ID' => $listing->ID, 'post_status' => $listing->post_status ) );
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_disabled_date', current_time( 'mysql' ) );
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function renew_listing( $listing, $end_date = false ) {
        if ( $end_date === false ) {
            // if the Ad's end date is in the future, use that as starting point
            // for the new end date, else use current date.
            $end_date = awpcp_datetime( 'timestamp', $this->listing_renderer->get_plain_end_date( $listing ) );
            $now = current_time( 'timestamp' );
            $start_date = $end_date > $now ? $end_date : $now;

            $payment_term = $this->listing_renderer->get_payment_term( $listing );

            $this->wordpress->update_post_meta( $listing->ID, '_awpcp_end_date', $payment_term->calculate_end_date( $start_date ) );
        } else {
            $this->wordpress->update_post_meta( $listing->ID, '_awpcp_end_date', $end_date );
        }

        $this->wordpress->delete_post_meta( $listing->ID, '_awpcp_renew_email_sent' );
        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_renewed_date', current_time( 'mysql' ) );

        if ( ! $this->listing_renderer->is_public( $listing ) ) {
            $this->enable_listing( $listing );
        }

        return true;
    }

    /**
     * @since 4.0.0
     */
    public function generate_access_key() {
        return md5( sprintf( '%s%s%d', wp_salt(), uniqid( '', true ), rand( 1, 1000 ) ) );
    }

    /**
     * @since 3.0.2
     */
    public function get_ad_alerts( $ad ) {
        $alerts = array();

        if ( ! $this->listing_renderer->is_verified( $ad ) ) {
            $alerts[] = __( 'You need to verify the email address used as the contact email address for this Ad. The Ad will remain in a disabled status until you verify your address. A verification email has been sent to you.', 'another-wordpress-classifieds-plugin' );
        }

        if ( get_awpcp_option( 'adapprove' ) == 1 && $this->listing_renderer->is_disabled( $ad ) ) {
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

        if ( ( $moderate_listings || $moderate_images ) && $this->listing_renderer->is_disabled( $ad ) ) {
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

        if ( ( $moderate_modifications || $moderate_images ) && $this->listing_renderer->is_disabled( $ad ) ) {
            awpcp_send_listing_awaiting_approval_notification_to_moderators(
                $ad, $moderate_modifications, $moderate_images
            );
        }
    }

    /**
     * @since 3.0.2
     */
    public function send_verification_email( $ad ) {
        $contact_email = $this->listing_renderer->get_contact_email( $ad );
        $contact_name = $this->listing_renderer->get_contact_name( $ad );
        $listing_title = $this->listing_renderer->get_listing_title( $ad );

        $replacement = [
            'listing_title'     => $listing_title,
            'author_name'       => $contact_name,
            'verification_link' => awpcp_get_email_verification_url( $ad->ID ),
            'website_title'     => awpcp_get_blog_name(),
            'website_url'       => home_url(),
        ];

        $email = awpcp()->container['EmailHelper']->prepare_email_from_template_setting( 'verify-email-message-email-template', $replacement );

        $email->to[] = awpcp_format_recipient_address( $contact_email, $contact_name );

        if ( $email->send() ) {
            $emails_sent = intval( $this->wordpress->get_post_meta( $ad->ID, '_awpcp_verification_emails_sent', 1 ) );
            $this->wordpress->update_post_meta( $ad->ID, '_awpcp_verification_email_sent_at', current_time( 'mysql' ) );
            $this->wordpress->update_post_meta( $ad->ID, '_awpcp_verification_emails_sent', $emails_sent + 1 );
        }
    }

    // phpcs:enable Generic,Squiz,WordPress,PSR2,PEAR

    /**
     * @param object $listing   An instance of WP_Post.
     * @since 3.4
     */
    public function flag_listing( $listing ) {
        $meta_updated = (bool) $this->wordpress->update_post_meta( $listing->ID, '_awpcp_flagged', true );

        if ( $meta_updated ) {
            awpcp_send_listing_was_flagged_notification( $listing );
        }

        return $meta_updated;
    }

    /**
     * @param object $listing   An instance of WP_Post.
     * @since 3.4
     */
    public function unflag_listing( $listing ) {
        return (bool) $this->wordpress->delete_post_meta( $listing->ID, '_awpcp_flagged' );
    }

    // phpcs:disable Generic,Squiz,WordPress,PSR2,PEAR

    public function increase_visits_count( $listing ) {
        $number_of_visits = absint( get_post_meta( $listing->ID, '_awpcp_views', true ) );

        return update_post_meta( $listing->ID, '_awpcp_views', 1 + $number_of_visits );
    }

    /**
     * @since 4.0.0
     */
    public function delete_listing( $listing ) {
        do_action( 'awpcp_before_delete_ad', $listing );

        $attachments = $this->attachments->find_attachments( array( 'post_parent' => $listing->ID ) );

        foreach ( $attachments as $attachment ) {
            $this->attachments_logic->delete_attachment( $attachment );
        }

        $sql = 'DELETE FROM ' . AWPCP_TABLE_AD_REGIONS . ' WHERE ad_id = %d';
        $result = $this->db->query( $this->db->prepare( $sql, $listing->ID ) );

        $this->wordpress->delete_post( $listing->ID, true );

        do_action( 'awpcp_delete_ad', $listing );

        return $result === false ? false : true;
    }

    // phpcs:enable Generic,Squiz,WordPress,PSR2,PEAR

    /**
     * @since 4.0.0
     */
    public function can_payment_information_be_modified_during_submit( $listing ) {
        $payment_term = $this->listing_renderer->get_payment_term( $listing );

        if ( is_null( $payment_term ) ) {
            return true;
        }

        $payment_status = $this->listing_renderer->get_payment_status( $listing );

        if ( empty( $payment_status ) || 'Unpaid' === $payment_status ) {
            return true;
        }

        return false;
    }

    /**
     * @since 4.0.0
     */
    public function update_listing_payment_term( $listing, $payment_term ) {
        $start_date = $this->listing_renderer->get_plain_start_date( $listing );
        $end_date   = $this->listing_renderer->get_plain_end_date( $listing );

        if ( empty( $start_date ) ) {
            $start_date = current_time( 'mysql' );
        }

        if ( empty( $end_date ) ) {
            $end_date = $payment_term->calculate_end_date( awpcp_datetime( 'timestamp', $start_date ) );
        }

        $post_data = [
            'metadata' => [
                '_awpcp_payment_term_type' => $payment_term->type,
                '_awpcp_payment_term_id'   => $payment_term->id,
                '_awpcp_is_featured'       => $payment_term->featured,
                '_awpcp_start_date'        => $start_date,
                '_awpcp_end_date'          => $end_date,
            ],
        ];

        return $this->update_listing( $listing, $post_data );
    }
}
