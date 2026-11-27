<?php
/**
 * @package AWPCP\Tests\Admin\Pointers
 */

use Brain\Monkey\Functions;

/**
 * Tests for the Drip autoresponder AJAX handler.
 */
class AWPCP_DripAutoresponderAjaxHandlerTest extends AWPCP_UnitTestCase {

    /**
     * @var mixed
     */
    private $settings;

    /**
     * @var mixed
     */
    private $request;

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->settings = Mockery::mock( 'AWPCP_Settings_API' );
        $this->request  = Mockery::mock( 'AWPCP_Request' );

        $this->request->shouldReceive( 'post' )->with( 'nonce' )->andReturn( 'nonce' );
        $this->request->shouldReceive( 'post' )->with( 'action' )->andReturn( 'awpcp-autoresponder-dismissed' );

        $this->redefine(
            'AWPCP_AjaxHandler::flush',
            function ( $response ) {
                return $response;
            }
        );
    }

    /**
     * @since x.x
     */
    public function test_ajax_refuses_users_who_cannot_manage_awpcp() {
        $this->login_as_subscriber();
        Functions\when( 'wp_verify_nonce' )->justReturn( 1 );

        $this->settings->shouldReceive( 'update_option' )->never();

        $response = $this->get_test_subject()->ajax();

        $this->assertSame( 'error', $response['status'] );
    }

    /**
     * @since x.x
     */
    public function test_ajax_refuses_requests_with_an_invalid_nonce() {
        $this->login_as_administrator();
        Functions\when( 'wp_verify_nonce' )->justReturn( false );

        $this->settings->shouldReceive( 'update_option' )->never();

        $response = $this->get_test_subject()->ajax();

        $this->assertSame( 'error', $response['status'] );
    }

    /**
     * @since x.x
     */
    public function test_ajax_lets_administrators_dismiss_the_autoresponder() {
        $this->login_as_administrator();
        Functions\when( 'wp_verify_nonce' )->justReturn( 1 );

        $this->settings->shouldReceive( 'update_option' )
            ->once()
            ->with( 'show-drip-autoresponder', false, true );

        $response = $this->get_test_subject()->ajax();

        $this->assertSame( 'ok', $response['status'] );
    }

    /**
     * @since x.x
     */
    private function get_test_subject() {
        return new AWPCP_DripAutoresponderAjaxHandler( $this->settings, $this->request, null );
    }
}
