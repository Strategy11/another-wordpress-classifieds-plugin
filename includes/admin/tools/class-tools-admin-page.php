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
     * @var array
     */
    private $views;

    /**
     * @since 4.0.0
     */
    public function __construct( $template_renderer ) {
        // Tool page views.
        $views                   = array(
            array(
                'title'       => esc_html__( 'Import and Export Settings', 'another-wordpress-classifieds-plugin' ),
                'url'         => esc_url( add_query_arg( 'awpcp-view', 'import-settings' ) ),
                'description' => esc_html__( 'Import and export your settings for re-use on another site.', 'another-wordpress-classifieds-plugin' ),
            ),
            array(
                'title' => esc_html__( 'Import Listings', 'another-wordpress-classifieds-plugin' ),
                'url'   => esc_url( add_query_arg( 'awpcp-view', 'import-listings' ) ),
            ),
        );
        $views                   = apply_filters( 'awpcp_tool_page_views', $views );
        $this->views             = $views;
        $this->template_renderer = $template_renderer;
    }

    /**
     * @since 4.0.0
     */
    public function dispatch() {
        // @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->template_renderer->render_template( $this->template, $this->views );
    }
}
