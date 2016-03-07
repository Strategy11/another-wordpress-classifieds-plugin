<?php

function awpcp_modules_manager_factory() {
    static $instance;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_Modules_Manager_Factory(
            awpcp_manual_upgrade_tasks_manager(),
            awpcp_licenses_manager(),
            awpcp_modules_updater(),
            awpcp_settings_api(),
            awpcp_request()
        );
    }

    return $instance;
}

class AWPCP_Modules_Manager_Factory {

    private $modules_manager = null;

    private $upgrade_tasks;
    private $licenses_manager;
    private $modules_updater;
    private $settings;
    private $request;

    public function __construct( $upgrade_tasks, $licenses_manager, $modules_updater, $settings, $request ) {
        $this->upgrade_tasks = $upgrade_tasks;
        $this->licenses_manager = $licenses_manager;
        $this->modules_updater = $modules_updater;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function get_modules_manager_instance( $plugin = null ) {
        if ( is_null( $this->modules_manager ) && is_null( $plugin ) ) {
            throw new AWPCP_Exception( 'Instance of Modules Manager is not ready.' );
        }

        if ( is_null( $this->modules_manager ) ) {
            $this->modules_manager = new AWPCP_ModulesManager(
                $plugin,
                $this->upgrade_tasks,
                $this->licenses_manager,
                $this->modules_updater,
                $this->settings,
                $this->request
            );
        }

        return $this->modules_manager;
    }
}
