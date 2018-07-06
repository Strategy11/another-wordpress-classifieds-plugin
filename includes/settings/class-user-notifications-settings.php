<?php
/**
 * @package AWPCP\Settings
 */

/**
 * Constructor functions.
 */
function awpcp_user_notifications_settings() {
    return new AWPCP_UserNotificationsSettings();
}

/**
 * Register settings for notifications.
 */
class AWPCP_UserNotificationsSettings {

    /**
     * Handler for awpcp_register_settings action.
     */
    public function register_settings( $settings_manager ) {
        $this->register_subscriber_notifications_settings( $settings_manager );
        $this->register_moderator_notifications_settings( $settings_manager );
        $this->register_administrator_notifications_settings( $settings_manager );
    }

    /**
     * Register settins for subscriber notifications.
     */
    private function register_subscriber_notifications_settings( $settings_manager ) {
        $group = 'listings-settings';
        $key   = 'user-notifications';

        $settings_manager->add_settings_subgroup( [
            'id'       => 'listings-settings',
            'name'     => __( 'Classifieds', 'another-wordpress-classifieds-plugin' ),
            'priority' => 10,
            'parent'   => 'listings-settings',
        ] );

        $settings_manager->add_section( 'listings-settings', __( 'User Notifications', 'another-wordpress-classifieds-plugin' ), 'user-notifications', 3, array( $settings, 'section' ) );

        $settings_manager->add_setting(
            $key,
            'send-user-ad-posted-notification',
            __( 'Listing Created', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            1,
            __( 'An email will be sent when a listing is created.', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting( $key, 'send-ad-enabled-email', __( 'Listing Enabled', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( 'Notify Ad owner when the Ad is enabled.', 'another-wordpress-classifieds-plugin' ) );
        $settings_manager->add_setting( $key, 'sent-ad-renew-email', __( 'Listing Needs to be Renewed', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( 'An email will be sent to remind the user to Renew the Ad when the Ad is about to expire.', 'another-wordpress-classifieds-plugin' ) );

        $settings_manager->add_setting(
            $key,
            'ad-renew-email-threshold',
            __( 'When should AWPCP send the expiration notice?', 'another-wordpress-classifieds-plugin' ),
            'textfield',
            5,
            __( 'Enter the number of days before the ad expires to send the email.', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting( $key, 'notifyofadexpiring', __( 'Listing Expired', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( 'An email will be sent when the Ad expires.', 'another-wordpress-classifieds-plugin' ) );
    }

    /**
     * Register settings for moderator notifications.
     */
    private function register_moderator_notifications_settings( $settings_manager ) {
        $key = $settings_manager->add_section( 'listings-settings', __( 'Moderator Notifications', 'another-wordpress-classifieds-plugin' ), 'moderator-notifications', 4, array( $settings, 'section' ) );

        $settings_manager->add_setting(
            $key,
            'send-listing-posted-notification-to-moderators',
            __( 'Listing Created', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings_manager->get_option( 'notifyofadposted' ),
            __( 'An email will be sent to moderators when a listing is created.', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting(
            $key,
            'send-listing-updated-notification-to-moderators',
            __( 'Listing Edited', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings_manager->get_option( 'notifyofadposted' ),
            __( 'An email will be sent to moderators when a listing is edited.', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting(
            $key,
            'send-listing-awaiting-approval-notification-to-moderators',
            __( 'Listing Awaiting Approval', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings_manager->get_option( 'notifyofadposted' ),
            __( 'An email will be sent to moderator users every time a listing needs to be approved.', 'another-wordpress-classifieds-plugin' )
        );
    }

    /**
     * Register settings for administrator notifications.
     */
    private function register_administrator_notifications_settings( $settings_manager ) {
        $key = $settings_manager->add_section( 'listings-settings', __( 'Admin Notifications', 'another-wordpress-classifieds-plugin' ), 'admin-notifications', 5, array( $settings, 'section' ) );

        $settings_manager->add_setting(
            $key,
            'notifyofadposted',
            __( 'Listing Created', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            1,
            __( 'An email will be sent when a listing is created.', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting( $key, 'notifyofadexpired', __( 'Listing Expired', 'another-wordpress-classifieds-plugin' ), 'checkbox', 1, __( 'An email will be sent when the Ad expires.', 'another-wordpress-classifieds-plugin' ) );

        $settings_manager->add_setting(
            $key,
            'send-listing-updated-notification-to-administrators',
            __( 'Listing Edited', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings_manager->get_option( 'notifyofadposted' ),
            __( 'An email will be sent to administrator when a listing is edited.', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting(
            $key,
            'send-listing-awaiting-approval-notification-to-administrators',
            __( 'Listing Awaiting Approval', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            $settings_manager->get_option( 'notifyofadposted' ),
            __( 'An email will be sent to administrator users every time a listing needs to be approved.', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting(
            $key,
            'send-listing-flagged-notification-to-administrators',
            __( 'Listing Was Flagged', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            true,
            __( 'An email will be sent to administrator users when a listing is flagged.', 'another-wordpress-classifieds-plugin' )
        );

        $settings_manager->add_setting(
            $key,
            'send-media-uploaded-notification-to-administrators',
            __( 'New media was uploaded', 'another-wordpress-classifieds-plugin' ),
            'checkbox',
            false,
            __( 'An email will be sent to administrator users when new media is added to a listing.', 'another-wordpress-classifieds-plugin' )
        );
    }
}
