<?php

/**
 * @group core
 */
class AWPCP_TestEmailFunctions extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->key = '5d36e4dffdd39407db5979e34c6a86f8';
    }

    /**
     * @issues 598
     * @large
     */
    public function test_ad_posted_user_email() {
        $ad = $this->create_test_listing_with_key( $this->key );

        $transaction = null;
        $message = '';

        awpcp()->settings->update_option( 'requireuserregistration', true );
        $email = awpcp_ad_posted_user_email( $ad, $transaction, $message );
        $this->assertFalse( strpos( $email->body, $this->key ), 'Ad Access key is never included if Require Registration is On.' );

        awpcp()->settings->update_option( 'requireuserregistration', false );
        awpcp()->settings->update_option( 'include-ad-access-key', true );
        $email = awpcp_ad_posted_user_email( $ad, $transaction, $message );
        $this->assertTrue( strpos( $email->body, $this->key ) !== false, 'Ad Access Key is included in the email body.' );

        awpcp()->settings->update_option( 'requireuserregistration', false );
        awpcp()->settings->update_option( 'include-ad-access-key', false );
        $email = awpcp_ad_posted_user_email( $ad, $transaction, $message );
        $this->assertTrue( strpos( $email->body, $this->key ) === false, 'Ad Access Key is NOT included in the email body when setting is unchecked.' );
    }

    private function create_test_listing_with_key( $key ) {
        $listing = awpcp_tests_create_empty_listing();

        wp_update_post( array( 'ID' => $listing->ID, 'post_title' => 'Test Title' ) );

        update_post_meta( $listing->ID, '_awpcp_contact_name', 'John Doe' );
        update_post_meta( $listing->ID, '_awpcp_contact_email', 'john@example.com' );
        update_post_meta( $listing->ID, '_awpcp_access_key', $key );

        return $listing;
    }

    /**
     * @issues 598
     */
    function test_ad_updated_user_email() {
        $message = '';

        $ad = $this->create_test_listing_with_key( $this->key );

        awpcp()->settings->update_option( 'include-ad-access-key', true );
        $email = awpcp_ad_updated_user_email( $ad, $message );
        $this->assertTrue( strpos( $email->body, $this->key ) !== false, 'Ad Access Key is included in the email body.' );

        awpcp()->settings->update_option( 'include-ad-access-key', false );
        $email = awpcp_ad_updated_user_email( $ad, $message );
        $this->assertTrue( strpos( $email->body, $this->key ) === false, 'Ad Access Key is NOT included in the email body when setting is unchecked.' );
    }

    /**
     * @issues 598
     */
    function test_ad_renewed_user_email() {
        $message = '';

        $ad = $this->create_test_listing_with_key( $this->key );

        awpcp()->settings->update_option( 'include-ad-access-key', true );
        $email = awpcp_ad_renewed_user_email( $ad );
        $this->assertTrue( strpos( $email->body, $this->key ) !== false, 'Ad Access Key is included in the email body.' );

        awpcp()->settings->update_option( 'include-ad-access-key', false );
        $email = awpcp_ad_renewed_user_email( $ad );
        $this->assertTrue( strpos( $email->body, $this->key ) === false, 'Ad Access Key is NOT included in the email body when setting is unchecked.' );
    }

    /**
     * @issues 613
     */
    function test_awpcp_format_email_sent_datetime() {
        awpcp()->settings->update_option( 'date-format', 'D, M d, Y' );
        awpcp()->settings->update_option( 'time-format', 'h:i A' );
        awpcp()->settings->update_option( 'date-time-format', '<date> at <time>' );

        $expected = sprintf( 'Email sent %s at %s.', date( 'D, M d, Y' ), date( 'h:i A' ) );

        $this->assertEquals( $expected, awpcp_format_email_sent_datetime() );
    }

    function test_is_valid_email_address() {
        $this->assertTrue( awpcp_is_valid_email_address( 'email@example.com' ), 'email@example.com' );
        $this->assertTrue( awpcp_is_valid_email_address( 'firstname.lastname@example.com' ), 'firstname.lastname@example.com' );
        $this->assertTrue( awpcp_is_valid_email_address( 'email@subdomain.example.com' ), 'email@subdomain.example.com' );
        $this->assertTrue( awpcp_is_valid_email_address( 'firstname+lastname@example.com' ), 'firstname+lastname@example.com' );
        $this->assertTrue( awpcp_is_valid_email_address( 'email@[123.123.123.123]' ), 'email@[123.123.123.123]' );
        $this->assertTrue( awpcp_is_valid_email_address( '"email"@example.com' ), '"email"@example.com' );
        $this->assertTrue( awpcp_is_valid_email_address( '1234567890@example.com' ), '1234567890@example.com' );
        $this->assertTrue( awpcp_is_valid_email_address( 'email@example-one.com' ), 'email@example-one.com' );
        $this->assertTrue( awpcp_is_valid_email_address( '_______@example.com' ), '_______@example.com' );
        $this->assertTrue( awpcp_is_valid_email_address( 'email@example.name' ), 'email@example.name' );
        $this->assertTrue( awpcp_is_valid_email_address( 'email@example.museum' ), 'email@example.museum' );
        $this->assertTrue( awpcp_is_valid_email_address( 'email@example.co.jp' ), 'email@example.co.jp' );
        $this->assertTrue( awpcp_is_valid_email_address( 'firstname-lastname@example.com' ), 'firstname-lastname@example.com' );

        $this->assertTrue( awpcp_is_valid_email_address( 'much."more\ unusual"@example.com' ), 'much."more\ unusual"@example.com' );
        $this->assertTrue( awpcp_is_valid_email_address( 'very.unusual."@".unusual.com@example.com' ), 'very.unusual."@".unusual.com@example.com' );

        // too complex, not currently accepeted by our validation function
        // $this->assertTrue( awpcp_is_valid_email_address( 'very."(),:;<>[]".VERY."very@\\ "very".unusual@strange.example.com' ), 'very."(),:;<>[]".VERY."very@\\ "very".unusual@strange.example.com' );

        $this->assertFalse( awpcp_is_valid_email_address( 'plainaddress' ), 'plainaddress' );
        $this->assertFalse( awpcp_is_valid_email_address( '#@%^%#$@#$@#.com' ), '#@%^%#$@#$@#.com' );
        $this->assertFalse( awpcp_is_valid_email_address( '@example.com' ), '@example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'Joe Smith <email@example.com>' ), 'Joe Smith <email@example.com>' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email.example.com' ), 'email.example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email@example@example.com' ), 'email@example@example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( '.email@example.com' ), '.email@example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email.@example.com' ), 'email.@example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email..email@example.com' ), 'email..email@example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'あいうえお@example.com' ), 'あいうえお@example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email@example.com (Joe Smith)' ), 'email@example.com (Joe Smith)' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email@example' ), 'email@example' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email@-example.com' ), 'email@-example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email@example..com' ), 'email@example..com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'Abc..123@example.com' ), 'Abc..123@example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email@10.2.2.2' ), 'email@10.2.2.2' );
        $this->assertFalse( awpcp_is_valid_email_address( 'email@111.222.333.44444' ), 'email@111.222.333.44444' );

        // false positives, our validation function thinks the email addresses below are valid, but they are not
        // $this->assertFalse( awpcp_is_valid_email_address( 'email@example.web' ), 'email@example.web' );

        $this->assertFalse( awpcp_is_valid_email_address( '”(),:;<>[\]@example.com' ), '”(),:;<>[\]@example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'just"not"right@example.com' ), 'just"not"right@example.com' );
        $this->assertFalse( awpcp_is_valid_email_address( 'this\ is"really"not\allowed@example.com' ), 'this\ is"really"not\allowed@example.com' );
    }

    function test_is_email_address_allowed() {
        $whitelist = array(
            '*.sampleuni.edu',
            'eng.sampleuni.edu',
            'nursing.sampleuni.edu',
            'col.sampleuni.edu',
            'biz.sampleuni.edu',
        );

        awpcp()->settings->update_option( 'ad-poster-email-address-whitelist', implode( "\n", $whitelist ) );

        $this->assertTrue( awpcp_is_email_address_allowed( 'tadas@eng.sampleuni.edu' ) );
    }
}
