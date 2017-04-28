<?php

function awpcp_send_listing_posted_notification_to_user( $listing, $transaction, $message ) {
    if ( get_awpcp_option( 'send-user-ad-posted-notification' ) ) {
        $user_message = awpcp_ad_posted_user_email( $listing, $transaction, $message );
        $response = $user_message->send();
    } else {
        $response = false;
    }

    return $response;
}

function awpcp_send_listing_posted_notification_to_moderators( $listing, $transaction, $messages ) {
    $send_notification_to_administrators = get_awpcp_option( 'notifyofadposted' );
    $send_notification_to_moderators = get_awpcp_option( 'send-listing-posted-notification-to-moderators' );

    if ( $send_notification_to_administrators && $send_notification_to_moderators ) {
        $email_recipients = array_merge( array( awpcp_admin_email_to() ), awpcp_moderators_email_to() );
    } else if ( $send_notification_to_administrators ) {
        $email_recipients = array( awpcp_admin_email_to() );
    } else if ( $send_notification_to_moderators ) {
        $email_recipients = awpcp_moderators_email_to();
    } else {
        return false;
    }

    $user_message = awpcp_ad_posted_user_email( $listing, $transaction, $messages );
    $content = $user_message->body;

    $admin_message = new AWPCP_Email;
    $admin_message->to = $email_recipients;
    $admin_message->subject = __( 'New classified listing created', 'another-wordpress-classifieds-plugin' );

    $params = array( 'action' => 'view', 'id' => $listing->ID );
    $url = add_query_arg( urlencode_deep( $params ), awpcp_get_admin_listings_url() );

    $template = AWPCP_DIR . '/frontend/templates/email-place-ad-success-admin.tpl.php';
    $admin_message->prepare($template, compact('content', 'url'));

    $message_sent = $admin_message->send();

    return $message_sent;
}

function awpcp_send_listing_updated_notification_to_user( $listing, $messages ) {
    if ( get_awpcp_option( 'send-user-ad-posted-notification' ) ) {
        $user_mesage = awpcp_ad_updated_user_email( $listing, $messages );
        $response = $user_mesage->send();
    } else {
        $response = false;
    }

    return $response;
}

function awpcp_send_listing_updated_notification_to_moderators( $listing, $messages ) {
    $send_notification_to_administrators = get_awpcp_option( 'send-listing-updated-notification-to-administrators' );
    $send_notification_to_moderators = get_awpcp_option( 'send-listing-updated-notification-to-moderators' );

    if ( $send_notification_to_administrators && $send_notification_to_moderators ) {
        $email_recipients = array_merge( array( awpcp_admin_email_to() ), awpcp_moderators_email_to() );
    } else if ( $send_notification_to_administrators ) {
        $email_recipients = array( awpcp_admin_email_to() );
    } else if ( $send_notification_to_moderators ) {
        $email_recipients = awpcp_moderators_email_to();
    } else {
        return false;
    }

    $listing_title = awpcp_listing_renderer()->get_listing_title( $listing );

    $subject = __( 'Listing "%s" was updated', 'another-wordpress-classifieds-plugin' );
    $subject = sprintf( $subject, $listing_title );

    $user_message = awpcp_ad_updated_user_email( $listing, $messages );
    $content = $user_message->body;

    $admin_message = new AWPCP_Email;
    $admin_message->to = $email_recipients;
    $admin_message->subject = $subject;

    $params = array( 'action' => 'view', 'id' => $listing->ID );
    $manage_listing_url = add_query_arg( urlencode_deep( $params ), awpcp_get_admin_listings_url() );

    $template = AWPCP_DIR . '/templates/email/listing-updated-nofitication-moderators.plain.tpl.php';
    $admin_message->prepare( $template, compact( 'listing_title', 'manage_listing_url', 'content' ) );

    $message_sent = $admin_message->send();

    return $message_sent;
}

function awpcp_listing_updated_user_message( $listing, $messages ) {
    $admin_email = awpcp_admin_recipient_email_address();

    $payments_api = awpcp_payments_api();
    $show_total_amount = $payments_api->payments_enabled();
    $show_total_credits = $payments_api->credit_system_enabled();
    $currency_code = awpcp_get_currency_code();
    $blog_name = awpcp_get_blog_name();

    if ( ! is_null( $transaction ) ) {
        $transaction_totals = $transaction->get_totals();
        $total_amount = $transaction_totals['money'];
        $total_credits = $transaction_totals['credits'];
    } else {
        $total_amount = 0;
        $total_credits = 0;
    }

    if ( get_awpcp_option( 'requireuserregistration' ) ) {
        $include_listing_access_key = false;
        $include_edit_listing_url = true;
    } else {
        $include_listing_access_key = get_awpcp_option( 'include-ad-access-key' );
        $include_edit_listing_url = false;
    }

    $params = compact(
        'ad',
        'admin_email',
        'transaction',
        'currency_code',
        'show_total_amount',
        'show_total_credits',
        'include_listing_access_key',
        'include_edit_listing_url',
        'total_amount',
        'total_credits',
        'message',
        'blog_name'
    );

    $listing_renderer = awpcp_listing_renderer();
    $contact_name = $listing_renderer->get_contact_name( $ad );
    $contact_email = $listing_renderer->get_contact_email( $ad );

    $email = new AWPCP_Email;
    $email->to[] = awpcp_format_recipient_address( $contact_email, $contact_name );
    $email->subject = get_awpcp_option('listingaddedsubject');
    $email->prepare( AWPCP_DIR . '/frontend/templates/email-place-ad-success-user.tpl.php', $params );

    return $email;
}

