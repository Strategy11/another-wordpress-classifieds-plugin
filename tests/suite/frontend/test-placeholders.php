<?php

/**
 * @group core
 */
class AWPCP_TestPlaceholders extends AWPCP_UnitTestCase {

    public function test_excerpt() {
        $ad = (object) array( 'ID' => rand() + 1 );
        $ad->post_content = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";

        $expected = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of";

        awpcp()->settings->set_or_update_option( 'words-in-listing-excerpt', 33 );

        $excerpt = awpcp_do_placeholders( $ad, '$excerpt', 'listings' );
        $excerpt = str_replace( '&hellip;' , '', $excerpt );

        $this->assertEquals( $expected, $excerpt, 'The number of words in the excerpt should be controlled by plugin settings.' );
        $this->assertEquals( 33, str_word_count( $excerpt ), 'The number of words in the excerpt should be controlled by plugin settings.' );
    }

    public function test_date_placeholders() {
        awpcp()->settings->set_or_update_option( 'date-format', 'F d, Y' );

        $test_value = '2013-10-24 12:34:56';
        $expected_value = 'October 24, 2013';

        $ad = awpcp_tests_create_listing();
        $ad->post_date = $test_value;
        $ad->post_modified = $test_value;

        update_post_meta( $ad->ID, '_awpcp_start_date', $test_value );
        update_post_meta( $ad->ID, '_awpcp_end_date', $test_value );
        update_post_meta( $ad->ID, '_awpcp_renewed_date', $test_value );
		update_post_meta( $ad->ID, '_awpcp_verification_date', $test_value );

        $this->assertEquals( $expected_value, awpcp_do_placeholders( $ad, '$start_date', 'listings' ) );
        $this->assertEquals( $expected_value, awpcp_do_placeholders( $ad, '$end_date', 'listings' ) );
        $this->assertEquals( $expected_value, awpcp_do_placeholders( $ad, '$posted_date', 'listings' ) );
        $this->assertEquals( $expected_value, awpcp_do_placeholders( $ad, '$last_updated_date', 'listings' ) );
        $this->assertEquals( $expected_value, awpcp_do_placeholders( $ad, '$renewed_date', 'listings' ) );
    }

    public function test_renewed_date_placeholder_returns_posted_date_if_listing_has_not_been_renewed() {
        awpcp()->settings->set_or_update_option( 'date-format', 'F d, Y' );

        $test_value = '2013-10-24 12:34:56';
        $expected_value = 'October 24, 2013';

        $ad = awpcp_tests_create_listing();
        $ad->post_date = $test_value;

        $this->assertEquals( $expected_value, awpcp_do_placeholders( $ad, '$renewed_date', 'listings' ) );
    }

    public function test_price_placeholder() {
        awpcp()->settings->set_or_update_option( 'displaypricefield', true );
        awpcp()->settings->set_or_update_option( 'price-field-is-restricted', false );

        $price = 10 * ( rand() + 1 );
        $ad = awpcp_tests_create_listing();

        update_post_meta( $ad->ID, '_awpcp_price', $price );

        $output = awpcp_do_placeholder_price( $ad, 'price' );

        $this->assertContains( awpcp_format_money( $price / 100 ), $output );
    }

    public function test_price_placeholder_if_price_cannot_be_seen_by_anonymous_users() {
        $this->logout();

        awpcp()->settings->set_or_update_option( 'displaypricefield', true );
        awpcp()->settings->set_or_update_option( 'price-field-is-restricted', true );

        $ad = awpcp_tests_create_attachment();

        wp_update_post( $ad->ID, '_awpcp_price', 10 * ( rand() + 1 ) );

        $output = awpcp_do_placeholder_price( $ad, 'price' );

        $this->assertEquals( '', $output );
    }

    public function test_image_placeholders_are_empty_if_images_are_not_allowed() {
        awpcp()->settings->set_or_update_option( 'allowed-image-extensions', array() );

        $listing = (object) array( 'ID' => rand() + 1 );

        $output = awpcp_do_placeholder_images( $listing, 'awpcp_image_name_srccode' );

        $this->assertEquals( '', $output );
    }

    public function test_image_placeholders_return_the_fallback_image_if_the_ad_has_no_images() {
        awpcp()->settings->set_or_update_option( 'allowed-image-extensions', array( 'jpg' ) );

        $listing = (object) array( 'ID' => rand() + 1 );
        $listing->post_title = 'AWPCP Test Ad';

        $output = awpcp_do_placeholder_images( $listing, 'awpcp_image_name_srccode' );

        $this->assertContains( 'adhasnoimage.png', $output );
    }

    public function test_image_placeholders_do_not_trigger_warnings_when_image_filenames_include_percent_signs() {
        $url_with_percent_signs = 'http://example.com/%2F-wtf.png';

        $listing = (object) array( 'ID' => rand() + 1 );

        $featured_image = new WP_Post( new stdClass() );
        $featured_image->ID = rand() + 1;

        $another_image = new WP_Post( new stdClass() );
        $another_image->ID = rand() + 1;

        $attachments = Phake::mock( 'AWPCP_Attachments_Collection' );
        $attachment_properties = Phake::mock( 'AWPCP_Attachment_Properties' );
        $listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );

        Phake::when( $attachments )->get_featured_attachment_of_type( 'image', Phake::ignoreRemaining() )->thenReturn( $featured_image );
        Phake::when( $attachments )->count_attachments_of_type->thenReturn( 2 );
        Phake::when( $attachments )->find_visible_attachments->thenReturn( array( $another_image ) );

        Phake::when( $attachment_properties )->get_image_url->thenReturn( $url_with_percent_signs );

        $placeholders = new AWPCP_Image_Placeholders( $attachment_properties, $attachments, $listing_renderer );
        $output = $placeholders->do_image_placeholders( $listing, 'awpcpshowadotherimages' );

        $this->assertContains( $url_with_percent_signs, $output );
    }

    public function test_contact_url_placeholder() {
        $listing = (object) array( 'ID' => rand() + 1 );
        $listing->post_title = 'Whatever';

        $content = awpcp_do_placeholder_contact_url( $listing, null );

        $this->assertContains( (string) $listing->ID, $content );
    }

    public function test_contact_name_placeholder() {
        $listing = (object) array( 'ID' => rand() + 1 );

        $contact_name = 'John Doe';
        update_post_meta( $listing->ID, '_awpcp_contact_name', $contact_name );

        $content = awpcp_do_placeholder_contact_name( $listing, null );

        $this->assertContains( $contact_name, $content );
    }

    public function test_location_placeholder() {
        $listing = awpcp_tests_create_listing();

        awpcp_basic_regions_api()->update_ad_regions( $listing, array(
            array(
                'country' => 'Colombia',
                'state' => 'Antioquia',
                'city' => 'Medellín',
            ),
        ), 1 );

        $content = awpcp_do_placeholder_location( $listing, 'location' );

        $this->assertContains( 'Medellín, Antioquia, Colombia', $content );
    }

    public function test_details_placeholder() {
        $secure_url = 'https://example.org/';
        $insecure_url = 'http://example.com/';

        $listing = (object) array(
            'ad_id' => rand() + 1,
            'ad_details' => $secure_url . ' ' . $insecure_url,
        );

        awpcp()->settings->set_or_update_option( 'hyperlinkurlsinadtext', true );

        $content = awpcp_do_placeholder_details( $listing, 'addetails' );

        $this->assertContains( 'href="' . $insecure_url . '"', $content );
        $this->assertContains( 'href="' . $secure_url . '"', $content );
    }
}
