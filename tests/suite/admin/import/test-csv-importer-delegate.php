<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Import
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for CSV Importer Delegate.
 */
class AWPCP_Test_CSV_Importer_Delegate extends AWPCP_UnitTestCase {

    /**
     * Runs before every test.
     */
    public function setup() {
        parent::setup();

        $this->row_data = array(
            'title'         => 'Test Row',
            'details'       => 'Row Details',
            'contact_name'  => 'John Doe',
            'contact_email' => 'john.doe@example.com',
            'category_name' => 'Test Category',
            'username'      => 'johndoe',
            'start_date'    => '07/29/2016',
            'end_date'      => '08/29/2016',
        );

        $this->supported_columns = [
            'post_fields' => [
                'title'    => [
                    'name' => 'post_title',
                ],
                'details'  => [
                    'name' => 'post_content',
                ],
                'username' => [
                    'name' => 'post_author',
                ],
            ],
            'terms'       => [
                'category_name' => [
                    'name' => 'term_id',
                ],
            ],
            'metadata'    => [
                'contact_name'      => [
                    'name' => '_awpcp_contact_name',
                ],
                'contact_email'     => [
                    'name' => '_awpcp_contact_email',
                ],
                'start_date'        => [
                    'name' => '_awpcp_start_date',
                ],
                'end_date'          => [
                    'name' => '_awpcp_end_date',
                ],
                'payment_term_id'   => [
                    'name' => '_awpcp_payment_term_id',
                ],
                'payment_term_type' => [
                    'name' => '_awpcp_payment_term_type',
                ],
            ],
        ];

        $this->import_session    = Phake::mock( 'AWPCP_CSV_Import_Session' );
        $this->listings_payments = Mockery::mock( 'AWPCP_ListingsPayments' );
        $this->columns           = Mockery::mock( 'AWPCP_CSVImporterColumns' );
        $this->mime_types        = Phake::mock( 'AWPCP_MimeTypes' );
        $this->categories_logic  = Phake::mock( 'AWPCP_Categories_Logic' );
        $this->categories        = Phake::mock( 'AWPCP_Categories_Collection' );
        $this->listings_logic    = Phake::mock( 'AWPCP_ListingsAPI' );
        $this->listings          = Mockery::mock( 'AWPCP_ListingsCollection' );
        $this->payments          = Mockery::mock( 'AWPCP_PaymentsAPI' );
        $this->media_manager     = Mockery::mock( 'AWPCP_MediaManager' );

        Phake::when( $this->import_session )->is_test_mode_enabled->thenReturn( false );
        Phake::when( $this->import_session )->get_param( 'create_missing_categories' )->thenReturn( true );
        Phake::when( $this->import_session )->get_param( 'date_format' )->thenReturn( 'us_date' );
        Phake::when( $this->import_session )->get_param( 'date_separator' )->thenReturn( '/' );
        Phake::when( $this->import_session )->get_param( 'time_separator' )->thenReturn( ':' );
        Phake::when( $this->import_session )->get_param( 'category_separator' )->thenReturn( ';' );

        $this->columns->shouldReceive( 'get_supported_columns' )->andReturn( $this->supported_columns );
    }

    /**
     * @large
     */
    public function test_import_without_images() {
        // Execution.
        $result = $this->import_row();

        Phake::verify( $this->listings_logic )->create_listing( Phake::capture( $listing_data ) );

        $this->assertTrue( isset( $listing_data['post_fields'] ) );

        Phake::verify( $this->listings_logic )->update_listing(
            Phake::capture( $listing ),
            Phake::capture( $listing_data )
        );

        // Because the mocked version of create_listing() does not return anything.
        $this->assertNull( $listing );

        $this->assertEquals( AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_REQUIRED, $listing_data['metadata']['_awpcp_payment_status'] );
        $this->assertEquals( '2016-07-29 00:00:00', $listing_data['metadata']['_awpcp_start_date'] );
        $this->assertEquals( '2016-08-29 00:00:00', $listing_data['metadata']['_awpcp_end_date'] );

        $this->assertTrue( is_array( $result->messages ) && ! empty( $result->messages ) );
    }

    /**
     * @since 4.0.0
     */
    private function import_row() {
        $user = (object) [
            'ID' => wp_rand() + 1,
        ];

        Functions\expect( 'get_user_by' )
            ->with( 'login', $this->row_data['username'] )
            ->andReturn( null );

        Functions\expect( 'get_user_by' )
            ->with( 'id', $user->ID )
            ->andReturn( $user );

        Functions\when( 'wp_generate_password' )->justReturn( 'a secure password' );
        Functions\when( 'wp_create_user' )->justReturn( $user->ID );
        Functions\when( 'is_wp_error' )->justReturn( false );

        return $this->get_test_subject()->import_row( $this->row_data );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_CSV_Importer_Delegate(
            $this->import_session,
            $this->columns,
            $this->listings_payments,
            $this->mime_types,
            $this->categories_logic,
            $this->categories,
            $this->listings_logic,
            $this->listings,
            $this->payments,
            $this->media_manager
        );
    }

    /**
     * Test image are imported correctly.
     */
    public function test_import_row_with_images() {
        $this->row_data['images'] = 'image-1.jpg;image-2.png';

        $images_directory = WP_TESTS_DATA_DIR . '/csv-importer/images';

        Phake::when( $this->import_session )->get_images_directory->thenReturn( $images_directory );

        $this->media_manager->shouldReceive( 'validate_file' )->twice();
        $this->media_manager->shouldReceive( 'add_file' )->twice();

        // Execution.
        $this->import_row();
    }

    /**
     * @expectedException AWPCP_CSV_Importer_Exception
     * @since 4.0.0
     */
    public function test_import_row_with_invalid_images() {
        $this->row_data['images'] = 'invalid-image.jpg';

        $images_directory = WP_TESTS_DATA_DIR . '/csv-importer/images';

        Phake::when( $this->import_session )->get_images_directory->thenReturn( $images_directory );

        $this->media_manager->shouldReceive( 'validate_file' )
            ->andThrow( new AWPCP_Exception() );

        // Execution.
        $this->import_row();
    }

    /**
     * @since 4.0.0
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) $listing
     */
    public function test_import_row_with_payment_term() {
        $payment_term = (object) [
            'id'   => wp_rand(),
            'type' => 'fee',
        ];

        $this->row_data['payment_term_id']   = $payment_term->id;
        $this->row_data['payment_term_type'] = $payment_term->type;

        $this->payments->shouldReceive( 'get_payment_term' )
            ->with( $payment_term->id, $payment_term->type )
            ->andReturn( $payment_term );

        $this->listings_payments->shouldReceive( 'update_listing_payment_term' )
            ->withArgs(
                /**
                 * $listing is null here because update_listing() tries to load
                 * the recently imported post from the database, even though one
                 * wasn't really created.
                 */
                function( $listing, $new_payment_term ) use ( $payment_term ) {
                    return $new_payment_term === $payment_term;
                }
            );

        $this->import_row();
    }
}
