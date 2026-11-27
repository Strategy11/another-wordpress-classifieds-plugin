<?php
/**
 * @package AWPCP\Tests\Admin
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for the CSV exporter working directory handling.
 */
class AWPCP_CSVExporterTest extends AWPCP_UnitTestCase {

    const UPLOADS_DIR = '/var/www/wp-content/uploads';

    const EXPORT_ID = 'aB3dE5gH7jK9mN1pQ3sT5vW7yZ9bC1dF';

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        Functions\when( 'wp_upload_dir' )->justReturn(
            [
                'basedir' => self::UPLOADS_DIR,
                'baseurl' => 'https://example.org/wp-content/uploads',
                'error'   => false,
            ]
        );
    }

    /**
     * @since x.x
     */
    public function test_get_export_path_accepts_random_export_id() {
        $expected = self::UPLOADS_DIR . '/awpcp-csv-exports/' . self::EXPORT_ID . '/';

        $this->assertSame( $expected, AWPCP_CSVExporter::get_export_path( self::EXPORT_ID ) );
    }

    /**
     * @dataProvider invalid_export_ids_provider
     *
     * @since x.x
     *
     * @param mixed $export_id Export ID.
     */
    public function test_get_export_path_rejects_invalid_export_ids( $export_id ) {
        $this->assertSame( '', AWPCP_CSVExporter::get_export_path( $export_id ) );
    }

    /**
     * @since x.x
     */
    public function invalid_export_ids_provider() {
        return [
            'parent directory'   => [ '..' ],
            'path traversal'     => [ '../../../wp-content/plugins' ],
            'legacy uniqid name' => [ '6b0935ae5eea2' ],
            'too short'          => [ substr( self::EXPORT_ID, 1 ) ],
            'trailing newline'   => [ self::EXPORT_ID . "\n" ],
            'empty'              => [ '' ],
            'array'              => [ [ self::EXPORT_ID ] ],
            'null'               => [ null ],
        ];
    }

    /**
     * @dataProvider forged_working_dirs_provider
     *
     * @since x.x
     *
     * @param mixed $workingdir Working directory sent in the export state.
     */
    public function test_from_state_rejects_working_dirs_outside_exports_dir( $workingdir ) {
        Functions\expect( 'awpcp_get_wp_filesystem' )->never();

        $this->expectException( AWPCP_Exception::class );

        AWPCP_CSVExporter::from_state(
            [
                'settings'   => [],
                'workingdir' => $workingdir,
                'listings'   => [],
                'exported'   => 0,
            ]
        );
    }

    /**
     * @since x.x
     */
    public function forged_working_dirs_provider() {
        return [
            'plugins directory' => [ '/var/www/wp-content/plugins/' ],
            'relative parent'   => [ self::UPLOADS_DIR . '/awpcp-csv-exports/../../' ],
            'legacy uniqid dir' => [ self::UPLOADS_DIR . '/awpcp-csv-exports/6b0935ae5eea2/' ],
            'array'             => [ [ '/var/www/' ] ],
        ];
    }

    /**
     * @since x.x
     */
    public function test_delete_export_removes_working_dir_and_scheduled_cleanup() {
        $workingdir    = self::UPLOADS_DIR . '/awpcp-csv-exports/' . self::EXPORT_ID . '/';
        $wp_filesystem = Mockery::mock( 'WP_Filesystem_Direct' );

        $wp_filesystem->shouldReceive( 'is_dir' )->with( $workingdir )->andReturn( true );
        $wp_filesystem->shouldReceive( 'rmdir' )->once()->with( $workingdir, true )->andReturn( true );

        Functions\when( 'awpcp_get_wp_filesystem' )->justReturn( $wp_filesystem );
        Functions\expect( 'wp_clear_scheduled_hook' )->once()->with( 'awpcp_csv_export_cleanup', [ self::EXPORT_ID ] );

        AWPCP_CSVExporter::delete_export( self::EXPORT_ID );
    }

    /**
     * @since x.x
     */
    public function test_delete_export_ignores_invalid_export_ids() {
        $wp_filesystem = Mockery::mock( 'WP_Filesystem_Direct' );

        $wp_filesystem->shouldNotReceive( 'rmdir' );

        Functions\when( 'awpcp_get_wp_filesystem' )->justReturn( $wp_filesystem );
        Functions\expect( 'wp_clear_scheduled_hook' )->never();

        AWPCP_CSVExporter::delete_export( '../../plugins' );
    }

    /**
     * @since x.x
     */
    public function test_get_file_url_points_to_download_handler() {
        $reflection = new ReflectionClass( 'AWPCP_CSVExporter' );
        $exporter   = $reflection->newInstanceWithoutConstructor();
        $property   = $reflection->getProperty( 'workingdir' );

        $property->setAccessible( true );
        $property->setValue( $exporter, self::UPLOADS_DIR . '/awpcp-csv-exports/' . self::EXPORT_ID . '/' );

        Functions\when( 'admin_url' )->justReturn( 'https://example.org/wp-admin/admin-ajax.php' );
        Functions\when( 'wp_create_nonce' )->justReturn( 'nonce' );
        Functions\when( 'add_query_arg' )->alias(
            function ( $args, $url ) {
                return $url . '?' . http_build_query( $args );
            }
        );

        $url = $exporter->get_file_url();

        $this->assertStringStartsWith( 'https://example.org/wp-admin/admin-ajax.php?', $url );
        $this->assertStringContainsString( 'action=awpcp-csv-export-download', $url );
        $this->assertStringContainsString( 'export=' . self::EXPORT_ID, $url );
        $this->assertStringNotContainsString( 'uploads', $url );
    }
}
