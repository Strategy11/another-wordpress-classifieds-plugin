<?php
/**
 * Unit tests for the AWPCP_Image_Placeholders class.
 *
 * @package AWPCP\Tests\Frontend
 */

use Brain\Monkey\Functions;

/**
 * @since 4.0.1
 */
class AWPCP_Image_Placeholders_Test extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.1
     */
    public function setup() {
        parent::setup();

        $this->attachment_properties  = Mockery::mock( 'AWPCP_Attachment_Properties' );
        $this->attachments_collection = Mockery::mock( 'AWPCP_Attachments_Collection' );
        $this->image_renderer         = Mockery::mock( 'AWPCP_ImageRenderer' );
        $this->listing_renderer       = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->settings               = Mockery::mock( 'AWPCP_Settings' );
    }

    /**
     * @since 4.0.1
     *
     * @dataProvider hide_no_image_placeholders_setting_data_provider
     */
    public function test_hide_no_image_placeholders_setting( $placeholder ) {
        $listing = (object) [
            'ID' => wp_rand(),
        ];

        $this->listing_renderer->shouldReceive( 'get_view_listing_url' );

        $this->attachments_collection->shouldReceive( 'get_featured_image' )
            ->andReturn( null );

        $this->attachments_collection->shouldReceive( 'count_attachments_of_type' )
            ->andReturn( 0 );

        Functions\expect( 'get_awpcp_option' )
            ->once()
            ->with( 'displayadthumbwidth' )
            ->andReturn( wp_rand() );

        Functions\expect( 'get_awpcp_option' )
            ->once()
            ->with( 'hide-noimage-placeholder', 1 )
            ->andReturn( true );

        Functions\when( 'awpcp_are_images_allowed' )->justReturn( true );

        $output = $this->get_test_subject()->do_image_placeholders(
            $listing,
            $placeholder
        );

        $this->assertEmpty( $output );
    }

    /**
     * @since 4.0.1
     */
    public function hide_no_image_placeholders_setting_data_provider() {
        return [
            [ 'featureimg' ],
            [ 'awpcpshowadotherimages' ],
            [ 'images' ],
            [ 'awpcp_image_name_srccode' ],
        ];
    }

    /**
     * @since 4.0.1
     */
    private function get_test_subject() {
        return new AWPCP_Image_Placeholders(
            $this->attachment_properties,
            $this->attachments_collection,
            $this->image_renderer,
            $this->listing_renderer,
            $this->settings
        );
    }
}
