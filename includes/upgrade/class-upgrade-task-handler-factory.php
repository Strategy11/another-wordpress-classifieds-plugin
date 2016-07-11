<?php

class AWPCP_Upgrade_Task_Handler_Factory {

    private $container;

    public function __construct( $container ) {
        $this->container = $container;
    }

    public function get_task_handler( $task_runner_class ) {
        return new AWPCP_Upgrade_Task_Handler(
            $this->container->get( $task_runner_class ),
            $this->container->get( 'AWPCP_Upgrade_Sessions' ),
            awpcp_upgrade_tasks_manager()
        );
    }
}
