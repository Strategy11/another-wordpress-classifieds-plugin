<?php

function awpcp_task_queue() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_TaskQueue( awpcp_tasks_collection(), awpcp()->settings );
    }

    return $instance;
}

class AWPCP_TaskQueue {

    private $tasks;
    private $settings;

    public function __construct( $tasks, $settings ) {
        $this->tasks = $tasks;
        $this->settings = $settings;
    }

    public function add_task( $name, $handler, $params ) {
        $this->tasks->create_task( $name, $handler, $params );
        $this->schedule_next_task_queue_event();
    }

    private function schedule_next_task_queue_event() {
        $next_event_timestamp = $this->get_next_scheduled_event_timestamp();
        $current_time_timestamp = time();

        debugp( $next_event_timestamp, $current_time_timestamp, $next_event_timestamp - $current_time_timestamp );

        if ( $next_event_timestamp && ( $next_event_timestamp - $current_time_timestamp <= 60 ) ) {
            return;
        }

        wp_schedule_single_event( $current_time_timestamp + 10, 'awpcp-task-queue-event', array( 'created_at' => $current_time_timestamp ) );
    }

    /**
     * A modified version of wp_next_scheduled that doesn't takes into account
     * the parameters passed to the callback.
     */
    private function get_next_scheduled_event_timestamp() {
        $crons = _get_cron_array();

        if ( empty($crons) ) {
            return false;
        }

        foreach ( $crons as $timestamp => $cron ) {
            if ( isset( $cron[ 'awpcp-task-queue-event' ] ) ) {
                return $timestamp;
            }
        }

        return false;
    }

    public function task_queue_event( $created_at ) {
        if ( ! $this->get_lock() ) {
            return;
        }

        $next_tasks = $this->tasks->get_next_n_tasks( 2 );

        if ( $next_task = array_shift( $next_tasks ) ) {
            $this->run_task( $next_task );
        }

        if ( $next_task = array_shift( $next_tasks ) ) {
            $this->schedule_next_task_queue_event();
        }

        $this->release_lock();
    }

    private function get_lock() {
        $lockfile = $this->get_lock_file();

        if ( ! file_exists( $lockfile ) ) {
            return touch( $lockfile );
        } else if ( time() - filectime( $lockfile ) > 30 * 60 ) {
            unlink( $lockfile );
            return touch( $lockfile );
        } else {
            return false;
        }
    }

    private function get_lock_file() {
        return implode( DIRECTORY_SEPARATOR, array( $this->settings->get_runtime_option( 'awpcp-uploads-dir' ), 'task-queue.lock' ) );
    }

    private function run_task( $task ) {
        try {
            $task_handler = $task->get_handler( $task );
            $exit_code = call_user_func( array( $task_handler, 'run' ), $task->get_handler_parameters() );
        } catch ( AWPCP_Exception $e ) {
            trigger_error( $e->format_errors() );
            $exit_code = false;
        }

        if ( $exit_code ) {
            $this->remove_task( $task );
        } else {
            $this->reschedule_task( $task );
        }
    }

    private function remove_task( $task ) {
        trigger_error( 'Trying to remove a task.' );
    }

    private function reschedule_task( $task ) {
        trigger_error( 'Trying to reschedule a task.' );
    }

    private function release_lock() {
        $lockfile = $this->get_lock_file();

        if ( file_exists( $lockfile ) ) {
            return unlink( $this->get_lock_file() );
        } else {
             return false;
        }
    }
}


function awpcp_test_task_handler() {
    return new AWPCP_TestTaskHandler();
}

class AWPCP_TestTaskHandler {

    public function run( $params ) {
        throw new AWPCP_Exception( print_r( $params, true ) );
    }
}
