<?php

function awpcp_manual_upgrade_admin_page() {
    return new AWPCP_ManualUpgradeAdminPage(
        awpcp_upgrade_tasks_manager(),
        // TODO: this reference to the container shouldn't be here
        awpcp_container()->get( 'AWPCP_Upgrade_Sessions' ),
        awpcp_request()
    );
}

class AWPCP_ManualUpgradeAdminPage {

    private $upgrade_tasks;
    private $upgrade_sessions;
    private $request;

    public function __construct( $upgrade_tasks, $upgrade_sessions, $request ) {
        $this->upgrade_tasks = $upgrade_tasks;
        $this->upgrade_sessions = $upgrade_sessions;
        $this->request = $request;
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'awpcp-admin-manual-upgrade' );
    }

    public function dispatch() {
        $context = $this->request->param( 'context', 'plugin' );
        $pending_tasks = $this->get_pending_uprade_tasks( $context );

        $upgrade_session = $this->upgrade_sessions->get_or_create_session( $context );

        $this->add_tasks_to_upgrade_session( $pending_tasks, $upgrade_session );
        $tasks_definitions = $this->get_tasks_defintions( $pending_tasks, $upgrade_session, $context );

        $this->upgrade_sessions->save_session( $upgrade_session );

        return $this->render_asynchronous_tasks_component( $tasks_definitions, $context );
    }

    private function get_pending_uprade_tasks( $context ) {
        if ( $context === 'plugin' ) {
            return $this->upgrade_tasks->get_pending_tasks( compact( 'context' ) );
        } else {
            return $this->upgrade_tasks->get_pending_tasks();
        }
    }

    private function add_tasks_to_upgrade_session( $pending_upgrade_tasks, $upgrade_session ) {
        foreach ( $pending_upgrade_tasks as $task => $properties ) {
            if ( ! $upgrade_session->has_task( $task ) ) {
                $upgrade_session->add_task( $task );
            }
        }
    }

    private function get_tasks_defintions( $pending_upgrade_tasks, $upgrade_session, $context ) {
        $upgrade_tasks = array();
        $asynchronous_tasks = array();

        foreach ( $upgrade_session->get_tasks() as $task_slug => $_ ) {
            // add back already completed tasks!
            if ( ! isset( $pending_upgrade_tasks[ $task_slug ] ) ) {
                $upgrade_tasks[ $task_slug ] = $this->upgrade_tasks->get_upgrade_task( $task_slug );
            } else {
                $upgrade_tasks[ $task_slug ] = $pending_upgrade_tasks[ $task_slug ];
            }

            $items_count = $upgrade_session->get_task_metadata( $task_slug, 'items_count', null );
            $items_processed = $upgrade_session->get_task_metadata( $task_slug, 'items_processed', null );

            $asynchronous_tasks[ $task_slug ] = array(
                'name' => $upgrade_tasks[ $task_slug ]['name'],
                'description' => $upgrade_tasks[ $task_slug ]['description'],
                'action' => $task_slug,
                'context' => $context,
                'recordsCount' => $items_count,
                'recordsLeft' => is_null( $items_count ) ? null : $items_count - $items_processed,
            );
        }

        return $this->split_tasks_defintions( $upgrade_tasks, $asynchronous_tasks );
    }

    private function split_tasks_defintions( $pending_upgrade_tasks, $asynchronous_tasks ) {
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
            if ( ! is_null( $last_blocking_task ) && $storing_blocking_tasks ) {
                $blocking_tasks[] = $asynchronous_tasks[ $slug ];
            } else {
                $non_blocking_tasks[] = $asynchronous_tasks[ $slug ];
            }

            if ( $last_blocking_task == $slug ) {
                $storing_blocking_tasks = false;
            }
        }

        return compact( 'blocking_tasks', 'non_blocking_tasks' );
    }

    private function render_asynchronous_tasks_component( $asynchronous_tasks, $context ) {
        $params = array(
            'introduction' => $this->get_introduction_text( $context ),
            'groups' => $this->get_tasks_groups( $asynchronous_tasks ),
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

    private function get_tasks_groups( $asynchronous_tasks ) {
        $groups = array();

        if ( count( $asynchronous_tasks['blocking_tasks'] ) ) {
            if ( count( $asynchronous_tasks['non_blocking_tasks'] ) ) {
                $continue_link = sprintf( '<a href="%s" target="_blank">', add_query_arg( 'page', 'awpcp.php' ) );

                $successContent =
                '<p>' . _x( 'Congratulations. All blocking tasks were completed successfully. You can now access all features.', 'awpcp upgrade', 'another-wordpress-classifieds-plugin' ) . '</p>' .
                '<p><strong>' . __( 'Please keep this tab open, but you can open up another browser tab and continue working on your site while this processes in the background. <continue-link>Click here to open a new tab</a>.', 'another-wordpress-classifieds-plugin' ) . '</strong></p>';

                $successContent = str_replace( '<continue-link>', $continue_link, $successContent );
            } else {
                $continue_link = sprintf( '<a href="%s">', add_query_arg( 'page', 'awpcp.php' ) );

                $successContent = _x( 'Congratulations. All blocking tasks were completed successfully. You can now access all features. <continue-link>Click here to Continue</a>.', 'awpcp upgrade', 'another-wordpress-classifieds-plugin' );
                $successContent = str_replace( '<continue-link>', $continue_link, $successContent );
            }

            $groups[] = array(
                'title' => __( 'Upgrade Tasks that must complete immediately', 'another-wordpress-classifieds-plugin' ),
                'content' => '<p>' . __( "The following tasks need to be completed before you can use the plugin's and modules features again.", 'another-wordpress-classifieds-plugin' ) . '</p>',
                'successContent' => $successContent,
                'tasks' => $asynchronous_tasks['blocking_tasks'],
            );
        }

        if ( count( $asynchronous_tasks['non_blocking_tasks'] ) ) {
            if ( count( $asynchronous_tasks['blocking_tasks'] ) ) {
                $content = '<p>' . __( "The following tasks need to be completed, but the plugin's and modules features will continue to work while the routines are executed.", 'another-wordpress-classifieds-plugin' ) . '</p>';
            } else {
                $continue_link = sprintf( '<a href="%s" target="_blank">', add_query_arg( 'page', 'awpcp.php' ) );

                $content =
                '<p>' . __( "The following tasks need to be completed, but the plugin's and modules features will continue to work while the routines are executed.", 'another-wordpress-classifieds-plugin' ) . '</p>' .

                '<p><strong>' . __( 'Please keep this tab open, but you can open up another browser tab and continue working on your site while this processes in the background.', 'another-wordpress-classifieds-plugin' ) . '</strong></p>' .
                '<p>' . __( 'Click the Upgrade button and then <continue-link>click here to open a new tab</a>.', 'another-wordpress-classifieds-plugin' ) . '</p>';

                $content = str_replace( '<continue-link>', $continue_link, $content );
            }

            $continue_link = sprintf( '<a href="%s">', add_query_arg( 'page', 'awpcp.php' ) );

            $successContent = _x( 'Congratulations. All non blocking tasks were completed successfully. <continue-link>Click here to Continue</a>.', 'awpcp upgrade', 'another-wordpress-classifieds-plugin' );
            $successContent = str_replace( '<continue-link>', $continue_link, $successContent );

            $groups[] = array(
                'title' => __( 'Upgrade tasks that will run while the plugin continues to work', 'another-wordpress-classifieds-plugin' ),
                'content' => $content,
                'successContent' => $successContent,
                'tasks' => $asynchronous_tasks['non_blocking_tasks'],
            );
        }

        return $groups;
    }
}
