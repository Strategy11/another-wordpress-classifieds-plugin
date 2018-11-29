<?php
/**
 * @package AWPCP\Upgrade
 */

/**
 * Constructor function for Upgrade Tasks Manager class.
 */
function awpcp_upgrade_tasks_manager() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_Upgrade_Tasks_Manager();
    }

    return $instance;
}

class AWPCP_Upgrade_Tasks_Manager {

    private $tasks = array();

    public function register_upgrade_task( $params ) {
        $default_args = array(
            'slug'        => '',
            'name'        => '',
            'description' => '',
            'handler'     => '',
            'context'     => '',
            'blocking'    => true,
            'type'        => 'manual',
        );
        $task         = wp_parse_args( $params, $default_args );

        $this->tasks[ $task['slug'] ] = $task;
    }

    public function get_upgrade_task( $slug ) {
        if ( isset( $this->tasks[ $slug ] ) ) {
            return $this->tasks[ $slug ];
        }

        return null;
    }

    public function is_upgrade_task_enabled( $slug ) {
        return get_option( $slug );
    }

    public function get_tasks() {
        return $this->tasks;
    }

    public function has_pending_tasks( $query = array() ) {
        $pending_tasks = $this->get_pending_tasks( $query );
        return count( $pending_tasks ) > 0;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function get_pending_tasks( $query = array() ) {
        $default_args = array(
            'type'            => null,
            'context'         => null,
            'context__not_in' => null,
            'blocking'        => null,
        );

        $query = wp_parse_args( $query, $default_args );

        $pending_tasks = array();

        foreach ( $this->tasks as $slug => $task ) {
            if ( ! is_null( $query['context'] ) && $task['context'] !== $query['context'] ) {
                continue;
            }

            if ( ! is_null( $query['context__not_in'] ) && in_array( $task['context'], (array) $query['context__not_in'], true ) ) {
                continue;
            }

            if ( ! is_null( $query['type'] ) && $task['type'] !== $query['type'] ) {
                continue;
            }

            if ( ! is_null( $query['blocking'] ) && $task['blocking'] !== $query['blocking'] ) {
                continue;
            }

            if ( $this->is_upgrade_task_enabled( $slug ) ) {
                $pending_tasks[ $slug ] = $task;
            }
        }

        return $pending_tasks;
    }

    public function enable_upgrade_task( $slug ) {
        return update_option( $slug, true );
    }

    public function disable_upgrade_task( $slug ) {
        return delete_option( $slug );
    }
}
