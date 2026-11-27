<?php
/**
 * @package AWPCP\Tests\Admin
 */

use Brain\Monkey\Functions;

/**
 * Tests for the CSV export download handler, the only gate in front of exported listings.
 */
class AWPCP_ExportListingsAdminPageTest extends AWPCP_UnitTestCase {

    const EXPORT_ID = 'aB3dE5gH7jK9mN1pQ3sT5vW7yZ9bC1dF';

    /**
     * @var string
     */
    private $uploads_dir;

    /**
     * @var string
     */
    private $export_dir;

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->uploads_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'awpcp-tests-' . uniqid();
        $this->export_dir  = $this->uploads_dir . '/awpcp-csv-exports/' . self::EXPORT_ID;

        mkdir( $this->export_dir, 0777, true );

        Functions\stubEscapeFunctions();
        Functions\stubTranslationFunctions();
        Functions\when( 'wp_upload_dir' )->justReturn(
            [
                'basedir' => $this->uploads_dir,
                'error'   => false,
            ]
        );
        Functions\when( 'awpcp_get_wp_filesystem' )->justReturn( $this->get_filesystem() );
        Functions\when( 'wp_clear_scheduled_hook' )->justReturn( true );
        Functions\when( 'wp_die' )->alias(
            function ( $message, $title = '', $args = [] ) {
                throw new RuntimeException( $message, $args['response'] );
            }
        );
    }

    /**
     * @since x.x
     */
    public function tearDown(): void {
        $this->get_filesystem()->rmdir( $this->uploads_dir, true );

        parent::tearDown();
    }

    /**
     * @since x.x
     */
    public function test_download_with_invalid_nonce_dies_before_reading_the_file() {
        $this->create_export_file();
        $this->request_export( self::EXPORT_ID, true );

        Functions\expect( 'check_ajax_referer' )
            ->once()
            ->with( 'awpcp-csv-export-download' )
            ->andReturnUsing(
                function () {
                    throw new RuntimeException( '-1', 403 );
                }
            );

        $page = $this->get_test_subject();

        $this->assert_download_dies_with( $page, 403 );
        $this->assertSame( [], $page->sent_files );
        $this->assertFileExists( $this->export_dir . '/export.csv' );
    }

    /**
     * @since x.x
     */
    public function test_download_by_non_admin_is_forbidden_and_keeps_the_export() {
        $this->create_export_file();
        $this->accept_nonce();
        $this->request_export( self::EXPORT_ID, false );

        $page = $this->get_test_subject();

        $this->assert_download_dies_with( $page, 403 );
        $this->assertSame( [], $page->sent_files );
        $this->assertDirectoryExists( $this->export_dir );
    }

    /**
     * @since x.x
     */
    public function test_download_with_malformed_export_id_is_not_found() {
        $this->create_export_file();
        $this->accept_nonce();
        $this->request_export( '../../wp-config', true );

        $page = $this->get_test_subject();

        $this->assert_download_dies_with( $page, 404 );
        $this->assertSame( [], $page->sent_files );
        $this->assertDirectoryExists( $this->export_dir );
    }

    /**
     * @since x.x
     */
    public function test_download_of_export_without_file_is_not_found() {
        $this->accept_nonce();
        $this->request_export( self::EXPORT_ID, true );

        $page = $this->get_test_subject();

        $this->assert_download_dies_with( $page, 404 );
        $this->assertSame( [], $page->sent_files );
    }

    /**
     * @since x.x
     */
    public function test_download_sends_the_file_and_deletes_the_export() {
        $this->create_export_file();
        $this->accept_nonce();
        $this->request_export( self::EXPORT_ID, true );

        $page = $this->get_test_subject();

        $page->download();

        $this->assertSame( [ "id,title\n1,Bike\n" ], $page->sent_files );
        $this->assertSame( 1, $page->ended_requests );
        $this->assertDirectoryDoesNotExist( $this->export_dir );
    }

    /**
     * @since x.x
     *
     * @param string $export_id Export ID sent in the request.
     * @param bool   $is_admin  Whether the current user is an administrator.
     */
    private function request_export( $export_id, $is_admin ) {
        Functions\when( 'awpcp_current_user_is_admin' )->justReturn( $is_admin );
        Functions\when( 'awpcp_get_var' )->justReturn( $export_id );
    }

    /**
     * @since x.x
     */
    private function accept_nonce() {
        Functions\expect( 'check_ajax_referer' )
            ->once()
            ->with( 'awpcp-csv-export-download' )
            ->andReturn( 1 );
    }

    /**
     * @since x.x
     */
    private function create_export_file() {
        file_put_contents( $this->export_dir . '/export.csv', "id,title\n1,Bike\n" );
    }

    /**
     * @since x.x
     *
     * @param AWPCP_ExportListingsAdminPage $page          The page under test.
     * @param int                           $response_code Expected HTTP status code.
     */
    private function assert_download_dies_with( $page, $response_code ) {
        try {
            $page->download();
        } catch ( RuntimeException $e ) {
            $this->assertSame( $response_code, $e->getCode() );
            $this->assertSame( 0, $page->ended_requests );

            return;
        }

        $this->fail( 'The download request was expected to die.' );
    }

    /**
     * Returns the admin page with file streaming and exit replaced by recorders.
     *
     * @since x.x
     *
     * @return AWPCP_ExportListingsAdminPage
     */
    private function get_test_subject() {
        return new class() extends AWPCP_ExportListingsAdminPage {

            public $sent_files = [];

            public $ended_requests = 0;

            protected function send_file( $file_path ) {
                $this->sent_files[] = file_get_contents( $file_path );
            }

            protected function end_request() {
                ++$this->ended_requests;
            }
        };
    }

    /**
     * Returns the subset of WP_Filesystem the exporter uses, backed by the real disk.
     *
     * @since x.x
     *
     * @return object
     */
    private function get_filesystem() {
        return new class() {

            public function exists( $path ) {
                return file_exists( $path );
            }

            public function is_dir( $path ) {
                return is_dir( $path );
            }

            public function rmdir( $path, $recursive = false ) {
                if ( ! is_dir( $path ) ) {
                    return false;
                }

                foreach ( array_diff( scandir( $path ), [ '.', '..' ] ) as $item ) {
                    $item_path = $path . DIRECTORY_SEPARATOR . $item;

                    if ( is_dir( $item_path ) ) {
                        $this->rmdir( $item_path, $recursive );
                    } else {
                        unlink( $item_path );
                    }
                }

                return rmdir( $path );
            }
        };
    }
}
