<?php

class AWPCP_Custom_Request_Handler {
    public function ajax() {}
}

class AWPCP_Test_Ajax_Request_Handler extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->routes = Phake::mock( 'AWPCP_Routes' );
        $this->request = Phake::mock( 'AWPCP_Request' );

        $this->request_handler = Phake::mock( 'AWPCP_Custom_Request_Handler' );
        $this->ajax_actions = array(
            'test-action' => (object) array(
                'handler' => array( $this, 'get_request_handler_instance' ),
            ),
        );

        Phake::when( $this->request )->param( 'action' )->thenReturn( 'test-action' );
    }

    public function test_handle_anonymous_ajax_request() {
        Phake::when( $this->routes )->get_anonymous_ajax_actions->thenReturn( $this->ajax_actions );

        $handler = new AWPCP_Ajax_Request_Handler( $this->routes, $this->request );
        $handler->handle_anonymous_ajax_request();

        Phake::verify( $this->request_handler )->ajax();
    }

    public function get_request_handler_instance() {
        return $this->request_handler;
    }

    public function test_handle_private_ajax_request() {
        Phake::when( $this->routes )->get_private_ajax_actions->thenReturn( $this->ajax_actions );

        $handler = new AWPCP_Ajax_Request_Handler( $this->routes, $this->request );
        $handler->handle_private_ajax_request();

        Phake::verify( $this->request_handler )->ajax();
    }
}
