<?php
/**
 * @package AWPCP\Tests\Media
 */

use Brain\Monkey\Functions;


/**
 * @since 4.0.0
 */
class AWPCP_ImageRendererTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->settings = Mockery::mock( 'AWPCP_Settings' );
    }

    /**
     * @dataProvider render_attachment_thumbnail_data_provider
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function test_render_attachment_thumbnail( $thumbnails_per_row, $thumbnail_width, $default_size ) {
        $sizes = '(max-width: 767px) 100vw, (max-width: 1023px) 33.33vw';

        $image_sizes = [
            'awpcp-thumbnail' => [
                'width' => $thumbnail_width,
            ],
        ];

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'display-thumbnails-in-columns' )
            ->andReturn( $thumbnails_per_row );

        Functions\when( 'wp_get_additional_image_sizes' )->justReturn( $image_sizes );

        Functions\expect( 'wp_get_attachment_image' )
            ->once()
            ->andReturnUsing(
                function( $attachment_id, $size, $icon, $attributes ) use ( $sizes, $default_size ) {
                    $this->assertContains( $sizes, $attributes['sizes'] );
                    $this->assertContains( $default_size, $attributes['sizes'] );
                }
            );

        // Execution.
        $this->get_test_subject()->render_attachment_thumbnail( wp_rand() );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ImageRenderer(
            $this->settings
        );
    }

    /**
     * @since 4.0.0
     */
    public function render_attachment_thumbnail_data_provider() {
        $thumbnail_width = 80;

        return [
            [ 0, $thumbnail_width, "{$thumbnail_width}px" ],
            [ 1, $thumbnail_width, '100vw' ],
            [ 2, $thumbnail_width, '50vw' ],
            [ 3, $thumbnail_width, '33.33vw' ],
            [ 4, $thumbnail_width, '25vw' ],
        ];
    }
}
