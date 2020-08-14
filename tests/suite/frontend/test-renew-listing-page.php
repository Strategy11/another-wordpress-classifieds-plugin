<?php

class AWPCP_Test_Renew_Listing_Page extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->listings = Phake::mock( 'AWPCP_ListingsCollection' );
        $this->payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        $this->template_renderer = Phake::mock( 'AWPCP_Template_Renderer' );
        $this->request = Phake::mock( 'AWPCP_Request' );
    }

    public function test_get_ad() {
        $page = new AWPCP_RenewAdPage(
            'awpcp-renew-ad',
            null,
            null,
            null,
            null,
            null,
            null,
            $this->listings,
            null,
            null,
            null,
            $this->request
        );

        $listing = $page->get_ad();

        // verify that it gets the ID of the listing from the 'id' parameter in the admin.
        Phake::verify( $this->request )->param( 'ad_id' );
    }

    public function test_dispatch() {
        $listing = (object) array( 'ID' => rand() + 1 );
        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );

        Phake::when( $this->listings )->get->thenReturn( $listing );
        Phake::when( $this->listing_renderer )->has_expired->thenReturn( true );
        Phake::when( $this->request )->param( 'awpcprah' )->thenReturn( awpcp_get_renew_ad_hash( $listing->ID ) );
        Phake::when( $this->payments )->get_transaction->thenReturn( $transaction );

        $page = new AWPCP_RenewAdPage(
            'awpcp-renew-ad',
            null,
            null,
            null,
            null,
            $this->listing_renderer,
            null,
            $this->listings,
            $this->payments,
            $this->template_renderer,
            null,
            $this->request
        );

        $page->dispatch();

        $this->assertTrue( true );
    }

    public function test_verify_renew_ad_hash() {
        $listing = (object) array( 'ID' => rand() + 1 );

        Phake::when( $this->request )->param( 'awpcprah' )->thenReturn( awpcp_get_renew_ad_hash( $listing->ID ) );

        $page = new AWPCP_RenewAdPage(
            'awpcp-renew-ad',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $this->request
        );

        $this->assertTrue( $page->verify_renew_ad_hash( $listing ) );
    }

    public function test_render_finish_step() {
        $listing = (object) array( 'ID' => rand() + 1 );

        $page = new AWPCP_RenewAdPage(
            'awpcp-renew-ad',
            null,
            null,
            null,
            null,
            $this->listing_renderer,
            null,
            null,
            null,
            $this->template_renderer,
            null,
            $this->request
        );

        $page->render_finish_step( $listing );

        $this->assertTrue( true );
    }
}
