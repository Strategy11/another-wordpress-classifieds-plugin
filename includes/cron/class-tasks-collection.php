<?php

function awpcp_tasks_collection() {
    return new AWPCP_TasksCollection( awpcp_task_logic_factory(), $GLOBALS['wpdb'] );
}

class AWPCP_TasksCollection {

    private $task_logic_factory;
    private $db;

    public function __construct( $task_logic_factory, $db ) {
        $this->task_logic_factory = $task_logic_factory;
        $this->db = $db;
    }

    public function create_task( $name, $metadata = array() ) {
        $result = $this->db->insert( AWPCP_TABLE_TASKS, array(
            'name' => $name,
            'execute_after' => current_time( 'mysql' ),
            'metadata' => maybe_serialize( $metadata ),
            'created_at' => current_time( 'mysql' ),
        ) );

        if ( $result === false ) {
            $messsage = __( 'There was an error trying to save the task to the database.', 'AWPCP' );
            throw new AWPCP_Exception( $message );
        }

        return $this->db->insert_id;
    }

    public function get_next_task() {
        $sql = 'SELECT * FROM ' . AWPCP_TABLE_TASKS . ' WHERE execute_after < %s ORDER BY priority ASC, created_at ASC LIMIT 1';

        $result = $this->db->get_row( $this->db->prepare( $sql, current_time( 'mysql' ) ) );

        if ( $result === false ) {
            throw new AWPCP_Exception( 'There was an error tring to retrive the next task from the database.' );
        }

        if ( $result === null ) {
            throw new AWPCP_Exception( 'There are no more tasks.' );
        }

        return $this->create_task_logic_from_result( $result );
    }

    private function create_task_logic_from_result( $task ) {
        $task->metadata = maybe_unserialize( $task->metadata );
        return $this->task_logic_factory->create_task_logic( $task );
    }

    public function update_task( $task ) {
        $data = array(
            'priority' => $task->get_priority(),
            'execute_after' => $task->get_execute_after_date(),
            'metadata' => maybe_serialize( $task->get_all_metadata() ),
        );
        $conditions = array( 'id' => $task->get_id() );

        $result = $this->db->update( AWPCP_TABLE_TASKS, $data, $conditions );

        if ( $result === false ) {
            $message = 'There was an error trying to save task <task-id> to the database.';
            throw new AWPCP_Exception( str_replace( '<task-id>', $task->get_id(), $message ) );
        }

        return $result;
    }

    public function delete_task( $task_id ) {
        $result = $this->db->query( $this->db->prepare( 'DELETE FROM ' . AWPCP_TABLE_TASKS . ' WHERE id = %d', $task_id ) );

        if ( $result === false ) {
            $message = 'There was an error trying to delete task <task-id> from the database.';
            throw new AWPCP_Exception( str_replace( '<task-id>', $task_id, $message ) );
        }

        return $result;
    }
}
