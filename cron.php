<?php

// ensure we get the expiration hooks scheduled properly:
function awpcp_schedule_activation() {
    $cron_jobs = array(
        'doadexpirations_hook' => 'hourly',
        'doadcleanup_hook' => 'daily',
        'awpcp_ad_renewal_email_hook' => 'hourly',
        'awpcp-clean-up-payment-transactions' => 'daily',
        'awpcp-clean-up-non-verified-ads' => 'daily',
        'awpcp-task-queue-cron' => 'hourly',
        'awpcp-check-license-status' => 'daily',
    );

    foreach ( $cron_jobs as $cron_job => $frequency ) {
        if ( ! wp_next_scheduled( $cron_job ) ) {
            wp_schedule_event( time(), $frequency, $cron_job );
        }
    }

    add_action('doadexpirations_hook', 'doadexpirations');
    add_action('doadcleanup_hook', 'doadcleanup');
    add_action('awpcp_ad_renewal_email_hook', 'awpcp_ad_renewal_email');
    add_action('awpcp-clean-up-payment-transactions', 'awpcp_clean_up_payment_transactions');
    add_action( 'awpcp-clean-up-payment-transactions', 'awpcp_clean_up_non_verified_ads_handler' );
    add_action( 'awpcp-check-license-status', 'awpcp_check_license_status' );

    // if ( awpcp_current_user_is_admin() ) {
    //     wp_clear_scheduled_hook( 'doadexpirations_hook' );
    //     wp_clear_scheduled_hook( 'doadcleanup_hook' );
    //     wp_clear_scheduled_hook( 'awpcp_ad_renewal_email_hook' );
    //     wp_clear_scheduled_hook( 'awpcp-clean-up-payment-transactions' );
    //     wp_clear_scheduled_hook( 'awpcp-clean-up-non-verified-ads' );

    //     wp_schedule_event( time() + 10, 'hourly', 'doadexpirations_hook' );
    //     wp_schedule_event( time() + 10, 'daily', 'doadcleanup_hook' );
    //     wp_schedule_event( time() + 10, 'daily', 'awpcp_ad_renewal_email_hook' );
    //     wp_schedule_event( time() + 10, 'daily', 'awpcp-clean-up-payment-transactions' );
    //     wp_schedule_event( time() + 10, 'daily', 'awpcp-clean-up-non-verified-ads' );

    //     debugp(
    //         'System date is: ' . date('d-m-Y H:i:s'),
    //         'Ad Expiration: ' . date('d-m-Y H:i:s', wp_next_scheduled('doadexpirations_hook')),
    //         'Ad Cleanup: ' . date('d-m-Y H:i:s', wp_next_scheduled('doadcleanup_hook')),
    //         'Ad Renewal Email: ' . date('d-m-Y H:i:s', wp_next_scheduled('awpcp_ad_renewal_email_hook')),
    //         'Payment transactions: ' . date('d-m-Y H:i:s', wp_next_scheduled('awpcp-clean-up-payment-transactions')),
    //         'Unverified Ads: ' . date('d-m-Y H:i:s', wp_next_scheduled('awpcp-clean-up-non-verified-ads'))
    //     );
    // }
}

/**
 * @since 3.6.6
 */
function awpcp_check_license_status() {
    $license_status_check = get_site_transient( 'awpcp-license-status-check' );

    if ( ! empty( $license_status_check ) ) {
        return;
    }

    $licenses_manager = awpcp_licenses_manager();

    foreach ( awpcp_modules_manager()->get_modules() as $module ) {
        $licenses_manager->check_license_status( $module->name, $module->slug );
    }

    set_site_transient( 'awpcp-license-status-check', current_time( 'mysql' ), WEEK_IN_SECONDS );
}

/*
 * Function to disable ads run hourly
 */
