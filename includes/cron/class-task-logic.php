<?php

/**
 * TODO: now that the handler is not defined in the task, this logic object
 * doesn't seem to be necessary anymore. We can work with plain PHP object.
 */
class AWPCP_TaskLogic {

    const TASK_STATUS_NEW = 'new';
    const TASK_STATUS_DELAYED = 'delayed';
    const TASK_STATUS_FAILED = 'failed';
    const TASK_STATUS_COMPLETE = 'complete';

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

    public function get_priority() {
        return $this->task->priority;
    }

    public function get_execute_after_date() {
        return $this->task->execute_after;
    }

    public function get_all_metadata() {
        return $this->task->metadata;
    }

    public function get_metadata( $name ) {
        if ( isset( $this->task->metadata[ $name ] ) ) {
            $value = $this->task->metadata[ $name ];
        } else {
            $value = null;
        }

        return $value;
    }

    public function set_metadata( $name, $value ) {
        $this->task->metadata[ $name ] = $value;
    }

    public function delay( $seconds ) {
        $this->task->status = self::TASK_STATUS_DELAYED;
        $this->task->execute_after = awpcp_datetime( 'mysql', current_time( 'timestamp' ) + $seconds );
    }

    public function fail() {
        $this->task->status = self::TASK_STATUS_FAILED;
        $this->task->priority = $this->task->priority + 1;
    }

    public function complete() {
        $this->task->status = self::TASK_STATUS_COMPLETE;
    }

    public function is_delayed() {
        return $this->task->status === self::TASK_STATUS_DELAYED;
    }

    public function failed() {
        return $this->task->status === self::TASK_STATUS_FAILED;
    }

    public function is_complete() {
        return $this->task->status === self::TASK_STATUS_COMPLETE;
    }
}
