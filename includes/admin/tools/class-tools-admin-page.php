<?php
/**
 * @package AWPCP\Admin\Tools
 */

/**
 * @since 4.0.0
 */
class AWPCP_ToolsAdminPage {

    /**
     * @var string
     */
    private $template = '/admin/tools/tools-admin-page.tpl.php';

    /**
     * @since 4.0.0
     */
    public function __construct( $template_renderer ) {
        $this->template_renderer = $template_renderer;
    }

    /**
     * @since 4.0.0
     */
    public function dispatch() {
        echo $this->template_renderer->render_template( $this->template ); // XSS Ok.
    }
}
