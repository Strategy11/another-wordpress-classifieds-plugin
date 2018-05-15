<?php

function awpcp_uninstall_admin_page() {
    return new AWPCP_UninstallAdminPage(
        awpcp()->container['Uninstaller'],
        awpcp_request()
    );
}

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
        global $message;

        $action = $this->request->param( 'action', 'confirm' );
        $url = awpcp_current_url();
        $dirname = AWPCPUPLOADDIR;

        if (strcmp($action, 'uninstall') == 0) {
            $this->uninstaller->uninstall();
        }

        $template = AWPCP_DIR . '/admin/templates/admin-panel-uninstall.tpl.php';

        return awpcp_render_template( $template, compact('action', 'url', 'dirname') );
    }
}
