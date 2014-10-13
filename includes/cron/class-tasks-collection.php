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

    public function create_task( $name, $params = array() ) {
        $result = $this->db->insert( AWPCP_TABLE_TASKS, array(
            'name' => $name,
            'created_at' => current_time( 'mysql' ),
            'metadata' => maybe_serialize( array(
                'params' => $params
            ) ),
        ) );

        if ( $result === false ) {
            $messsage = __( 'There was an error trying to save the task to the database.', 'AWPCP' );
            throw new AWPCP_Exception( $message );
        }

        return $this->db->insert_id;
    }

    public function get_next_task() {
        $result = $this->db->get_row( 'SELECT * FROM ' . AWPCP_TABLE_TASKS . ' ORDER BY executed_at ASC, id ASC LIMIT 1' );

        if ( $result === false ) {
            throw new AWPCP_Exception( 'There was an error tring to retrive the next task from the database.' );
        }

        return $this->create_task_logic_from_result( $result );
    }

    private function create_task_logic_from_result( $task ) {
        $task->metadata = maybe_unserialize( $task->metadata );
        return $this->task_logic_factory->create_task_logic( $task );
    }

    public function update_task( $task_id, $executed_at ) {
        $result = $this->db->update( AWPCP_TABLE_TASKS, array( 'executed_at' => $executed_at ), array( 'id' => $task_id ) );

        if ( $result === false ) {
            $message = 'There was an error trying to save task <task-id> to the database.';
            throw new AWPCP_Exception( str_replace( '<task-id>', $task_id, $message ) );
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