function awpcp_send_listing_awaiting_approval_notification_to_moderators(
        $listing, $moderate_listings, $moderate_images ) {

    $email_recipients = awpcp_get_recipients_for_listing_awaiting_approval_notification();

    if ( empty( $email_recipients ) ) {
        return false;
    }

    $content = awpcp_get_messages_for_listing_awaiting_approval_notification( $listing, $moderate_listings, $moderate_images );
    $messages = $content['messages'];

    $mail = new AWPCP_Email;
    $mail->to = $email_recipients;
    $mail->subject = $content['subject'];
    $template = AWPCP_DIR . '/frontend/templates/email-ad-awaiting-approval-admin.tpl.php';
    $mail->prepare( $template, compact( 'messages' ) );

    return $mail->send();
}

/**
 * @since 3.4
 */
function awpcp_get_recipients_for_listing_awaiting_approval_notification() {
    $send_notification_to_administrators = get_awpcp_option( 'send-listing-awaiting-approval-notification-to-administrators' );
    $send_notification_to_moderators = get_awpcp_option( 'send-listing-awaiting-approval-notification-to-moderators' );

    if ( $send_notification_to_administrators && $send_notification_to_moderators ) {
        $email_recipients = array_merge( array( awpcp_admin_email_to() ), awpcp_moderators_email_to() );
    } else if ( $send_notification_to_administrators ) {
        $email_recipients = array( awpcp_admin_email_to() );
    } else if ( $send_notification_to_moderators ) {
        $email_recipients = awpcp_moderators_email_to();
    } else {
        $email_recipients = array();
    }

    return $email_recipients;
}

function awpcp_get_messages_for_listing_awaiting_approval_notification( $listing, $moderate_listings, $moderate_images ) {
    $listing_renderer = awpcp_listing_renderer();

    $params = array( 'action' => 'manage-images', 'id' => $listing->ID );
    $manage_images_url = add_query_arg( urlencode_deep( $params ), awpcp_get_admin_listings_url() );

    if ( $moderate_images && ! $moderate_listings ) {
        $subject = __( 'Images on listing "%s" are awaiting approval', 'another-wordpress-classifieds-plugin' );

        $message = __( 'Images on Ad "%s" are awaiting approval. You can approve the images going to the Manage Images section for that Ad and clicking the "Enable" button below each image. Click here to continue: %s.', 'another-wordpress-classifieds-plugin');
        $messages = array( sprintf( $message, $listing_renderer->get_listing_title( $listing ), $manage_images_url ) );
    } else {
        $subject = __( 'Listing "%s" is awaiting approval', 'another-wordpress-classifieds-plugin' );

        $message = __('The Ad "%s" is awaiting approval. You can approve the Ad going to the Manage Listings section and clicking the "Enable" action shown on top. Click here to continue: %s.', 'another-wordpress-classifieds-plugin');
        $params = array( 'action' => 'view', 'id' => $listing->ID );
        $url = add_query_arg( urlencode_deep( $params ), awpcp_get_admin_listings_url() );

        $messages[] = sprintf( $message, $listing_renderer->get_listing_title( $listing ), $url );

        if ( $moderate_images ) {
            $message = __( 'Additionally, You can approve the images going to the Manage Images section for that Ad and clicking the "Enable" button below each image. Click here to continue: %s.', 'another-wordpress-classifieds-plugin' );
            $messages[] = sprintf( $message, $manage_images_url );
        }
    }

    $subject = sprintf( $subject, $listing_renderer->get_listing_title( $listing ) );

    return array( 'subject' => $subject, 'messages' => $messages );
}

/**
 * TODO: write tests for this function.
 * @since 3.4
 */
function awpcp_send_listing_was_flagged_notification( $listing ) {
    $listing_renderer = awpcp_listing_renderer();

    if ( ! get_awpcp_option( 'send-listing-flagged-notification-to-administrators' ) ) {
        return false;
    }

    $query_args = array( 'filterby' => 'flagged', 'filter' => 1 );
    $flagged_listings_url = add_query_arg( $query_args, awpcp_get_admin_listings_url() );

    $params = array(
        'site_name' => get_bloginfo( 'name' ),
        'flagged_listings_url' => $flagged_listings_url,
    );

    $template = AWPCP_DIR . '/templates/email/listing-was-flagged.plain.tpl.php';

    $mail = new AWPCP_Email;
    $mail->to = awpcp_admin_email_to();
    $mail->subject = str_replace(
        '<listing-title>',
        $listing_renderer->get_listing_title( $listing ),
        __( 'Listing <listing-title> was flagged', 'another-wordpress-classifieds-plugin' )
    );

    $mail->prepare( $template, $params );

    return $mail->send();
}