function doadexpirations() {
    global $wpdb, $nameofsite;

    $listings_logic = awpcp_listings_api();

    $notify_admin = get_awpcp_option('notifyofadexpired');
    $notify_expiring = get_awpcp_option('notifyofadexpiring');
    // disable the ads or delete the ads?
    // 1 = disable, 0 = delete
    $disable_ads = get_awpcp_option('autoexpiredisabledelete');

    // allow users to use %s placeholder for the website name in the subject line
    $subject = get_awpcp_option('adexpiredsubjectline');
    $subject = sprintf($subject, $nameofsite);
    $bodybase = get_awpcp_option('adexpiredbodymessage');

    $ads = awpcp_listings_collection()->find_valid_listings(array(
        'meta_query' => array(
            array(
                'key' => '_awpcp_end_date',
                'value' => current_time( 'mysql' ),
                'compare' => '<=',
                'type' => 'DATETIME',
            ),
        ),
    ));

    foreach ($ads as $ad) {
        $listings_logic->disable_listing( $ad );

        if ( $notify_expiring == false && $notify_admin == false ) {
            continue;
        }

        $adtitle = get_adtitle( $ad->ID );
        $adstartdate = date( "D M j Y G:i:s", strtotime( get_adstartdate( $ad->ID ) ) );

        $body = $bodybase;
        $body.= "\n\n";
        $body.= __( "Listing Details", 'another-wordpress-classifieds-plugin' );
        $body.= "\n\n";
        $body.= __( "Ad Title:", 'another-wordpress-classifieds-plugin' );
        $body.= " $adtitle";
        $body.= "\n\n";
        $body.= __( "Posted:", 'another-wordpress-classifieds-plugin' );
        $body.= " $adstartdate";
        $body.= "\n\n";

        $body.= __( "Renew your ad by visiting:", 'another-wordpress-classifieds-plugin' );
        $body.= " " . urldecode( awpcp_get_renew_ad_url( $ad->ID ) );
        $body.= "\n\n";

        if ( $notify_expiring ) {
            $user_email = awpcp_format_recipient_address( get_adposteremail( $ad->ad_id ) );
            if ( ! empty( $user_email ) ) {
                $email = new AWPCP_Email();

                $email->to = $user_email;
                $email->from = awpcp_admin_email_from();
                $email->subject = $subject;
                $email->body = $body;

                $email->send();
            }
        }

        if ( $notify_admin ) {
            $email = new AWPCP_Email();

            $email->to = awpcp_admin_email_to();
            $email->from = awpcp_admin_email_from();
            $email->subject = $subject;
            $email->body = $body;

            $email->send();
        }
    }
}

/*
 * Function run once per month to cleanup disabled / deleted ads.
 */
function doadcleanup() {
    $listings_logic = awpcp_listings_api();
    $listings = awpcp_listings_collection();

    $should_delete_expired_listings = get_awpcp_option('autoexpiredisabledelete') != 1;

    // also, get Ads that were disabled more than a week ago, but only if the
    // 'disable instead of delete' flag is not set.
    if (get_awpcp_option('autoexpiredisabledelete') != 1) {
        $days_before_expired_listings_are_deleted = get_awpcp_option( 'days-before-expired-listings-are-deleted' );
        $sql = "(disabled=1 AND (disabled_date + INTERVAL %d DAY) < CURDATE())";

        $conditions[] = sprintf( $sql, $days_before_expired_listings_are_deleted );
    }

    awpcp_delete_unpaid_listings_older_than_a_month( $listings_logic, $listings );
}

/**
 * @access private
 * @since feature/1112
 */
function awpcp_delete_unpaid_listings_older_than_a_month( $listings_logic, $listings ) {
    $query = array(
        'meta_query' => array(
            array(
                'key' => '_awpcp_payment_status',
                'value' => 'Unpaid',
                'compare' => '=',
            ),
        ),
        'date_query' => array(
            array(
                'column' => 'post_date_gmt',
                'before' => '30 days ago',
            ),
        ),
    );

    foreach ( $listings->find_listings( $query ) as $listing ) {
        $listings_logic->delete_listing( $listing );
    }
}

/**
 * @access private
 * @since feature/1112
 */
function awpcp_delete_expired_listings( $listings_logic, $listings ) {
    $days_before_expired_listings_are_deleted = get_awpcp_option( 'days-before-expired-listings-are-deleted' );
    $timestamp =  strtotime( "$days_before_expired_listings_are_deleted days ago" );

    $query = array(
        'post_status' => 'disabled',
        'meta_query' => array(
            array(
                'key' => '_awpcp_disabled_date',
                'value' => awpcp_datetime( 'mysql', $timestamp ),
                'compare' => '<=',
                'type' => 'DATETIME',
            ),
        ),
    );

    foreach ( $listings->find_listings( $query ) as $listing ) {
        $listings_logic->delete_listing( $listing );
    }
}


