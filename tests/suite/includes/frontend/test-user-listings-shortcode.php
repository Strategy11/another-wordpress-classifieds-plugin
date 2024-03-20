<?php
/**
 * @package AWPCP\Tests\Frontend
 */

/**
 * Unit tests for the AWPCPUSERLISTINGS shortcode.
 */
class AWPCP_UserListingsShortcodeTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     *
     * @dataProvider shortcode_attrs_data_provider
     */
    public function test_shortcode_attrs( $attrs, $query, $options, $current_user_id ) {
        $container = [
            'Meta'            => (object) [],
            'ShowListingPage' => (object) [],
        ];

        $this->redefine( 'awpcp_browse_listings_page', Patchwork\always( (object) [] ) );

        WP_Mock::userFunction( 'get_awpcp_option', [
            'return' => true,
        ] );
        WP_Mock::userFunction( 'is_user_logged_in', [
            'return' => true,
        ] );
        WP_Mock::userFunction( 'wp_enqueue_script', [
            'return' => '',
        ] );
        WP_Mock::userFunction( 'get_current_user_id', [
            'return' => $current_user_id,
        ] );

        $arguments = [];

        WP_Mock::userFunction( 'awpcp_display_listings', [
            'times' => 1,
            'args'  => [
                function( $query, $context, $options ) use ( &$arguments ) {
                    if ( $context !== 'user-listings-shortcode' ) {
                        return false;
                    }

                    $arguments['query']   = $query;
                    $arguments['context'] = $context;
                    $arguments['options'] = $options;

                    return true;
                },
            ],
        ] );

        $pages = new AWPCP_Pages( $container );

        // Execution.
        $pages->user_listings_shortcode( $attrs );

        // Verification.
        $this->assertEquals( $query, $arguments['query'] );
        $this->assertEquals( $options, $arguments['options'] );
    }

    public function shortcode_attrs_data_provider() {
        $current_user_id = wp_rand();

        return [
            [
                // 'limit' query var shouldn't be defined unless the attribute is
                // explicitely included.
                [],
                [
                    'context' => 'public-listings',
                    'author'  => $current_user_id,
                ],
                [
                    'show_menu_items' => true,
                    'show_pagination' => true,
                ],
                $current_user_id,
            ],
        ];
    }
}
