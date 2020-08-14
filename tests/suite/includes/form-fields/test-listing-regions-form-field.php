<?php

class AWPCP_Test_Listing_Regions_Form_Field extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
    }

    public function test_constructor() {
        $form_field = awpcp_listing_regions_form_field( null );
        $this->assertInstanceOf( 'AWPCP_ListingRegionsFormField', $form_field );
    }

    public function test_render() {
        $listing = awpcp_tests_create_listing();

        $form_field = new AWPCP_ListingRegionsFormField(
            null,
            $this->listing_renderer,
            $this->payments,
            $this->settings
        );

        $form_field->render( null, array(), $listing, 'context?' );

        $this->assertTrue( true );
    }
}
