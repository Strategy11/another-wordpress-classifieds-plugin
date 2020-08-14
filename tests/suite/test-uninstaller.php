<?php
/**
 * @package AWPCP\Tests\Plugin
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for Uninstaller.
 */
class AWPCP_UninstallerTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function test_uninstall() {
        $categories = [
            (object) [
                'term_id' => wp_rand() + 1,
            ],
        ];

        $listings = [
            (object) [
                'ID' => wp_rand() + 1,
            ],
        ];

        $uploads_dir = sys_get_temp_dir() . '/' . md5( wp_rand() );
        // phpcs:disable WordPress.VIP.FileSystemWritesDisallow.directory_mkdir
        mkdir( $uploads_dir );
        // phpcs:disable

        $this->listings_logic         = Mockery::mock( 'AWPCP_ListingsAPI' );
        $this->listings_collection    = Mockery::mock( 'AWPCP_ListingsCollection' );
        $this->categories_logic       = Mockery::mock( 'AWPCP_CategoriesLogic' );
        $this->categories_collection  = Mockery::mock( 'AWPCP_CategoriesCollection' );
        $this->roles_and_capabilities = Mockery::mock( 'AWPCP_RolesAndCapabilities' );
        $this->settings               = Mockery::mock( 'AWPCP_SettingsAPI' );
        $this->db                     = Mockery::mock( 'wpdb' );

        Functions\when( 'awpcp_get_plugin_pages_ids' )->justReturn( [] );
        Functions\when( 'awpcp_setup_uploads_dir' )->justReturn( [ $uploads_dir, null ] );
        Functions\when( 'esc_sql' )->justReturn( '' );
        Functions\when( 'delete_option' )->justReturn( true );
        Functions\when( 'wp_clear_scheduled_hook' )->justReturn( true );
        Functions\when( 'get_option' )->justReturn( [] );
        Functions\when( 'deactivate_plugins' )->justReturn( null );

        $this->categories_collection->shouldReceive( 'find_categories' )
            ->andReturn( $categories );

        $this->categories_logic->shouldReceive( 'delete_category_and_associated_listings' )
            ->once()
            ->with( $categories[0] );

        $this->listings_collection->shouldReceive( 'find_listings' )
            ->andReturn( $listings );

        $this->listings_logic->shouldReceive( 'delete_listing' )
            ->once()
            ->with( $listings[0] );

        $this->db->prefix   = 'prefix_';
        $this->db->options  = $this->db->prefix . 'options';
        $this->db->posts    = $this->db->prefix . 'posts';
        $this->db->usermeta = $this->db->prefix . 'usermeta';
        $this->db->postmeta = $this->db->prefix . 'postmeta';

        $this->db->shouldReceive( 'query' );
        $this->db->shouldReceive( 'get_col' )->andReturn( [] );
        $this->db->shouldReceive( 'prepare' )->andReturn( '' );
        $this->db->shouldReceive( 'get_blog_prefix' )->andReturn( 'something' );

        $this->settings->setting_name = 'setting_name';

        $this->roles_and_capabilities->shouldReceive( [
            'get_administrator_roles_names'               => [],
            'remove_administrator_capabilities_from_role' => null,
            'get_subscriber_roles_names'                  => [],
            'remove_subscriber_capabilities_from_role'    => null,
            'remove_moderator_role'                       => null,
        ] );

        $uninstaller = new AWPCP_Uninstaller(
            'another-wordpress-classifieds-plugin/awpcp.php',
            'awpcp_listing',
            $this->listings_logic,
            $this->listings_collection,
            $this->categories_logic,
            $this->categories_collection,
            $this->roles_and_capabilities,
            $this->settings,
            $this->db
        );

        // Execution.
        $uninstaller->uninstall();

        if ( file_exists( $uploads_dir ) ) {
            // phpcs:disable WordPress.VIP.FileSystemWritesDisallow.directory_rmdir
            rmdir( $uploads_dir );
            // phpcs:enable
        }
    }
}
