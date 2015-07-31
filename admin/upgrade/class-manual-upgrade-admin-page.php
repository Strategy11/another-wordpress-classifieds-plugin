<?php

function awpcp_manual_upgrade_admin_page() {
    return new AWPCP_ManualUpgradeAdminPage( awpcp_manual_upgrade_tasks() );
}

class AWPCP_ManualUpgradeAdminPage {

    private $upgrade_tasks;

    public function __construct( $upgrade_tasks ) {
        $this->upgrade_tasks = $upgrade_tasks;
    }

    public function dispatch() {
        $pending_upgrade_tasks = $this->upgrade_tasks->get_pending_tasks();

        $tasks = array();
        foreach ( $pending_upgrade_tasks as $action => $data ) {
            $tasks[] = array('name' => $data['name'], 'action' => $action);
        }

        $messages = array(
            'introduction' => _x( 'Before you can use AWPCP again we need to upgrade your database. This operation may take a few minutes, depending on the amount of information stored. Please press the Upgrade button shown below to start the process.', 'awpcp upgrade', 'AWPCP' ),
            'success' => sprintf( _x( 'Congratulations. AWPCP has been successfully upgraded. You can now access all features. <a href="%s">Click here to Continue</a>.', 'awpcp upgrade', 'AWPCP' ), add_query_arg( 'page', 'awpcp.php' ) ),
            'button' => _x( 'Upgrade', 'awpcp upgrade', 'AWPCP' ),
        );

        $tasks = new AWPCP_AsynchronousTasksComponent( $tasks, $messages );

        return $tasks->render();
    }
}
