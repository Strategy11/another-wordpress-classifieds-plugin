<?php

function awpcp_router() {
    return new AWPCP_Router( awpcp_request() );
}

class AWPCP_Router {

    private $request;

    private $admin_pages = array();
    private $admin_subpages = array();

    private $ajax_actions = array( 'private' => array(), 'public' => array() );

    private $current_page = null;
    private $request_handler = null;

    public function __construct( $request ) {
        $this->request = $request;
    }

    public function configure_routes() {
        // this action needs to be executed before building the admin menu
        // and configuring rewrite rules and handlers for shortcodes and ajax actions.
        do_action( 'awpcp-configure-routes', $this );
    }

    public function add_admin_page( $menu_title, $page_title, $slug, $handler, $capability, $menu_icon = null ) {
        $admin_page = $this->get_or_create_admin_page( $slug );

        $admin_page->menu_title = $menu_title;
        $admin_page->title = $page_title;
        $admin_page->slug = $slug;
        $admin_page->handler = $handler;
        $admin_page->capability = $capability;
        $admin_page->menu_icon = $menu_icon;

        return $slug;
    }

    private function get_or_create_admin_page( $slug ) {
        if ( ! isset( $this->admin_pages[ $slug ] ) ) {
            $this->admin_pages[ $slug ] = new stdClass();
            $this->admin_pages[ $slug ]->slug = $slug;
            $this->admin_pages[ $slug ]->subpages = array();
        }

        return $this->admin_pages[ $slug ];
    }

    public function add_admin_subpage( $parent_page, $menu_title, $page_title, $slug, $handler = null, $capability = 'install_plugins', $priority = 10 ) {
        $admin_page = $this->get_or_create_admin_page( $parent_page );

        $admin_page->subpages[ $slug ] = $this->create_admin_subpage(
            $menu_title,
            $page_title,
            $slug,
            $handler,
            $capability,
            $priority
        );

        return "$parent_page::$slug";
    }

    private function create_admin_subpage( $menu_title, $page_title, $slug, $handler = null, $capability = 'install_plugins', $priority = 10, $type = 'subpage' ) {
        $subpage = new stdClass();

        $subpage->menu_title = $menu_title;
        $subpage->title = $page_title;
        $subpage->slug = $slug;
        $subpage->handler = $handler;
        $subpage->capability = $capability;
        $subpage->sections = array();
        $subpage->priority = $priority;
        $subpage->type = $type;

        return $subpage;
    }

    public function add_admin_section( $page, $section_param, $section_slug, $handler = null ) {
        $subpage = $this->get_admin_subpage( $page );

        if ( ! is_null( $subpage ) ) {
            $section = new stdClass();

            $section->param = $section_param;
            $section->slug = $section_slug;
            $section->handler = $handler;

            $subpage->sections[ $section_slug ] = $section;
        }

        return is_null( $subpage ) ? false : true;
    }

    private function get_admin_subpage( $ref ) {
        $parts = explode( '::', $ref );

        if ( count( $parts ) !== 2 ) {
            return null;
        }

        $parent_page = $this->get_or_create_admin_page( $parts[0] );

        if ( ! isset( $parent_page->subpages[ $parts[1] ] ) ) {
            return null;
        }

        return $parent_page->subpages[ $parts[1] ];
    }

    public function add_admin_users_page( $menu_title, $page_title, $slug, $handler = null, $capability = 'install_plugins', $priority = 10 ) {
        if ( current_user_can( 'edit_users' ) ) {
            $parent = 'users.php';
        } else {
            $parent = 'profile.php';
        }

        return $this->add_admin_subpage(
            $parent,
            $menu_title,
            $page_title,
            $slug,
            $handler,
            $capability,
            $priority,
            'users-page'
        );
    }

    public function add_admin_custom_link( $parent_page, $menu_title, $slug, $capability, $url, $priority ) {
        $custom_page = new stdClass();

        $custom_page->menu_title = $menu_title;
        $custom_page->slug = $slug;
        $custom_page->capability = $capability;
        $custom_page->priority = $priority;
        $custom_page->url = $url;
        $custom_page->type = 'custom-link';

        $admin_page = $this->get_or_create_admin_page( $parent_page );
        $admin_page->subpages[ $slug ] = $custom_page;

        return "custom:$parent_page::$slug::$url";
    }

