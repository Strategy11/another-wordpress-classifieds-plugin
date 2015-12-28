<?php

function awpcp_modules_manager_factory() {
    static $instance;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_Modules_Manager_Factory(
            awpcp_licenses_manager(),
            awpcp_modules_updater(),
            awpcp_settings_api()
        );
    }

    return $instance;
}

class AWPCP_Modules_Manager_Factory {

    private $modules_manager = null;

    private $licenses_manager;
    private $modules_updater;
    private $settings;

    public function __construct( $licenses_manager, $modules_updater, $settings ) {
        $this->licenses_manager = $licenses_manager;
        $this->modules_updater = $modules_updater;
        $this->settings = $settings;
    }

    public function get_modules_manager_instance( $plugin = null ) {
        if ( is_null( $this->modules_manager ) && is_null( $plugin ) ) {
            throw new AWPCP_Exception( 'Instance of Modules Manager is not ready.' );
        }

        if ( is_null( $this->modules_manager ) ) {
            $this->modules_manager = new AWPCP_ModulesManager(
                $plugin,
                $this->licenses_manager,
                $this->modules_updater,
                $this->settings
            );
        }

        return $this->modules_manager;
    }
}