/**
 * Check if any Ad is about to expire and send an email to the poster.
 *
 * This functions runs daily.
 */
function awpcp_ad_renewal_email() {
    $listing_renderer = awpcp_listing_renderer();

	if (!(get_awpcp_option('sent-ad-renew-email') == 1)) {
		return;
	}

    $notification = awpcp_listing_is_about_to_expire_notification();
    $admin_sender_email = awpcp_admin_email_from();

	foreach ( awpcp_listings_collection()->find_listings_about_to_expire() as $listing ) {
        // When the user clicks the renew ad link, AWPCP uses
        // the is_about_to_expire() method to decide if the Ad
        // can be renewed. We double check here to make
        // sure users can use the link in the email immediately.
        if ( ! $listing_renderer->is_about_to_expire( $listing ) ) {
            continue;
        }

        $email = new AWPCP_Email();

        $email->from = $admin_sender_email;
        $email->to = awpcp_format_recipient_address( $listing_renderer->get_contact_email( $listing ) );
        $email->subject = $notification->render_subject( $listing );
        $email->body = $notification->render_body( $listing );

		if ( $email->send() ) {
			$listing->renew_email_sent = true;
			$listing->save();
		}
	}
}

function awpcp_calculate_end_of_renew_email_date_range_from_now() {
    $threshold = intval( get_awpcp_option( 'ad-renew-email-threshold' ) );
    $target_date = strtotime( "+ $threshold days", current_time( 'timestamp' ) );

    return $target_date;
}


/**
 * Remove incomplete payment transactions
 */
function awpcp_clean_up_payment_transactions() {
    $threshold = awpcp_datetime( 'mysql', current_time( 'timestamp' ) - 24 * 60 * 60 );

    $transactions = AWPCP_Payment_Transaction::query(array(
        'status' => array(
            AWPCP_Payment_Transaction::STATUS_NEW,
            AWPCP_Payment_Transaction::STATUS_OPEN,
        ),
        'created' => array('<', $threshold),
    ));

    foreach ($transactions as $transaction) {
        $transaction->delete();
    }
}

/**
 * @since 3.3
 */
function awpcp_clean_up_non_verified_ads_handler() {
    return awpcp_clean_up_non_verified_ads(
        awpcp_listings_collection(),
        awpcp_listings_api(),
        awpcp()->settings,
        awpcp_wordpress()
    );
}

/**
 * @since feature/1112  Updated to load listings using Listings Collection methods.
 * @since 3.0.2
 */
function awpcp_clean_up_non_verified_ads( $listings_collection, /* AWPCP_ListingsAPI */ $listings, $settings, $wordpress ) {
    if ( ! $settings->get_option( 'enable-email-verification' ) ) {
        return;
    }

    $resend_email_threshold = $settings->get_option( 'email-verification-first-threshold' );
    $delete_ads_threshold = $settings->get_option( 'email-verification-second-threshold' );

    // delete Ads that have been in a non-verified state for more than M days
    $results = $listings_collection->find_listings_awaiting_verification( array(
        'date_query' => array(
            'relation' => 'AND',
            array(
                'before' => awpcp_datetime( 'mysql', current_time( 'timestamp' ) - $delete_ads_threshold * DAY_IN_SECONDS ),
            ),
        ),
    ) );

    foreach ( $results as $listing ) {
        $listings->delete_listing( $listing );
    }

    // re-send verificaiton email for Ads that have been in a non-verified state for more than N days
    $results = $listings_collection->find_listings_awaiting_verification( array(
        'date_query' => array(
            'relation' => 'AND',
            array(
                'before' => awpcp_datetime( 'mysql', current_time( 'timestamp' ) - $resend_email_threshold * DAY_IN_SECONDS ),
            ),
        ),
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => '_awpcp_verification_emails_sent',
                'value' => 1,
                'compare' => '<=',
                'type' => 'UNSIGNED'
            ),
        ),
    ) );

    foreach ( $results as $listing ) {
        $emails_sent = intval( $wordpress->get_post_meta( $listing->ID, '_awpcp_verification_emails_sent', 1 ) );

        if ( $emails_sent >= 2 ) {
            continue;
        }

        $listings->send_verification_email( $listing );
    }
}
