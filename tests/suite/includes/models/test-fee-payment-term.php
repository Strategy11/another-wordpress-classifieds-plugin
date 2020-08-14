<?php

/**
 * @group core
 */
class AWPCP_TestFeePaymentTerm extends AWPCP_UnitTestCase {

    public function test_save_new_fee() {
        $fee = new AWPCP_Fee( array(
            'id' => '',
            'name' => 'Test Fee',
            'duration_amount' => 1,
            'duration_interval' => AWPCP_PaymentTerm::INTERVAL_DAY,
            'price' => '0',
            'credits' => '0',
            'images' => '0',
            'title_characters' => '0',
            'characters' => '0',
        ) );

        $result = $fee->save();

        $this->assertTrue( $result, 'save() operation returned a true value.');
        $this->assertTrue( is_numeric( $fee->id ), 'Fee ID is numeric.' );
        $this->assertTrue( $fee->id > 0, 'Fee ID is greater than zero' );
    }
}
