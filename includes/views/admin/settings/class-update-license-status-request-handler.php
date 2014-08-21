<?php

function awpcp_update_license_status_request_handler() {
    return new AWPCP_UpdateLicenseStatusRequestHandler( awpcp_licenses_manager(), awpcp_modules_manager(), awpcp_request() );
}

class AWPCP_UpdateLicenseStatusRequestHandler {

    private $licenses_manager;
    private $modules_manager;
    private $request;

    public function __construct( $licenses_manager, $modules_manager, $request ) {
        $this->licenses_manager = $licenses_manager;
        $this->modules_manager = $modules_manager;
        $this->request = $request;
    }

    public function dispatch() {
        if ( wp_verify_nonce( $this->request->post( 'awpcp-update-license-status-nonce' ), 'awpcp-update-license-status-nonce' ) ) {
            $this->handle_request();
        }
    }

    private function handle_request() {
        foreach ( $this->request->post( 'awpcp-options' ) as $option_name => $license ) {
            $module_slug = str_replace( '-license', '', $option_name );

            if ( ! empty( $this->request->post( "awpcp-activate-$option_name" ) ) ) {
                $this->activate_license( $module_slug );
                break;
            }

            if ( ! empty( $this->request->post( "awpcp-deactivate-$option_name" ) ) ) {
                $this->deactivate_license( $module_slug );
                break;
            }
        }
    }

    private function activate_license( $module_slug ) {
        $module = $this->modules_manager->get_module( $module_slug );
        $this->licenses_manager->activate_license( $module->name, $module->slug );
    }

    private function deactivate_license( $module_slug ) {
        $module = $this->modules_manager->get_module( $module_slug );
        $this->licenses_manager->deactivate_license( $module->name, $module->slug );
    }
}
