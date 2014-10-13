<?php

/**
 * TODO: now that the handler is not defined in the task, this logic object
 * doesn't seem to be necessary anymore. We can work with plain PHP object.
 */
class AWPCP_TaskLogic {

    private $task;

    public function __construct( $task ) {
        $this->task = $task;
    }

    public function get_id() {
        return $this->task->id;
    }

    public function get_name() {
        return $this->task->name;
    }

    // public function get_handler() {
    //     $handler_constructor = $this->task->metadata['handler'];

    //     if ( ! function_exists( $handler_constructor ) ) {
    //         throw new AWPCP_Exception( "The constructor function for the task handler doesn't exists." );
    //     }

    //     $task_handler = call_user_func( $handler_constructor );

    //     if ( is_null( $task_handler ) ) {
    //         throw new AWPCP_Exception( 'The constructor function for the task handler returned NULL.' );
    //     }

    //     if ( ! method_exists( $task_handler, 'run' ) ) {
    //         throw new AWPCP_Exception( "The task handler doesn't have a run() method" );
    //     }

    //     return $task_handler;
    // }

    public function get_parameters() {
        return $this->task->metadata['params'];
    }
}
