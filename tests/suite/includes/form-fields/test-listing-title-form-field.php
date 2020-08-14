<?php

class AWPCP_Test_Listing_Title_Form_Field extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->listing_renderer = Phake::mock( 'AWPCP_ListingRenderer' );
        $this->payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        $this->template_renderer = Phake::mock( 'AWPCP_Template_Renderer' );
    }

    public function test_constructor() {
        $form_field = awpcp_listing_title_form_field( null );
        $this->assertInstanceOf( 'AWPCP_ListingTitleFormField', $form_field );
    }

    public function test_render() {
        $listing = awpcp_tests_create_listing();
        $payment_term = Phake::mock( 'AWPCP_Fee' );

        Phake::when( $this->listing_renderer )->get_payment_term->thenReturn( $payment_term );

        $form_field = new AWPCP_ListingTitleFormField(
            null,
            $this->listing_renderer,
            $this->payments,
            $this->template_renderer
        );
        $form_field->render( null, array(), $listing, '' );

        Phake::verify( $this->template_renderer )->render_template( Phake::anyParameters() );
    }
}
