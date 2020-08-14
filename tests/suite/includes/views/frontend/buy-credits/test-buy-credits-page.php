<?php

/**
 * @group core
 */
class AWPCP_TestBuyCreditsPage extends AWPCP_UnitTestCase {

    /**
     * TODO: this test was copied from TestBasePage class. Is there are
     * better way to apply test sub classes of a class that has it
     * own set of tests.
     *
     * See: http://stackoverflow.com/questions/7456123/how-to-unit-test-subclasses
     */
    public function test_proper_step_is_executed() {
        $request = $this->mock_request_with_method_and_step( 'POST', 'first' );

        $step = $this->createPartialMock( 'stdClass', array( 'post' ) );
        $step->expects( $this->once() )->method( 'post' );

        $page = new AWPCP_BuyCreditsPage( array( 'first' => $step ), $request );
        $page->skip_next_step();
        $page->dispatch();
    }

    private function mock_request_with_method_and_step( $method, $step_name ) {
        $request = $this->createPartialMock( 'AWPCP_Request', array( 'method', 'param' ) );

        $request->expects( $this->any() )
                ->method( 'method' )
                ->will( $this->returnValue( $method ) );
        $request->expects( $this->any() )
                ->method( 'param' )
                ->will( $this->returnValue( $step_name ) );

        return $request;
    }

    public function test_get_transaction_creating_a_new_transaction() {
        $user_id = $this->login_as_subscriber();

        $page = new AWPCP_BuyCreditsPage( array(), new AWPCP_Request() );
        $transaction = $page->get_transaction();

        $this->assertNotNull( $transaction );
        $this->assertEquals( 'add-credit', $transaction->get( 'context' ) );
        $this->assertEquals( $user_id, $transaction->user_id );
    }

    public function test_get_transaction_without_creating_a_new_transaction() {
        $page = new AWPCP_BuyCreditsPage( array(), new AWPCP_Request() );
        $transaction = $page->get_transaction( false );

        $this->assertNull( $transaction );
    }

    public function test_get_transaction_returns_existing_transaction() {
        $user_id = $this->login_as_subscriber();
        $transaction_id = $this->create_payment_transaction();
        $request = $this->mock_request_with_transaction_id( $transaction_id );

        $page = new AWPCP_BuyCreditsPage( array(), $request );
        $transaction = $page->get_transaction();

        $this->assertNotNull( $transaction );
        $this->assertEquals( $transaction_id, $transaction->id );
        $this->assertEquals( 'add-credit', $transaction->get( 'context' ) );
        $this->assertEquals( $user_id, $transaction->user_id );
    }

    private function create_payment_transaction() {
        $transaction = AWPCP_Payment_Transaction::find_or_create( '' );
        $transaction->save();

        return $transaction->id;
    }

    private function mock_request_with_transaction_id( $transaction_id ) {
        $request = $this->createPartialMock( 'AWPCP_Request', array( 'param' ) );
        $request->expects( $this->once() )
                ->method( 'param' )
                ->will( $this->returnValue( $transaction_id ) );

        return $request;
    }

    public function test_admin_users_are_not_allowed_to_buy_credits() {
        $this->login_as_administrator();
        $this->pause_filter( 'awpcp_menu_items' );

        $step = $this->createPartialMock( 'stdClass', array( 'get', 'post' ) );
        $step->expects( $this->never() )->method( 'get' );
        $step->expects( $this->never() )->method( 'post' );

        $page = new AWPCP_BuyCreditsPage( array( 'fake' => $step ), new AWPCP_Request() );

        $this->assertEquals( array(), $page->errors );

        $page->dispatch();

        $this->assertTrue( ! empty( $page->errors ) );
    }
}
