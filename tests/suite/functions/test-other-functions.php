<?php

/**
 * @group core
 */
class AWPCP_TestFunctions extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();
        $this->_SERVER = $_SERVER;
    }

    public function teardown() {
        parent::teardown();
        $_SERVER = $this->_SERVER;
    }

    /**
     * @group deprecated
     * @expectedDeprecated awpcp_get_current_domain
     */
    function test_get_current_domain() {
        $_SERVER['HTTP_HOST'] = '';
        $_SERVER['SERVER_NAME'] = 'www.google.com';

        $this->assertEquals( 'www.google.com', awpcp_get_current_domain() );
        $this->assertEquals( 'google.com', awpcp_get_current_domain(false) );
        $this->assertEquals( '.google.com', awpcp_get_current_domain(false, '.') );

        $_SERVER['HTTP_HOST'] = 'www.example.com';

        $this->assertEquals( 'www.example.com', awpcp_get_current_domain() );
        $this->assertEquals( 'example.com', awpcp_get_current_domain(false) );
        $this->assertEquals( '.example.com', awpcp_get_current_domain(false, '.') );

        $_SERVER['HTTP_HOST'] = 'other.example.com';

        $this->assertEquals( 'other.example.com', awpcp_get_current_domain() );
        $this->assertEquals( 'other.example.com', awpcp_get_current_domain(false) );
        $this->assertEquals( 'other.example.com', awpcp_get_current_domain(false, '.') );
    }

    /**
     * @medium
     */
    public function test_ajaxurl() {
        // Fix for unexplained Undefined index: pagenow
        if ( ! isset( $GLOBALS['pagenow'] ) ) {
            $GLOBALS['pagenow'] = 'index.php';
        }

        // backup WordPress URL
        $siteurl = get_option('siteurl');
        $force_ssl_admin = force_ssl_admin();

        $hosts = array(
            'example.com' => array('example.com', 'www.example.com'),
            'www.example.com' => array('example.com', 'www.example.com'),
            'other.example.com' => array('other.example.com')
        );
        $wordpress = array('', '/wordpress', '/wordpress/more');

        // ajaxurl's domain should match request domain
        foreach ( array('http', 'https') as $scheme ) {
            foreach( array( false, true ) as $force_ssl ) {
                foreach ($hosts as $host => $domains) {
                    foreach ($domains as $domain) {
                        foreach ($wordpress as $wp) {
                            update_option('siteurl', "{$scheme}://{$domain}{$wp}");
                            force_ssl_admin( $force_ssl );

                            $_SERVER['HTTPS'] = $scheme == 'https' ? 'on' : 'off';
                            $_SERVER['HTTP_HOST'] = $host;

                            $this->assertEquals( "{$scheme}://{$host}{$wp}/wp-admin/admin-ajax.php", awpcp_ajaxurl( true ) );
                        }
                    }
                }
            }
        }

        update_option('siteurl', $siteurl);
        force_ssl_admin( $force_ssl_admin );
    }

    function test_parse_bool() {
        $this->assertTrue(awpcp_parse_bool('yes'));
        $this->assertTrue(awpcp_parse_bool('true'));
        $this->assertTrue(awpcp_parse_bool(true));
        $this->assertTrue(awpcp_parse_bool(1));
        $this->assertTrue(awpcp_parse_bool('bla'));
        $this->assertFalse(awpcp_parse_bool('no'));
        $this->assertFalse(awpcp_parse_bool('false'));
        $this->assertFalse(awpcp_parse_bool(false));
        $this->assertFalse(awpcp_parse_bool(0));
        $this->assertFalse(awpcp_parse_bool(''));
    }

    function test_array_insert() {
        $items = array('a' => 1, 'c' => 3, 'e' => 5);

        $before = array('a' => 1, 'b' => 2, 'c' => 3, 'e' => 5);
        $result = awpcp_array_insert_before($items, 'c', 'b', 2);
        $this->assertEquals($result, $before, 'resulting array has all items');
        $this->assertEquals(join('', array_keys($result)), join('', array_keys($before)), 'all items are properly ordered');

        $after = array('a' => 1, 'c' => 3, 'd' => 4, 'e' => 5);
        $result = awpcp_array_insert_after($items, 'c', 'd', 4);
        $this->assertEquals($result, $after, 'resulting array has all items');
        $this->assertEquals(join('', array_keys($result)), join('', array_keys($after)), 'all items are properly ordered');
    }

    // function test_replace_content_placeholders() {
    //     awpcp_do_placeholders($awpcp_current_ad, '$website_url
    //                                            $website_link
    //                                            $village
    //                                            $views
    //                                            $url_showad
    //                                            $url
    //                                            $twitter_button
    //                                            $tweetbtn
    //                                            $title
    //                                            $thumbnail_width
    //                                            $state
    //                                            $start_date
    //                                            $showadsense3
    //                                            $showadsense2
    //                                            $showadsense1
    //                                            $sharebtn
    //                                            $region
    //                                            $price
    //                                            $posted_date
    //                                            $location
    //                                            $imgblockwidth
    //                                            $images
    //                                            $flagad
    //                                            $flag_link
    //                                            $featureimg
    //                                            $featured_image
    //                                            $facebook_button
    //                                            $extra_fields
    //                                            $details
    //                                            $county
    //                                            $country
    //                                            $contact_url
    //                                            $contact_phone
    //                                            $contact_name
    //                                            $codecontact
    //                                            $city
    //                                            $category_url
    //                                            $category_name
    //                                            $category_link
    //                                            $awpcpvisitwebsite
    //                                            $awpcpshowadotherimages
    //                                            $awpcpextrafields
    //                                            $awpcpadviews
    //                                            $awpcpadpostdate
    //                                            $awpcp_state_display
    //                                            $awpcp_image_name_srccode
    //                                            $awpcp_display_price
    //                                            $awpcp_display_adviews
    //                                            $awpcp_country_display
    //                                            $awpcp_city_display
    //                                            $adsense_3
    //                                            $adsense_2
    //                                            $adsense_1
    //                                            $aditemprice
    //                                            $addetailssummary
    //                                            $addetails
    //                                            $adcontactphone
    //                                            $adcontact_name
    //                                            $ad_title
    //                                            $ad_startdate
    //                                            $ad_postdate
    //                                            $ad_categoryurl
    //                                            $ad_categoryname');

    //     // new placeholders - AWPCP >= 3.0
    //     $this->assertEquals('TITLE', awpcp_replace_content_placeholders("\$title", array('title' => 'TITLE')));
    //     $this->assertEquals('LOCATION', awpcp_replace_content_placeholders("\$location", array('location' => 'LOCATION')));

    //     // legacy placeholders - AWPCP < 3.0
    //     $this->assertEquals('FLAG LINK', awpcp_replace_content_placeholders("\$flagad", array('flag_link' => 'FLAG LINK')));
    //     $this->assertEquals('WEBSITE LINK', awpcp_replace_content_placeholders("\$awpcpvisitwebsite", array('website_link' => 'WEBSITE LINK')));
    //     $this->assertEquals('VIEWS', awpcp_replace_content_placeholders("\$awpcpadviews", array('views' => 'VIEWS')));
    //     $this->assertEquals('URL', awpcp_replace_content_placeholders("\$url_showad", array('url' => 'URL')));
    //     $this->assertEquals('THUMBNAIL WIDTH', awpcp_replace_content_placeholders("\$imgblockwidth", array('thumbnail_width' => 'THUMBNAIL WIDTH')));
    //     $this->assertEquals('THUMBNAIL', awpcp_replace_content_placeholders("\$awpcp_image_name_srccode", array('thumbnail' => 'THUMBNAIL')));
    // }

    function test_parse_money() {
        $this->assertEquals(12345678.35, awpcp_parse_money('12,345,678.35', '.', ','));
        $this->assertEquals(6.00, awpcp_parse_money('6,00', ',', '.'));

        $this->assertEquals(false, awpcp_parse_money('12,345,678.35', ',', '.'));
        $this->assertEquals(false, awpcp_parse_money('6,00', '.', ','));
    }

    /**
     * This test should catch problems with number_format function in PHP versions prior to 5.4.0.
     *
     * In PHP 5.4.0 number_format supports multiple bytes in dec_point and thousands_sep.
     * Only the first byte of each separator was used in older versions.
     */
    function test_format_money() {
        awpcp()->settings->set_or_update_option( 'include-space-between-currency-symbol-and-amount', false );
        awpcp()->settings->set_or_update_option( 'decimal-separator', 'ה' );
        awpcp()->settings->set_or_update_option( 'currency-symbol', '€' );

        $formatted = awpcp_format_money( 1.23 );

        $this->assertEquals( '€1ה23', $formatted );
    }

    // function test_format_date() {
    //     $date = strtotime( '1988-02-4' );

    //     $this->assertEquals( '4',                   awpcp_format_date( 'd', $date ) );
    //     $this->assertEquals( '04',                  awpcp_format_date( 'dd', $date ) );
    //     $this->assertEquals( '34',                  awpcp_format_date( 'o', $date ) );
    //     $this->assertEquals( '#034',                awpcp_format_date( '#oo', $date ) );
    //     $this->assertEquals( 'Thu',                 awpcp_format_date( 'D', $date ) );
    //     $this->assertEquals( 'Thursday',            awpcp_format_date( 'DD', $date ) );
    //     $this->assertEquals( '2',                   awpcp_format_date( 'm', $date ) );
    //     $this->assertEquals( '#02',                 awpcp_format_date( '#mm', $date ) );
    //     $this->assertEquals( 'Feb',                 awpcp_format_date( 'M', $date ) );
    //     $this->assertEquals( 'February',            awpcp_format_date( 'MM', $date ) );
    //     $this->assertEquals( '88',                  awpcp_format_date( 'y', $date ) );
    //     $this->assertEquals( '1988',                awpcp_format_date( 'yy', $date ) );
    //     $this->assertEquals( '570931200',           awpcp_format_date( '@', $date ) );
    //     $this->assertEquals( '6270652800000000',    awpcp_format_date( '!', $date ) );
    //     $this->assertEquals( '1988-02-04',          awpcp_format_date( 'ATOM', $date ) );
    //     $this->assertEquals( 'Thu, 04 Feb 1988',    awpcp_format_date( 'COOKIE', $date ) );
    //     $this->assertEquals( '1988-02-04',          awpcp_format_date( 'ISO_8601', $date ) );
    //     $this->assertEquals( 'Thu, 4 Feb 88',       awpcp_format_date( 'RFC_822', $date ) );
    //     $this->assertEquals( 'Thursday, 04-Feb-88', awpcp_format_date( 'RFC_850', $date ) );
    //     $this->assertEquals( 'Thu, 4 Feb 88',       awpcp_format_date( 'RFC_1036', $date ) );
    //     $this->assertEquals( 'Thu, 4 Feb 1988',     awpcp_format_date( 'RFC_1123', $date ) );
    //     $this->assertEquals( 'Thu, 4 Feb 1988',     awpcp_format_date( 'RFC_2822', $date ) );
    //     $this->assertEquals( 'Thu, 4 Feb 88',       awpcp_format_date( 'RSS', $date ) );
    //     $this->assertEquals( '6270652800000000',    awpcp_format_date( 'TICKS', $date ) );
    //     $this->assertEquals( '570931200',           awpcp_format_date( 'TIMESTAMP', $date ) );

    //     $date = strtotime( '2013-05-09' );

    //     $this->assertEquals( '#128',                awpcp_format_date( '#oo', $date ) );
    // }

    // function test_format_time() {
    //     $date = strtotime( '1988-02-4 3:07:25 PM' );

    //     $this->assertEquals( '3', awpcp_format_time( 'h', $date ) );
    //     $this->assertEquals( '03', awpcp_format_time( 'hh', $date ) );
    //     $this->assertEquals( '15', awpcp_format_time( 'H', $date ) );
    //     $this->assertEquals( '15', awpcp_format_time( 'HH', $date ) );
    //     $this->assertEquals( '7', awpcp_format_time( 'm', $date ) );
    //     $this->assertEquals( '25', awpcp_format_time( 's', $date ) );
    //     $this->assertEquals( 'pm', awpcp_format_time( 'a', $date ) );
    //     $this->assertEquals( 'PM', awpcp_format_time( 'A', $date ) );
    // }

    function test_get_email_verification_url() {
        $this->assertTrue( strpos( awpcp_get_email_verification_url( 1 ), awpcp_get_email_verification_hash( 1 ) ) !== FALSE );
        $this->assertNotEquals( awpcp_get_email_verification_hash( 1 ), awpcp_get_email_verification_hash( 2 ) );
    }

    function test_get_email_verification_hash() {
        $this->assertNotEquals( awpcp_get_email_verification_url( 1 ), awpcp_get_email_verification_url( 2 ) );
    }

    function test_verify_email_verification_hash() {
        $this->assertTrue( awpcp_verify_email_verification_hash( 1, awpcp_get_email_verification_hash( 1 ) ) );
    }

    public function test_awpcp_login_form_uses_custom_registration_url() {
        update_option( 'users_can_register', true );
        $registration_url = 'http://reg.istration.url';
        awpcp()->settings->update_option( 'registrationurl', $registration_url );

        $output = awpcp_login_form();

        $this->assertContains( $registration_url, $output );
    }

    public function test_get_option_removes_whitespace_around_option_values() {
        $email_address = 'wvega@wvega.com';

        awpcp()->settings->update_option( 'paypalemail', ' ' .  $email_address .  ' ' );

        $this->assertEquals( $email_address, get_awpcp_option( 'paypalemail' ) );
    }

    public function test_phpmailer_init_smtp() {
        $hostname = 'smtp.gmail.com';
        $port = '25';
        $username = 'awpcp-developer';
        $password = 'password';

        awpcp()->settings->set_or_update_option( 'usesmtp', true );
        awpcp()->settings->set_or_update_option( 'smtphost', $hostname );
        awpcp()->settings->set_or_update_option( 'smtpport', $port );
        awpcp()->settings->set_or_update_option( 'smtpusername', $username );
        awpcp()->settings->set_or_update_option( 'smtppassword', $password );

        $phpmailer = new stdClass();
        awpcp_phpmailer_init_smtp( $phpmailer );

        $this->assertEquals( 'smtp', $phpmailer->Mailer );
        $this->assertEquals( $hostname, $phpmailer->Host );
        $this->assertEquals( $port, $phpmailer->Port );
        $this->assertEquals( $username, $phpmailer->Username );
        $this->assertEquals( $password, $phpmailer->Password );
    }

    public function test_pagination_handles_division_by_zero() {
        awpcp_pagination( array( 'results' => 0 ), 'http://example.org' );

        // the test pass if it reaches this point without throwing a PHP Warning.
        $this->assertTrue( true );
    }
}
