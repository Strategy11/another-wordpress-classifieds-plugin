<?php

class AWPCP_Test_Listing_Details_Form_Field extends AWPCP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();

        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        $this->template_renderer = Phake::mock( 'AWPCP_Template_Renderer' );
    }

    public function test_constructor() {
        $form_field = awpcp_listing_details_form_field( null );
        $this->assertInstanceOf( 'AWPCP_ListingDetailsFormField', $form_field );
    }

    public function test_render() {
        $listing = awpcp_tests_create_listing();
        $payment_term = Phake::mock( 'AWPCP_Fee' );

        Phake::when( $this->listing_renderer )->get_payment_term->thenReturn( $payment_term );

        $form_field = new AWPCP_ListingDetailsFormField(
            null,
            $this->listing_renderer,
            $this->payments,
            $this->template_renderer
        );
        $form_field->render( null, array(), $listing, '' );

        Phake::verify( $this->template_renderer )->render_template( Phake::anyParameters() );
    }
}
