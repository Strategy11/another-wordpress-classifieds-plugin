<?php

class AWPCP_TestEmailSetttings extends AWPCP_UnitTestCase {

    public function test_validate_email_settings() {
        $group = 'email-settings';
        $options = array(
            'awpcpadminemail' => 'invalid email address',
            'admin-recipient-email' => 'invalid email email',
        );

        $original_email_address = 'user@example.com';

        $settings = awpcp()->settings;

        $settings->set_or_update_option( 'awpcpadminemail', $original_email_address );
        $settings->set_or_update_option( 'admin-recipient-email', $original_email_address );

        // Execution
        $new_options = $settings->validate_email_settings( $options, $group );

        // Verification
        $this->assertEquals( $original_email_address, $new_options['awpcpadminemail'] );
        $this->assertEquals( $original_email_address, $new_options['admin-recipient-email'] );
    }
}

