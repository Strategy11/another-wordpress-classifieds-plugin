<?php
/**
 * @package AWPCP\Tests\Upgrade
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for the upgrade task AJAX handler.
 */
class AWPCP_Test_Upgrade_Task_Ajax_Handler extends AWPCP_UnitTestCase {

    /**
     * @var Mockery\MockInterface
     */
    private $task_controller;

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->task_controller = Mockery::mock( 'AWPCP_Upgrade_Task_Controller' );

        Functions\when( 'awpcp_get_var' )->alias(
            function ( $args ) {
                return 'action' === $args['param'] ? 'awpcp-test-task' : 'plugin';
            }
        );
    }

    /**
     * @since x.x
     *
     * @return array
     */
    public function unauthorized_request_provider() {
        return array(
            'user without moderator capability' => array( false, true ),
            'moderator without a valid nonce'    => array( true, false ),
        );
    }

    /**
     * @dataProvider unauthorized_request_provider
     *
     * @since x.x
     *
     * @param bool $is_moderator Whether the current user is a moderator.
     * @param bool $valid_nonce  Whether the request nonce is valid.
     */
    public function test_ajax_does_not_run_task_for_unauthorized_requests( $is_moderator, $valid_nonce ) {
        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( $is_moderator );
        Functions\when( 'check_ajax_referer' )->justReturn( $valid_nonce );

        $this->task_controller->shouldNotReceive( 'run_task' );

        $handler = $this->get_handler();
        $handler->shouldReceive( 'error_response' )->once()->andReturn( 'error' );

        $this->assertSame( 'error', $handler->ajax() );
    }

    /**
     * @since x.x
     */
    public function test_ajax_runs_task_for_moderator_with_valid_nonce() {
        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( true );
        Functions\expect( 'check_ajax_referer' )
            ->once()
            ->with( 'awpcp-upgrade-task', 'nonce', false )
            ->andReturn( 1 );

        $this->task_controller->shouldReceive( 'run_task' )
            ->once()
            ->with( 'awpcp-test-task', 'plugin' )
            ->andReturn( array( 10, 4 ) );

        $handler = $this->get_handler();
        $handler->shouldReceive( 'progress_response' )->once()->with( 10, 4 )->andReturn( 'progress' );

        $this->assertSame( 'progress', $handler->ajax() );
    }

    /**
     * @since x.x
     *
     * @return Mockery\MockInterface
     */
    private function get_handler() {
        $response = Mockery::mock( 'AWPCP_AjaxResponse' );

        return Mockery::mock( 'AWPCP_Upgrade_Task_Ajax_Handler', array( $this->task_controller, null, $response ) )
            ->makePartial();
    }
}
