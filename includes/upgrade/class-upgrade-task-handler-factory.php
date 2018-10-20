<?php
/**
 * @package AWPCP\Upgrade
 */

/**
 * Factory for Upgrade Task Handler class.
 */
class AWPCP_Upgrade_Task_Handler_Factory {

    /**
     * @var Container
     */
    private $container;

    /**
     * Constructor.
     */
    public function __construct( $container ) {
        $this->container = $container;
    }

    /**
     * Loads an upgrade task handler using an instance of the provided class
     * name as the Task Runner.
     */
    public function get_task_handler( $task_runner_class ) {
        $task_runner = null;

        if ( isset( $this->container[ $task_runner_class ] ) ) {
            $task_runner = $this->container[ $task_runner_class ];
        }

        if ( is_null( $task_runner ) ) {
            $task_runner = $this->container->get( $task_runner_class );
        }

        return new AWPCP_Upgrade_Task_Handler(
            $task_runner,
            $this->container->get( 'AWPCP_Upgrade_Sessions' ),
            awpcp_upgrade_tasks_manager()
        );
    }
}
