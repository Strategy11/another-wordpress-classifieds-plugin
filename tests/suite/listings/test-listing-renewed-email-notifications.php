<?php
/**
 * @package AWPCP\Tests\Plugin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Test ListingRenewedEmailNotification class.
 */
class AWPCP_ListingRenewedEmailNotificationTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function test_send_user_notification() {
        $notifications = $this->get_test_subject();

        // Execution.
        $email_sent = $notifications->send_user_notification( null );

        // Verification.
        $this->assertTrue( $email_sent );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        $this->listing_renderer  = Mockery::spy( 'AWPCP_ListingRenderer' );
        $this->template_renderer = Mockery::spy( 'AWPCP_TemplateRenderer' );
        $this->settings          = Mockery::spy( 'AWPCP_Settings' );

        Patchwork\redefine( 'AWPCP_Email::prepare', Patchwork\always( null ) );
        Patchwork\redefine( 'AWPCP_Email::send', Patchwork\always( true ) );

        return new AWPCP_ListingRenewedEmailNotifications(
            $this->listing_renderer,
            $this->template_renderer,
            $this->settings
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_send_admin_notification() {
        $notifications = $this->get_test_subject();

        Functions\when( 'awpcp_admin_email_to' )->justReturn( 'admin@example.org' );

        // Execution.
        $email_sent = $notifications->send_admin_notification( null );

        // Verification.
        $this->assertTrue( $email_sent );
    }
}
