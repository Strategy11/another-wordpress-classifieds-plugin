<?php

class AWPCP_Upgrade_Task_Handler_Factory {

    private $container;

    public function __construct( $container ) {
        $this->container = $container;
    }

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
