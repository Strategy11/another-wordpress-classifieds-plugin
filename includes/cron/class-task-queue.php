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

    public function add_task( $name, $params ) {
        $this->tasks->create_task( $name, $params );
        $this->schedule_next_task_queue_event();
    }

    private function schedule_next_task_queue_event() {
        $next_event_timestamp = $this->get_next_scheduled_event_timestamp();
        $current_time_timestamp = time();

        if ( $next_event_timestamp && ( $next_event_timestamp - $current_time_timestamp <= 60 ) ) {
            return;
        }

        wp_schedule_single_event( $current_time_timestamp + 5, 'awpcp-task-queue-event', array( 'created_at' => $current_time_timestamp ) );
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

        $this->process_next_task();

        if ( $this->have_more_tasks() ) {
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

    private function process_next_task() {
        try {
            $next_task = $this->tasks->get_next_task();
        } catch ( AWPCP_Exception $e ) {
            trigger_error( $e->format_errors() );
            return;
        }

        $this->process_task( $next_task );
    }

    private function process_task( $task ) {
        try {
            $task_was_executed_succesfully = $this->run_task( $task );

            if ( $task_was_executed_succesfully ) {
                $this->remove_task( $task );
            } else {
                $this->reschedule_task( $task  );
            }
        } catch ( AWPCP_Exception $e ) {
            trigger_error( $e->format_errors() );
        }
    }

    private function run_task( $task ) {
        try {
            $exit_code = apply_filters( "awpcp-task-{$task->get_name()}", false, $task->get_parameters() );
        } catch ( AWPCP_Exception $e ) {
            trigger_error( $e->format_errors() );
            $exit_code = false;
        }

        return $exit_code;
    }

    private function remove_task( $task ) {
        $this->tasks->delete_task( $task->get_id() );
        trigger_error( 'Task ' . $task->get_id() . ' deleted.' );
    }

    private function reschedule_task( $task ) {
        $this->tasks->update_task( $task->get_id(), current_time( 'mysql' ) );
        trigger_error( 'Task ' . $task->get_id() . ' rescheduled.' );
    }

    private function have_more_tasks() {
        try {
            $next_task = $this->tasks->get_next_task();
        } catch ( AWPCP_Exception $e ) {
            return false;
        }

        return true;
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