    public function add_private_ajax_action( $action_name, $action_handler ) {
        $action = new stdClass();

        $action->name = $action_name;
        $action->handler = $action_handler;

        $this->ajax_actions['private'][ $action->name ] = $action;

        return add_action( "wp_ajax_awpcp-{$action->name}", array( $this, 'handle_private_ajax_request' ) );
    }

    public function get_admin_pages() {
        return $this->admin_pages;
    }

    public function load() {
        $this->current_page = $this->get_active_page();
        $this->request_handler = $this->get_request_handler( $this->current_page );

        if ( method_exists( $this->request_handler, 'on_load' ) ) {
            $this->request_handler->on_load();
        }
    }

    private function get_active_page() {
        global $plugin_page;

        $admin_page_slug = get_admin_page_parent();

        if ( isset( $this->admin_pages[ $admin_page_slug ] ) && $plugin_page == $admin_page_slug ) {
            return $this->admin_pages[ $admin_page_slug ];
        } else if ( isset( $this->admin_pages[ $admin_page_slug ]->subpages[ $plugin_page ] ) ) {
            return $this->admin_pages[ $admin_page_slug ]->subpages[ $plugin_page ];
        } else {
            return null;
        }
    }

    private function get_request_handler( $page ) {
        if ( is_null( $page ) ) {
            return null;
        }

        $request_handler = $this->get_request_handler_from_page_sections( $page );

        if ( ! is_null( $request_handler ) ) {
            return $request_handler;
        } else if ( is_callable( $page->handler ) ) {
            return call_user_func( $page->handler );
        } else {
            return null;
        }
    }

    private function get_request_handler_from_page_sections( $page ) {
        if ( ! isset( $page->sections ) ) {
            return null;
        }

        $request_handler = null;

        foreach ( (array) $page->sections as $section_slug => $section ) {
            $param_value = $this->request->param( $section->param );

            if ( $param_value != $section_slug || ! is_callable( $section->handler ) ) {
                continue;
            }

            $request_handler = call_user_func( $section->handler );

            if ( ! is_null( $request_handler ) ) {
                break;
            }
        }

        return $request_handler;
    }

    public function dispatch() {
        if ( is_admin() ) {
            $this->handle_admin_page( $this->current_page, $this->request_handler );
        }
    }

    // private function handle_admin_request() {
    //     global $plugin_page;

    //     $admin_page_slug = get_admin_page_parent();

    //     if ( ! isset( $this->admin_pages[ $admin_page_slug ] ) ) {
    //         return;
    //     }

    //     if ( $plugin_page == $admin_page_slug ) {
    //         $this->handle_admin_page( $this->admin_pages[ $admin_page_slug ] );
    //     } else if ( isset( $this->admin_pages[ $admin_page_slug ]->subpages[ $plugin_page ] ) ) {
    //         $this->handle_admin_page( $this->admin_pages[ $admin_page_slug ]->subpages[ $plugin_page ] );
    //     }
    // }

    private function handle_admin_page( $admin_page, $request_handler ) {
        if ( method_exists( $request_handler, 'enqueue_scripts' ) ) {
            $request_handler->enqueue_scripts();
        }

        if ( method_exists( $request_handler, 'get_display_options' ) ) {
            $admin_page->options = $request_handler->get_display_options();
        }

        echo $this->render_admin_page( $admin_page, $request_handler->dispatch() );
    }

    public function render_admin_page( $admin_page, $content ) {
        // necesary to use the admin-page template without having to modify it
        $this->page = $admin_page->slug;

        ob_start();
        include( AWPCP_DIR . '/admin/templates/admin-page.tpl.php' );
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function handle_private_ajax_request() {
        $action_name = str_replace( 'awpcp-', '', $this->request->param( 'action' ) );

        if ( ! isset( $this->ajax_actions['private'][ $action_name ] ) ) {
            return;
        }

        $current_action = $this->ajax_actions['private'][ $action_name ];

        if ( is_null( $current_action->handler ) || ! function_exists( $current_action->handler ) ) {
            return;
        }

        $request_handler = call_user_func( $current_action->handler );
        $request_handler->ajax();
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
