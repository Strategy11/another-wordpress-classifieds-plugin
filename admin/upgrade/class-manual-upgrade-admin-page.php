<?php

function awpcp_manual_upgrade_admin_page() {
    return new AWPCP_ManualUpgradeAdminPage(
        awpcp_manual_upgrade_tasks_manager(),
        awpcp_request()
    );
}

class AWPCP_ManualUpgradeAdminPage {

    private $upgrade_tasks;
    private $request;

    public function __construct( $upgrade_tasks, $request ) {
        $this->upgrade_tasks = $upgrade_tasks;
        $this->request = $request;
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'awpcp-admin-manual-upgrade' );
    }

    public function dispatch() {
        $context = $this->request->param( 'context', 'plugin' );

        $tasks = $this->prepare_tasks( $context );
        $messages = $this->prepare_messages( $context );

        $tasks = new AWPCP_AsynchronousTasksComponent( $tasks, $messages );

        return $tasks->render();
    }

    private function prepare_tasks() {
        $pending_upgrade_tasks = $this->upgrade_tasks->get_pending_tasks();

        $tasks = array();
        foreach ( $pending_upgrade_tasks as $action => $data ) {
            $tasks[] = array('name' => $data['name'], 'action' => $action);
        }

        return $tasks;
    }

    private function prepare_messages( $context ) {
        if ( $context == 'plugin' ) {
            $messages['introduction'] = _x( 'Before you can use AWPCP again we need to upgrade your database. This operation may take a few minutes, depending on the amount of information stored. Please press the Upgrade button shown below to start the process.', 'awpcp upgrade', 'AWPCP' );
        } else if ( $context == 'premium-modules' ) {
            $messages['introduction'] = _x( 'Before you can use all premium modules features again, we need to upgrade your database. This operation may take a few minutes, depending on the amount of information stored. Please press the Upgrade button shown below to start the process.', 'awpcp upgrade', 'AWPCP' );
        }

        $continue_link = sprintf( '<a href="%s">', add_query_arg( 'page', 'awpcp.php' ) );
        $messages['success'] = _x( 'Congratulations. All manual upgrades were completed successfully. You can now access all features. <continue-link>Click here to Continue</a>.', 'awpcp upgrade', 'AWPCP' );
        $messages['success'] = str_replace( '<continue-link>', $continue_link, $messages['success'] );

        $messages['button'] = _x( 'Upgrade', 'awpcp upgrade', 'AWPCP' );

        return $messages;
    }
}
