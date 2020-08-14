<?php

class AWPCP_Test_Payment_Term_Fee extends AWPCP_UnitTestCase {

    public function test_transfer_ads_to() {
        $payment_term = new AWPCP_Fee( array( 'name' => 'The Payment Term' ) );
        $payment_term->save();

        $other_payment_term = new AWPCP_Fee( array( 'name' => 'Other Payment Term', ) );
        $other_payment_term->save();

        $listing = awpcp_tests_create_listing();
        $errors = array();

        update_post_meta( $listing->ID, '_awpcp_payment_term_id', $payment_term->id );

        $result = $payment_term->transfer_ads_to( $other_payment_term->id, $errors );

        $this->assertTrue( $result );
        $this->assertEquals( array(), $errors );
        $this->assertEquals( $other_payment_term->id, get_post_meta( $listing->ID, '_awpcp_payment_term_id', true ) );
    }
}
