<?php

function awpcp_license_settings_update_handler() {
    return new AWPCP_License_Settings_Update_Handler( awpcp_licenses_manager(), awpcp_modules_manager(), awpcp_request() );
}

class AWPCP_License_Settings_Update_Handler {

    private $licenses_manager;
    private $modules_manager;
    private $request;

    public function __construct( $licenses_manager, $modules_manager, $request ) {
        $this->licenses_manager = $licenses_manager;
        $this->modules_manager = $modules_manager;
        $this->request = $request;
    }

    public function process_settings( $old_settings, $new_settings, $option_name ) {
        if ( wp_verify_nonce( $this->request->post( 'awpcp-update-license-status-nonce' ), 'awpcp-update-license-status-nonce' ) ) {
            $this->process_settings_for_modules( $this->modules_manager->get_modules(), $old_settings, $new_settings );
        }
    }

    private function process_settings_for_modules( $modules, $old_settings, $new_settings ) {
        foreach ( $modules as $module_slug => $module ) {
            $license_setting_name = $this->licenses_manager->get_license_setting_name( $module_slug );
            $sanitized_setting_name = str_replace( '.', '_', $license_setting_name );

            $old_license = $old_settings[ $license_setting_name ];
            $new_license = $new_settings[ $license_setting_name ];

            if ( strcmp( $new_license, $old_license ) !== 0 ) {
                $this->update_license( $module_slug, $new_license );
            } else if ( $this->request->post( "awpcp-check-$sanitized_setting_name", false ) ) {
                $this->check_license( $module_slug, $new_license );
            } else if ( $this->request->post( "awpcp-activate-$sanitized_setting_name", false ) ) {
                $this->activate_license( $module_slug );
            } else if ( $this->request->post( "awpcp-deactivate-$sanitized_setting_name", false ) ) {
                $this->deactivate_license( $module_slug );
            }
        }
    }

    private function update_license( $module_slug, $new_license ) {
        if ( ! empty( $new_license ) ) {
            $this->activate_license( $module_slug );
        } else {
            $this->licenses_manager->drop_license_status( $module_slug );
        }
    }

    private function check_license( $module_slug, $license ) {
        // calling set module license causes the license manager to drop
        // the saved status, forcing it to check with the store in the next
        // request.
        // TODO: do not rely on set_module_license's side effects.
        $this->licenses_manager->set_module_license( $module_slug, $license );
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
