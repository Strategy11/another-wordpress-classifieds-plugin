<?php
/**
 * @package AWPCP\Tests\Plugin\FormFields
 */

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * Unit tests for Form Fields Data Validator.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AWPCP_FormFieldsDataValidatorTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->roles = Mockery::mock( 'AWPCP_RolesAndCapabilities' );

        $this->data = array(
            'post_fields'      => array(
                'post_title' => 'Test Listing',
            ),
            'metadata'         => array(
                '_awpcp_start_date'    => '',
                '_awpcp_end_date'      => '',
                '_awpcp_website_url'   => 'https://example.org',
                '_awpcp_contact_email' => 'john@example.org',
                '_awpcp_price'         => '5.99',
            ),
            'terms_of_service' => 'accepted',
        );

        Functions\when( 'awpcp_is_email_address_allowed' )->justReturn( true );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_validation_errors_applies_filter() {
        $this->get_validation_errors();

		$this->markTestSkipped( 'Failing. Needs work' );
        $this->assertTrue( Filters\applied( 'awpcp-validate-post-listing-details' ) > 0 );
    }

    /**
     * @since 4.0.0
     */
    public function test_it_validates_post_title() {
        $this->data['post_fields']['post_title'] = '';

        $errors = $this->get_validation_errors();

        // Verification.
        $this->assertArrayHasKey( 'ad_title', $errors );
    }

    /**
     * @since 4.0.0
     */
    private function get_validation_errors() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $this->authorization = Mockery::mock( 'AWPCP_ListingAuthorization' );
        $this->settings      = Mockery::mock( 'AWPCP_SettingsAPI' );

        $this->authorization->shouldReceive( 'is_current_user_allowed_to_edit_listing_start_date' )
            ->andReturn( true );

        $this->authorization->shouldReceive( 'is_current_user_allowed_to_edit_listing_end_date' )
            ->andReturn( true );

        $this->roles->shouldReceive( 'current_user_is_moderator' )->andReturn( false );

        $this->settings->shouldReceive( 'get_option' )->andReturnUsing(
            function( $name ) {
                $options = array(
                    'displaywebsitefield'               => true,
                    'displaywebsitefieldreqop'          => true,
                    'ad_poster_email_address_whitelist' => "example.com\nexample.net",
                    'displayphonefield'                 => true,
                    'displayphonefieldreqop'            => true,
                    'displaypricefield'                 => true,
                    'displaypricefieldreqop'            => true,
                    'requiredtos'                       => true,
                );

                return $options[ $name ];
            }
        );

        $validator = $this->get_test_subject();

        // Execution.
        return $validator->get_validation_errors( $this->data, $post );
    }

    /**
     * @since 4.0.1
     */
    private function get_test_subject() {
        return new AWPCP_FormFieldsValidator(
            $this->authorization,
            $this->roles,
            $this->settings
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_it_validates_website_url() {
        $this->check_metadata_error( '_awpcp_website_url', 'not-a-url', 'websiteurl' );
    }

    /**
     * @param string $meta_name     The name of the post meta.
     * @param mixed  $posted_value  An invalid value for the given meta.
     * @param string $error_name    The index of the expected error.
     * @since 4.0.0
     */
    public function check_metadata_error( $meta_name, $posted_value, $error_name ) {
        $this->data['metadata'][ $meta_name ] = $posted_value;

        $errors = $this->get_validation_errors();

        // Verification.
        $this->assertArrayHasKey( $error_name, $errors );
    }

    /**
     * @since 4.0.0
     */
    public function test_contact_name_is_required() {
        $this->check_metadata_error( '_awpcp_contact_name', '', 'ad_contact_name' );
    }

    /**
     * @since 4.0.0
     */
    public function test_contact_email_is_required() {
        $this->check_metadata_error( '_awpcp_contact_email', '', 'ad_contact_email' );
    }

    /**
     * @since 4.0.0
     */
    public function test_contact_email_must_be_a_valid_email_address() {
        $this->check_metadata_error( '_awpcp_contact_email', 'not-an-email-address', 'ad_contact_email' );
    }

    /**
     * @since 4.0.0
     */
    public function test_contact_email_must_be_one_of_the_allowed_addresses() {
        Functions\when( 'awpcp_is_email_address_allowed' )->justReturn( false );

        $this->check_metadata_error( '_awpcp_contact_email', 'not@allowed.address.test', 'ad_contact_email' );
    }

    /**
     * @since 4.0.0
     */
    public function test_contact_phone_is_required() {
        $this->check_metadata_error( '_awpcp_contact_phone', '', 'ad_contact_phone' );
    }

    /**
     * @since 4.0.0
     */
    public function test_price_is_required() {
        $this->check_metadata_error( '_awpcp_price', '', 'ad_item_price' );
    }

    /**
     * @since 4.0.0
     */
    public function test_price_must_be_a_number() {
        $this->check_metadata_error( '_awpcp_price', 'not a number', 'ad_item_price' );
    }
}
