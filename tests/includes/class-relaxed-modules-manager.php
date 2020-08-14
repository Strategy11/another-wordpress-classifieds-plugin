<?php

function awpcp_relaxed_modules_manager() {
    return new AWPCP_Relaxed_Modules_Manager(
        awpcp(),
        awpcp_upgrade_tasks_manager(),
        awpcp_licenses_manager(),
        awpcp_modules_updater(),
        awpcp()->settings,
        awpcp_request()
    );
}

class AWPCP_Relaxed_Modules_Manager extends AWPCP_ModulesManager {

    protected function is_premium_module( $module ) {
        return false;
    }
}
