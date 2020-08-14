<?php

/**
 * @group core
 */
class AWPCP_TestBasePage extends AWPCP_UnitTestCase {

    public function test_proper_step_is_executed() {
        $request = $this->mock_request_with_method_and_step( 'POST', 'first' );

        $step = $this->createPartialMock( 'stdClass', array( 'post' ) );
        $step->expects( $this->once() )->method( 'post' );

        $page = new AWPCP_BasePage( array( 'first' => $step ), $request );
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

    public function test_next_step_is_executed() {
        $request = $this->mock_request_with_method_and_step( 'POST', 'first' );

        $first_step = $this->createPartialMock( 'stdClass', array( 'post', 'get' ) );
        $first_step->expects( $this->once() )->method( 'post' );

        $second_step = $this->createPartialMock( 'stdClass', array( 'post', 'get' ) );
        $second_step->expects( $this->once() )->method( 'get' );

        $steps = array( 'first' => $first_step, 'second' => $second_step );
        $page = new AWPCP_BasePage( $steps, $request );
        $page->set_next_step( 'second' );
        $page->dispatch();
    }
}
