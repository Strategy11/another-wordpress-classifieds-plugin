<?php
/**
 * @package AWPCP\Tests\Suite\FormFields
 */

/**
 * Test for Form Fields Data class.
 */
class AWPCP_FormFieldsDataTest extends AWPCP_UnitTestCase {

    private $authorization;
    private $listing_renderer;

    /**
     * @since 4.0.0
     */
    protected $request;

    public function setUp(): void {
        parent::setUp();
        $this->authorization    = Mockery::mock( 'AWPCP_ListingAuthorization' );
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->request          = Mockery::mock( 'AWPCP_Request' );

        WP_Mock::userFunction( 'awpcp_maybe_add_http_to_url', [
            'return' => function( $arg ) {
                return $arg;
            },
        ] );
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

        WP_Mock::userFunction( 'awpcp_get_var', [
            'args'   => [ [ 'param' => 'ad_details', 'sanitize' => 'sanitize_textarea_field' ] ],
            'return' => '1',
        ] );
        WP_Mock::userFunction( 'awpcp_get_var', [
            'args'   => [ [ 'param' => 'ad_title' ] ],
            'return' => 'Test Title',
        ] );
        WP_Mock::userFunction( 'awpcp_parse_money', [
            'return' => '1',
        ] );

        WP_Mock::userFunction( 'awpcp_get_digits_from_string', [
            'return' => function( $arg ) {
                return strip_tags( $arg );
            },
        ] );
        WP_Mock::userFunction( 'current_time', [
            'return' => time(),
        ] );

        $form_fields_data = $this->get_test_subject();

        // Execution.
        $data = $form_fields_data->get_posted_data( null );

        // Verification.
        $this->assertArrayHasKey( 'post_fields', $data );
        $this->assertNotEmpty( $data['post_fields']['post_title'] );

		$this->markTestSkipped( 'Failing. Needs work' );
        //$this->assertTrue( Brain\Monkey\Filters\applied( 'awpcp-get-posted-data' ) > 0 );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_FormFieldsData(
            $this->authorization,
            $this->listing_renderer
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

        $this->authorization
            ->shouldReceive( 'is_current_user_allowed_to_edit_listing_start_date' )
            ->andReturn( true );
        $this->authorization
            ->shouldReceive( 'is_current_user_allowed_to_edit_listing_end_date' )
            ->andReturn( true );

        WP_Mock::userFunction( 'awpcp_get_var', [
            'args' => [ array( 'param' => 'ad_details', 'sanitize' => 'sanitize_textarea_field' ) ],
            'return' => $html_content,
        ] );
        WP_Mock::userFunction( 'awpcp_get_var', [
            'args' => [ array( 'param' => 'ad_title') ],
            'return' => 'ad_title',
        ] );

        WP_Mock::userFunction( 'awpcp_strip_all_tags_deep', [
            'times'  => 1,
            'args'   => [ $html_content ],
            'return' => function( $arg ) {
                return strip_tags( $arg );
            },
        ] );

        WP_Mock::userFunction( 'awpcp_get_var', [
            'args' => [ [ 'param' => 'ad_item_price' ] ],
            'return' => 150,
        ] );

        WP_Mock::userFunction( 'awpcp_parse_money', [
            'times'  => 1,
            'args'   => [ 150 ],
            'return' => '150',
        ] );

        WP_Mock::userFunction( 'awpcp_get_digits_from_string' );
        WP_Mock::userFunction( 'current_time' );
        $this->listing_renderer->shouldReceive( 'get_plain_start_date' )
                               ->andReturn( '' );
        $this->listing_renderer->shouldReceive( 'get_plain_end_date' )
                               ->andReturn( '' );

        // Execution.
        $data = $this->get_test_subject()->get_posted_data( $listing );
        // Verification.
        $this->assertStringContainsStringIgnoringCase( '<img src="/wp-content/uploads/2019/06/B.jpg" width="50%" height="50%" /><hr />', $data['post_fields']['post_content'] );
    }
}
