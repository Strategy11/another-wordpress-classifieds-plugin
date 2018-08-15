<?php

class AWPCP_Page {

    public $show_menu_items = true;
    public $classifieds_bar_components = array();

    protected $template = 'frontend/templates/page.tpl.php';
    protected $action = false;

    public $page;
    public $title;

    protected $template_renderer;

    public function __construct( $page, $title, $template_renderer ) {
        $this->page = $page;
        $this->title = $title;
        $this->template_renderer = $template_renderer;
    }

    public function get_current_action($default=null) {
        return $this->action ? $this->action : $default;
    }

    public function url($params=array()) {
        $url = add_query_arg( urlencode_deep( $params ), awpcp_current_url());
        return $url;
    }

    public function dispatch() {
        return '';
    }

    public function redirect($action) {
        $this->action = $action;
        return $this->dispatch();
    }

    public function title() {
        return $this->title;
    }

    /**
     * @since feature/1112 Updated to use an instance of Template Renderer.
     */
    public function render( $content_template, $content_params = array() ) {
        $page_template = AWPCP_DIR . '/' . $this->template;

        return $this->template_renderer->render_page_template(
            $this, $page_template, $content_template, $content_params
        );
    }
}
