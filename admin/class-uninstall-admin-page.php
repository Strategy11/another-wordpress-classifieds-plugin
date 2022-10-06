<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Constructor function.
 */
function awpcp_uninstall_admin_page() {
    return new AWPCP_UninstallAdminPage(
        awpcp()->container['Uninstaller'],
        awpcp()->container['Settings']
    );
}

/**
 * Uninstall admin page.
 */
class AWPCP_UninstallAdminPage {

    /**
     * @var object
     */
    private $uninstaller;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param object $uninstaller   An installer of Uninstaller.
     * @param object $settings      An instance of Settings.
     */
    public function __construct( $uninstaller, $settings ) {
        $this->uninstaller = $uninstaller;
        $this->settings    = $settings;
    }

    /**
     * Renders the page.
     */
    public function dispatch() {
        $action  = awpcp_get_var( array( 'param' => 'action', 'default' => 'confirm' ) );
        $url     = awpcp_current_url();
        $dirname = $this->settings->get_runtime_option( 'awpcp-uploads-dir' );

        if ( 0 === strcmp( $action, 'uninstall' ) ) {
            $this->uninstaller->uninstall();
        }

        $template = AWPCP_DIR . '/admin/templates/admin-panel-uninstall.tpl.php';

        return awpcp_render_template( $template, compact( 'action', 'url', 'dirname' ) );
    }
}
