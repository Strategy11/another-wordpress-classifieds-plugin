<?php

// // uncomment this line for testing
// set_site_transient( 'update_plugins', null );

/**
 * @since 3.3
 */
function awpcp_modules_updater() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_ModulesUpdater( awpcp_easy_digital_downloads() );
    }

    return $instance;
}

/**
 * An adaptation of EDD_SL_Plugin_Updater to handle
 * multiple modules.
 *
 * @since 3.3
 */
class AWPCP_ModulesUpdater {

    private $modules = array();

    private $edd;

    public function __construct( $edd ) {
        $this->edd = $edd;
    }

    public function watch( $module, $license ) {
        // We are now retrieving version information for modules with an
        // incompatible version as well. That means that the instance of the module
        // may not support all the features known to the current version of the
        // main plugin.
        //
        // To avoid unexpected problems we limit our interaction with the module's
        // instance to carefully extracting the slug, name, version and path to
        // the main file.
        if (
                ! isset( $module->slug )
            || ! isset( $module->name )
            || ! isset( $module->version )
            || ! isset( $module->file )
        ) {
            return;
        }

        $this->modules[ $module->slug ] = array(
            'slug'     => $module->slug,
            'name'     => $module->name,
            'version'  => $module->version,
            'basename' => plugin_basename( $module->file ),
            'license'  => $license,
        );
    }

    public function filter_plugins_version_information( $plugins_information ) {
        if ( empty( $plugins_information ) || ! isset( $plugins_information->response ) ) {
            return $plugins_information;
        }

        foreach ( $this->modules as $module ) {
            $plugins_information = $this->filter_version_information_for_module( $module, $plugins_information );
        }

        return $plugins_information;
    }

    private function filter_version_information_for_module( $module, $plugins_information ) {
        if ( isset( $plugins_information->response[ $module['basename'] ] ) ) {
            return $plugins_information;
        }

        try {
            $information = $this->get_information_for_module( $module );
        } catch ( AWPCP_Easy_Digital_Downloads_Exception $e ) {
            awpcp_flash( $e->getMessage(), array( 'notice', 'notice-error' ) );
            return $plugins_information;
        } catch ( AWPCP_HTTP_Exception $e ) {
            awpcp_flash( $e->getMessage(), array( 'notice', 'notice-error' ) );
            return $plugins_information;
        }

        if ( ! is_object( $information ) ) {
            return $plugins_information;
        }

        if ( version_compare( $module['version'] , $information->new_version, '<' ) ) {
            $plugins_information->response[ $module['basename'] ] = $information;
        }

        $plugins_information->last_checked = time();
        $plugins_information->checked[ $module['basename'] ] = $module['version'];

        return $plugins_information;
    }

    private function get_information_for_module( $module ) {
        $transient_key = md5( 'awpcp_module_' . sanitize_key( $module['name'] ) . '_version_info' );
        $information = get_site_transient( $transient_key );

        if ( false !== $information ) {
            return $information;
        }

        $information = $this->edd->get_version(
            $module['name'],
            $module['slug'],
            'D. Rodenbaugh',
            $module['license']
        );

        set_site_transient( $transient_key, $information, DAY_IN_SECONDS );

        return $information;
    }

    public function filter_detailed_plugin_information( $response, $action, $args ) {
        if ( $action != 'plugin_information' || ! isset( $this->modules[ $args->slug ] ) ) {
            return $response;
        }

        try {
            $information = $this->get_information_for_module( $this->modules[ $args->slug ] );
        } catch ( AWPCP_Easy_Digital_Downloads_Exception $e ) {
            awpcp_flash( $e->getMessage(), array( 'notice', 'notice-error' ) );
            return $response;
        } catch ( AWPCP_HTTP_Exception $e ) {
            awpcp_flash( $e->getMessage(), array( 'notice', 'notice-error' ) );
            return $response;
        }

        return $information;
    }

    public function setup_http_request_args_filter( $bail, $package, $upgrader ) {
        if ( strpos( $package, 'edd-sl/package_download' ) !== false ) {
            add_filter( 'http_request_args', array( $this, 'filter_http_request_args' ), 10, 2 );
        }

        return $bail;
    }

    public function filter_http_request_args( $args, $url ) {
        remove_filter( 'http_request_args', array( $this, 'filter_http_request_args' ) );

        $args['user-agent'] = awpcp_user_agent_header();

        return $args;
    }
}
