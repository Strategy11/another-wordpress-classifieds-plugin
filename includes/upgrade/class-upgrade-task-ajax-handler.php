<?php
/**
 * @package AWPCP\Upgrade
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AWPCP_Upgrade_Task_Ajax_Handler extends AWPCP_AjaxHandler {

    /**
     * @var object
     */
    private $task_controller;

    public function __construct( $task_controller, $null, $response ) {
        parent::__construct( $response );

        $this->task_controller = $task_controller;
    }

    /**
     * Runs the requested upgrade task after checking the user's capability
     * and the request nonce.
     *
     * @since 4.0.0
     * @since x.x Requires the moderator capability and a valid nonce.
     */
    public function ajax() {
        if ( ! awpcp_current_user_is_moderator() || ! check_ajax_referer( 'awpcp-upgrade-task', 'nonce', false ) ) {
            return $this->error_response( __( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ) );
        }

        $task_slug = awpcp_get_var( array( 'param' => 'action' ) );
        $context   = awpcp_get_var( array( 'param' => 'context' ) );

        try {
            list( $records_count, $records_left ) = $this->task_controller->run_task( $task_slug, $context );
        } catch ( AWPCP_Exception $e ) {
            return $this->error_response( $e->getMessage() );
        }

        return $this->progress_response( $records_count, $records_left );
    }
}
