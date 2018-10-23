<?php
/**
 * @package AWPCP\Framework
 */

/**
 * @since 4.0.0
 */
trait AWPCP_ModuleInstaller {

    /**
     * @since 4.0.0
     */
    protected function upgrade_module( $module ) {
        $installed_version = $module->get_installed_version();

        foreach ( $this->get_upgrade_routines() as $version => $routines ) {
            if ( version_compare( $installed_version, $version ) >= 0 ) {
                continue;
            }

            foreach ( (array) $routines as $routine ) {
                if ( method_exists( $this, $routine ) ) {
                    $this->{$routine}( $installed_version );
                }
            }
        }
    }
}
