<?php

class Test_Listings_Table extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->page = Phake::mock( 'AWPCP_Admin_Listings' );
        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
    }

    public function test_constructor() {
        $table = awpcp_listings_table( null, array( 'screen' => 'whatever' ) );
        $this->assertInstanceOf( 'AWPCP_Listings_Table', $table );
    }

    public function test_column_title() {
        $listing = awpcp_tests_create_listing();

        Phake::when( $this->listing_renderer )->get_listing_title->thenReturn( $listing->post_title );

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $title = $table->column_title( $listing );

        Phake::verify( $this->page )->url( Phake::capture( $params ) );

        $this->assertContains( $listing->post_title, $title );
        $this->assertEquals( $listing->ID, $params['id'] );
    }

    public function test_column_access_key() {
        $listing = awpcp_tests_create_listing();

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $access_key = $table->column_access_key( $listing );

        Phake::verify( $this->listing_renderer )->get_access_key( $listing );
    }

    public function test_column_start_date() {
        $listing = awpcp_tests_create_listing();

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $access_key = $table->column_start_date( $listing );

        Phake::verify( $this->listing_renderer )->get_start_date( $listing );
    }

    public function test_column_end_date() {
        $listing = awpcp_tests_create_listing();

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $access_key = $table->column_end_date( $listing );

        Phake::verify( $this->listing_renderer )->get_end_date( $listing );
    }

    public function test_column_renewed_date() {
        $listing = awpcp_tests_create_listing();

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $access_key = $table->column_renewed_date( $listing );

        Phake::verify( $this->listing_renderer )->get_renewed_date_formatted( $listing );
    }

    public function test_column_status() {
        $listing = awpcp_tests_create_listing();

        Phake::when( $this->listing_renderer )->is_verified->thenReturn( false );

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $status = $table->column_status( $listing );

        Phake::verify( $this->listing_renderer )->is_verified( $listing );
        Phake::verify( $this->listing_renderer )->is_disabled( $listing );
        Phake::verify( $this->page )->url( Phake::capture( $params ) );

        $this->assertEquals( $listing->ID, $params['id'] );
    }

    public function test_column_payment_term() {
        $listing = awpcp_tests_create_listing();

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $content = $table->column_payment_term( $listing );

        Phake::verify( $this->listing_renderer )->get_payment_term( $listing );
    }

    public function test_column_payment_status() {
        $listing = awpcp_tests_create_listing();
        $payment_status = 'Unpaid';

        Phake::when( $this->listing_renderer )->get_payment_status->thenReturn( $payment_status );

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $content = $table->column_payment_status( $listing );

        Phake::verify( $this->listing_renderer )->get_payment_status_formatted( $listing );
        Phake::verify( $this->page )->url( Phake::capture( $params ) );

        $this->assertEquals( $listing->ID, $params['id'] );
    }

    public function test_column_owner() {
        $listing = awpcp_tests_create_listing();

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $content = $table->column_owner( $listing );

        Phake::verify( $this->listing_renderer )->get_user( $listing );
    }

    public function test_cb_column() {
        $listing = awpcp_tests_create_listing();

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        $content = $table->column_cb( $listing );

        $this->assertContains( (string) $listing->ID , $content );
    }

    public function test_single_row() {
        $listing = awpcp_tests_create_listing();

        $table = new AWPCP_Listings_Table(
            $this->page,
            array( 'screen' => 'whatever' ),
            null,
            $this->listing_renderer,
            null
        );

        ob_start();
        $table->single_row( $listing );
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertContains( 'data-id="' . $listing->ID . '"', $content );
        $this->assertContains( 'id="awpcp-ad-' . $listing->ID . '"', $content );
    }
}
