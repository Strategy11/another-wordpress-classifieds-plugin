<?php

/**
 * @since 3.5.4
 */
function awpcp_router() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_Router(
            awpcp_routes(),
            awpcp_template_renderer(),
            awpcp_request()
        );
    }

    return $instance;
}

/**
 * @since 3.5.4
 */
class AWPCP_Router {

    private $template_renderer;
    private $request;

    private $current_page = null;
    private $request_handler = null;

    public function __construct( $routes, $template_renderer, $request ) {
        $this->routes = $routes;
        $this->template_renderer = $template_renderer;
        $this->request = $request;
    }

    public function get_routes() {
        return $this->routes;
    }

    public function configure_routes() {
        // this action needs to be executed before building the admin menu
        // and configuring rewrite rules and handlers for shortcodes and ajax actions.
        do_action( 'awpcp-configure-routes', $this->routes );
    }

    public function on_admin_load() {
        $this->current_page = $this->get_active_admin_page();
        $this->request_handler = $this->get_request_handler( $this->current_page );

        $this->load_admin_page( $this->current_page, $this->request_handler );
    }

    private function get_active_admin_page() {
        return $this->routes->get_admin_page( get_admin_page_parent(), $GLOBALS['plugin_page'] );
    }

    private function get_request_handler( $page ) {
        if ( is_null( $page ) ) {
            return null;
        }

        if ( isset( $page->sections ) ) {
            $section_handler = $this->get_request_handler_from_page_sections( $page );
        } else {
            $section_handler = null;
        }

        return $this->pick_request_handler( array( $section_handler, $page->handler ) );
    }

    private function get_request_handler_from_page_sections( $page ) {
        foreach ( (array) $page->sections as $section_slug => $section ) {
            $param_value = $this->request->param( $section->param, false );

            if ( $param_value === false ) {
                continue;
            }

            if ( ! is_null( $section->value ) && $param_value != $section->value ) {
                continue;
            }

            return $section->handler;
        }

        return null;
    }

    private function pick_request_handler( $request_handlers ) {
        foreach ( $request_handlers as $constructor_function ) {
            if ( ! is_callable( $constructor_function ) ) {
                continue;
            }

            $request_handler = call_user_func( $constructor_function );

            if ( ! is_null( $request_handler ) ) {
                return $request_handler;
            }
        }

        return null;
    }

    public function load_admin_page( $admin_page, $request_handler ) {
        if ( method_exists( $request_handler, 'on_load' ) ) {
            $request_handler->on_load();
        }

        do_action( 'awpcp-admin-load-' . $admin_page->slug );
    }

    public function serve_admin_page( $route ) {
        $route = wp_parse_args( $route, array( 'parent' => null, 'page' => null, 'section' => null ) );

        $admin_page = $this->routes->get_admin_page( $route['parent'], $route['page'] );
        $request_handler = $this->get_request_handler_for_section( $admin_page, $route['section'] );

        $this->load_admin_page( $admin_page, $request_handler );
        $this->handle_admin_page( $admin_page, $request_handler );
    }

    private function get_request_handler_for_section( $page, $section_slug ) {
        if ( isset( $page->sections[ $section_slug ] ) ) {
            $section_handler = $page->sections[ $section_slug ]->handler;
        } else {
            $section_handler = null;
        }

        return $this->pick_request_handler( array( $section_handler, $page->handler ) );
    }

    public function on_admin_dispatch() {
        if ( is_admin() ) {
            $this->handle_admin_page( $this->current_page, $this->request_handler );
        }
    }

    private function handle_admin_page( $admin_page, $request_handler ) {
        if ( method_exists( $request_handler, 'enqueue_scripts' ) ) {
            $request_handler->enqueue_scripts();
        }

        if ( method_exists( $request_handler, 'get_display_options' ) ) {
            $admin_page->options = $request_handler->get_display_options();
        }

        if ( method_exists( $request_handler, 'dispatch' ) ) {
            $page_content = $request_handler->dispatch();
        } else {
            $page_content = false;
        }

        if ( $page_content ) {
            echo $this->render_admin_page( $admin_page, $page_content );
        }
    }

    private function render_admin_page( $admin_page, $content ) {
        $template = AWPCP_DIR . '/admin/templates/admin-page.tpl.php';

        $params = array(
            'current_page' => $this->current_page,
            'page_slug' => $admin_page->slug,
            'page_title' => $this->title(),
            'show_sidebar' => $this->show_sidebar( $this->current_page ),
            'content' => $content,
        );

        return $this->template_renderer->render_template( $template, $params );
    }



    public function handle_anonymous_ajax_request() {
        return $this->handle_ajax_request( 'anonymous' );
    }

    private function handle_ajax_request( $type ) {
        $action_name = str_replace( 'awpcp-', '', $this->request->param( 'action' ) );

        if ( ! isset( $this->ajax_actions[ $type ][ $action_name ] ) ) {
            return;
        }

        $current_action = $this->ajax_actions[ $type ][ $action_name ];

        if ( is_null( $current_action->handler ) || ! function_exists( $current_action->handler ) ) {
            return;
        }

        $request_handler = call_user_func( $current_action->handler );

        if ( is_null( $request_handler ) ) {
            return;
        }

        $request_handler->ajax();
    }

    public function handle_private_ajax_request() {
        return $this->handle_ajax_request( 'private' );
    }

    /* Admin Page template expects user class to have the following methods defined */

    private function title() {
        return $this->current_page->title;
    }

    private function show_sidebar( $current_page ) {
        if ( isset( $current_page->options['show_sidebar'] ) ) {
            return $current_page->options['show_sidebar'];
        } else {
            return awpcp_current_user_is_admin();
        }
    }
}
