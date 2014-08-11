<?php

/**
 * @since 3.2.3
 */
abstract class AWPCP_Module {

    protected $slig;
    protected $version;
    protected $required_awpcp_version;
    protected $text_domain;

    public function __construct( $slug, $version, $required_awpcp_version ) {
        $this->slug = $slug;
        $this->version = $version;
        $this->required_awpcp_version = $required_awpcp_version;
        $this->text_domain = "awpcp-$slug";
    }

    public function setup( $awpcp ) {
        load_plugin_textdomain( $this->text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        if ( ! method_exists( $awpcp, 'is_up_to_date' ) || ! $awpcp->is_up_to_date() ) {
            // plugin installed but db versions differs from plugin version.
            // upgrade is required
            add_action( 'admin_notices', array( $this, 'required_awpcp_version_notice' ) );
            return;
        } else if ( version_compare( $awpcp->version, $this->required_awpcp_version ) < 0 ) {
            add_action( 'admin_notices', array( $this, 'required_awpcp_version_notice' ) );
            return;
        } else if ( ! $awpcp->is_compatible_with( $this->slug, $this->version ) ) {
            add_action( 'admin_notices', array( $this, 'module_not_compatible_notice' ) );
            return;
        }

        if ( ! $this->is_up_to_date() ) {
            $this->install_or_upgrade();
        }

        if ( ! $this->is_up_to_date() ) {
            return;
        }

        $this->module_setup();
    }

    public abstract function required_awpcp_version_notice();

    public function module_not_compatible_notice() {
        if ( function_exists( 'awpcp_module_not_compatible_notice' ) ) {
            echo awpcp_module_not_compatible_notice( $this->slug, $this->version );
        }
    }

    protected function is_up_to_date() {
        $installed_version = $this->get_installed_version();
        return version_compare( $installed_version, $this->version, '==' );
    }

    protected abstract function get_installed_version();

    protected function install_or_upgrade() {
        // overwrite in children classes if necessary
    }

    protected function module_setup() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $this->ajax_setup();
        } else if ( is_admin() ) {
            $this->admin_setup();
        } else {
            $this->frontend_setup();
        }
    }

    protected function ajax_setup() {}

    protected function admin_setup() {}

    protected function frontend_setup() {}
}
