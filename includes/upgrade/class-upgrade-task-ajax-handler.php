<?php

class AWPCP_Upgrade_Task_Ajax_Handler extends AWPCP_AjaxHandler {

    private $tasks_manager;
    private $task_handler_factory;
    private $request;

    public function __construct( $tasks_manager, $task_handler_factory, $request, $response ) {
        parent::__construct( $response );

        $this->tasks_manager = $tasks_manager;
        $this->task_handler_factory = $task_handler_factory;
        $this->request = $request;
    }

    public function ajax() {
        $task_slug = $this->request->param( 'action' );
        $context = $this->request->param( 'context' );

        $task = $this->tasks_manager->get_upgrade_task( $task_slug );

        if ( is_null( $task ) ) {
            return $this->error_response( sprintf( "No task was found with identifier: %s.", $task_slug ) );
        }

        $task_handler = $this->task_handler_factory->get_task_handler( $task['handler'] );

        if ( is_null( $task_handler ) ) {
            return $this->error_response( sprintf( "The handler for task '%s' couldn't be instantiated.", $task_slug ) );
        }

        list( $records_count, $records_left ) = $task_handler->run_task( $task_slug, $context );

        return $this->progress_response( $records_count, $records_left );
    }
}
