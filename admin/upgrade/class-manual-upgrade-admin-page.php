<?php

function awpcp_manual_upgrade_admin_page() {
    return new AWPCP_ManualUpgradeAdminPage(
        awpcp_upgrade_tasks_manager(),
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

        $params = array(
            'introduction' => $this->get_introduction_text( $context ),
            'groups' => $this->get_tasks_groups( $context ),
            'submit' => _x( 'Upgrade', 'awpcp upgrade', 'another-wordpress-classifieds-plugin' ),
        );

        $tasks = new AWPCP_AsynchronousTasksComponent( $params );

        return $tasks->render();
    }

    private function get_introduction_text( $context ) {
        if ( $context == 'plugin' ) {
            return _x( 'Another WordPress Classifieds Plugin needs to upgrade your database.  The operation may take several minutes, depending on the amount of information stored. Please press the Upgrade button shown below to start the process.', 'awpcp upgrade', 'another-wordpress-classifieds-plugin' );
        } else {
            return _x( "AWPCP's premium modules need to upgrade the information stored in the database.  The operation may take several minutes, depending on the amount of information stored. Please press the Upgrade button shown below to start the process.", 'awpcp upgrade', 'another-wordpress-classifieds-plugin' );
        }
    }

    private function get_tasks_groups( $context ) {
        $tasks = $this->prepare_tasks( $context );
        $groups = array();

        if ( count( $tasks['blocking_tasks'] ) ) {
            if ( count( $tasks['non_blocking_tasks'] ) ) {
                $successContent = _x( 'Congratulations. All blocking tasks were completed successfully. You can now access all features.', 'awpcp upgrade', 'another-wordpress-classifieds-plugin' );
            } else {
                $continue_link = sprintf( '<a href="%s">', add_query_arg( 'page', 'awpcp.php' ) );

                $successContent = _x( 'Congratulations. All blocking tasks were completed successfully. You can now access all features. <continue-link>Click here to Continue</a>.', 'awpcp upgrade', 'another-wordpress-classifieds-plugin' );
                $successContent = str_replace( '<continue-link>', $continue_link, $successContent );
            }

            $groups[] = array(
                'title' => __( 'Upgrade Tasks that must complete immediately', 'another-wordpress-classifieds-plugin' ),
                'content' => __( "The following tasks need to be completed before you can use the plugin's and modules features again.", 'another-wordpress-classifieds-plugin' ),
                'successContent' => $successContent,
                'tasks' => $tasks['blocking_tasks'],
            );
        }

        if ( count( $tasks['non_blocking_tasks'] ) ) {
            $continue_link = sprintf( '<a href="%s">', add_query_arg( 'page', 'awpcp.php' ) );

            $successContent = _x( 'Congratulations. All non blocking tasks were completed successfully. <continue-link>Click here to Continue</a>.', 'awpcp upgrade', 'another-wordpress-classifieds-plugin' );
            $successContent = str_replace( '<continue-link>', $continue_link, $successContent );

            $groups[] = array(
                'title' => __( 'Upgrade tasks that will run while the plugin continues to work', 'another-wordpress-classifieds-plugin' ),
                'content' => __( "The following tasks need to be completed, but the plugin's and modules features will continue to work while the routines are executed.", 'another-wordpress-classifieds-plugin' ),
                'successContent' => $successContent,
                'tasks' => $tasks['non_blocking_tasks'],
            );
        }

        return $groups;
    }

    private function prepare_tasks( $context ) {
        if ( $context === 'plugin' ) {
            $pending_upgrade_tasks = $this->upgrade_tasks->get_pending_tasks( compact( 'context' ) );
        } else {
            $pending_upgrade_tasks = $this->upgrade_tasks->get_pending_tasks();
        }

        $last_blocking_task = null;
        $storing_blocking_tasks = true;

        foreach ( array_reverse( array_keys( $pending_upgrade_tasks ) ) as $i => $key ) {
            if ( $pending_upgrade_tasks[ $key ]['blocking'] ) {
                $last_blocking_task = $key;
                break;
            }
        }

        $blocking_tasks = array();
        $non_blocking_tasks = array();

        foreach ( $pending_upgrade_tasks as $slug => $task ) {
            $task = array(
                'name' => $task['name'],
                'description' => $task['description'],
                'action' => $slug,
            );

            if ( ! is_null( $last_blocking_task ) && $storing_blocking_tasks ) {
                $blocking_tasks[] = array( 'name' => $task['name'], 'action' => $slug );
            } else {
                $non_blocking_tasks[] = array( 'name' => $task['name'], 'action' => $slug );
            }

            if ( $last_blocking_task == $slug ) {
                $storing_blocking_tasks = false;
            }
        }

        return compact( 'blocking_tasks', 'non_blocking_tasks' );
    }
}
