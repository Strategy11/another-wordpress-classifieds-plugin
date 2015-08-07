<?php

function awpcp_uninstall_admin_page() {
    return new AWPCP_UninstallAdminPage( awpcp(), awpcp_request() );
}

class AWPCP_UninstallAdminPage {

    private $plugin;
    private $request;

    public function __construct( $plugin, $request ) {
        $this->plugin = $plugin;
        $this->request = $request;
    }

    public function dispatch() {
        global $message;

        $action = $this->request->param( 'action', 'confirm' );
        $url = awpcp_current_url();
        $dirname = AWPCPUPLOADDIR;

        if (strcmp($action, 'uninstall') == 0) {
            $this->plugin->installer->uninstall();
        }

        $template = AWPCP_DIR . '/admin/templates/admin-panel-uninstall.tpl.php';

        return awpcp_render_template( $template, compact('action', 'url', 'dirname') );
    }
}
