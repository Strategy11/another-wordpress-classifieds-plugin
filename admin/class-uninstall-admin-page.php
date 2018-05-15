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
        awpcp_request()
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
     * @var object
     */
    private $request;

    /**
     * @param object $uninstaller   An installer of Uninstaller.
     * @param object $request       An instance of Request.
     */
    public function __construct( $uninstaller, $request ) {
        $this->uninstaller = $uninstaller;
        $this->request     = $request;
    }

    /**
     * Renders the page.
     */
    public function dispatch() {
        $action  = $this->request->param( 'action', 'confirm' );
        $url     = awpcp_current_url();
        $dirname = AWPCPUPLOADDIR;

        if ( 0 === strcmp( $action, 'uninstall' ) ) {
            $this->uninstaller->uninstall();
        }

        $template = AWPCP_DIR . '/admin/templates/admin-panel-uninstall.tpl.php';

        return awpcp_render_template( $template, compact( 'action', 'url', 'dirname' ) );
    }
}
