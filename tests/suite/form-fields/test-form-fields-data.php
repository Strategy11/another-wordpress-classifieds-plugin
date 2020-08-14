<?php
/**
 * @package AWPCP\Tests\Suite\FormFields
 */

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * Test for Form Fields Data class.
 */
class AWPCP_FormFieldsDataTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->authorization    = Mockery::mock( 'AWPCP_ListingAuthorization' );
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->request          = Mockery::mock( 'AWPCP_Request' );

        Functions\when( 'awpcp_maybe_add_http_to_url' )->returnArg();
    }

    /**
     * @since 4.0.0
     */
    public function test_get_posted_data_return_data_for_standard_fields() {
        $this->authorization->shouldReceive( 'is_current_user_allowed_to_edit_listing_start_date' )
            ->andReturn( true );

        $this->authorization->shouldReceive( 'is_current_user_allowed_to_edit_listing_end_date' )
            ->andReturn( true );

        $this->listing_renderer->shouldReceive( 'get_plain_start_date' )
            ->andReturn( null );
        $this->listing_renderer->shouldReceive( 'get_plain_end_date' )
            ->andReturn( null );

        $this->request->shouldReceive( 'param' )
            ->once()
            ->with( 'ad_title' )
            ->andReturn( 'Test Title' );

        $this->request->shouldReceive( 'param' );

        Functions\when( 'awpcp_strip_all_tags_deep' )->returnArg();

        $form_fields_data = $this->get_test_subject();

        // Execution.
        $data = $form_fields_data->get_posted_data( null );

        // Verification.
        $this->assertArrayHasKey( 'post_fields', $data );
        $this->assertNotEmpty( $data['post_fields']['post_title'] );
        $this->assertTrue( Filters\applied( 'awpcp-get-posted-data' ) > 0 );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_FormFieldsData(
            $this->authorization,
            $this->listing_renderer,
            $this->request
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_posted_data_returns_original_date_if_no_value_was_provided() {
        $listing = (object) [];

        $start_date = '2019-01-23';
        $end_date   = '2019-01-23';

        $this->request->shouldReceive( 'param' )->andReturn( null );

        Functions\when( 'awpcp_strip_all_tags_deep' )->returnArg();

        $this->authorization
            ->shouldReceive( 'is_current_user_allowed_to_edit_listing_start_date' )
            ->andReturn( true );
        $this->authorization
            ->shouldReceive( 'is_current_user_allowed_to_edit_listing_end_date' )
            ->andReturn( true );

        $this->listing_renderer->shouldReceive( 'get_plain_start_date' )
            ->andReturn( $start_date );
        $this->listing_renderer->shouldReceive( 'get_plain_end_date' )
            ->andReturn( $end_date );

        $form_fields_data = $this->get_test_subject();

        // Execution.
        $data = $form_fields_data->get_posted_data( $listing );

        // Verification.
        $this->assertEquals( $start_date, $data['metadata']['_awpcp_start_date'] );
        $this->assertEquals( $end_date, $data['metadata']['_awpcp_end_date'] );
    }

    /**
     * @since 4.0.2
     */
    public function test_get_posted_data_keeps_html_in_details_field() {
        $listing = (object) [];

        $html_content = "<hr />\n<b>CO2-Effizlenz</b>\nAuf der Grundlage der gemessenen CO2-Emissionen unter Berücksichtigung der Masse des Fahrzeugs ermittelt.<br/>\n<img src=\"/wp-content/uploads/2019/06/B.jpg\" width=\"50%\" height=\"50%\" /><hr />";

        $this->request->shouldReceive( 'param' )
            ->with( 'ad_details' )
            ->andReturn( $html_content );

        $this->request->shouldReceive( 'param' )
            ->andReturn( null );

        Functions\expect( 'awpcp_strip_all_tags_deep' )
            ->never()
            ->with( $html_content )
            ->andReturn( strip_tags( $html_content ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags

        Functions\expect( 'awpcp_strip_all_tags_deep' )
            ->with( Mockery::any() )
            ->andReturnUsing( 'strip_tags' );

        $this->authorization
            ->shouldReceive( 'is_current_user_allowed_to_edit_listing_start_date' )
            ->andReturn( true );
        $this->authorization
            ->shouldReceive( 'is_current_user_allowed_to_edit_listing_end_date' )
            ->andReturn( true );

        $this->listing_renderer->shouldReceive( 'get_plain_start_date' )
            ->andReturn( '' );
        $this->listing_renderer->shouldReceive( 'get_plain_end_date' )
            ->andReturn( '' );

        // Execution.
        $data = $this->get_test_subject()->get_posted_data( $listing );

        // Verification.
        $this->assertContains( '<img src="/wp-content/uploads/2019/06/B.jpg" width="50%" height="50%" /><hr />', $data['post_fields']['post_content'] );
    }
}
