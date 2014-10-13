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

    public function create_task( $name, $handler, $params = array() ) {
        $result = $this->db->insert( AWPCP_TABLE_TASKS, array(
            'name' => $name,
            'created_at' => current_time( 'mysql' ),
            'metadata' => maybe_serialize( array(
                'handler' => $handler,
                'params' => $params
            ) ),
        ) );

        if ( $result === false ) {
            $messsage = __( 'There was an error trying to save the task in the database.', 'AWPCP' );
            throw new AWPCP_Exception( $message );
        }

        return $this->db->insert_id;
    }

    public function get_next_n_tasks( $n ) {
        $query = 'SELECT * FROM ' . AWPCP_TABLE_TASKS . ' ORDER BY executed_at ASC, id ASC LIMIT %d';

        $results = $this->db->get_results( $this->db->prepare( $query, $n ) );

        return $this->create_task_logic_from_db_results( $results );
    }

    private function create_task_logic_from_db_results( $results ) {
        $tasks = array();

        foreach ( $results as $task ) {
            $task->metadata = maybe_unserialize( $task->metadata );
            $tasks[] = $this->task_logic_factory->create_task_logic( $task );
        }

        return $tasks;
    }
}
