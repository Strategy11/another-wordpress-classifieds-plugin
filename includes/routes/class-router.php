<?php

function awpcp_router() {
    return new AWPCP_Router( awpcp_request() );
}

class AWPCP_Router {

    private $request;

    private $admin_pages = array();
    private $admin_subpages = array();

    private $ajax_actions = array( 'private' => array(), 'public' => array() );

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

    public function add_admin_subpage( $parent_page, $menu_title, $page_title, $slug, $handler = null, $capability = 'install_plugins', $priority = 10, $type = 'subpage' ) {
        $admin_page = $this->get_or_create_admin_page( $parent_page );
        $admin_page->subpages[ $slug ] = $this->create_admin_subpage( $menu_title, $page_title, $slug, $handler, $capability, $priority, $type );

        return "$parent_page,$slug";
    }

    private function create_admin_subpage( $menu_title, $page_title, $slug, $handler = null, $capability = 'install_plugins', $priority = 10, $type ) {
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

    public function add_admin_users_page( $menu_title, $page_title, $slug, $handler = null, $capability = 'install_plugins', $priority = 10 ) {
        if ( current_user_can( 'edit_users' ) ) {
            $parent = 'users.php';
        } else {
            $parent = 'profile.php';
        }

        return $this->add_admin_subpage( $parent, $menu_title, $page_title, $slug, $handler, $capability, $priority, 'users-page' );
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

        return "custom:$parent_page,$slug,$url";
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

    public function dispatch() {
        if ( is_admin() ) {
            $this->handle_admin_request();
        }
    }

    private function handle_admin_request() {
        global $plugin_page;

        $admin_page_slug = get_admin_page_parent();
        // debugp( $plugin_page, $admin_page_slug );

        if ( ! isset( $this->admin_pages[ $admin_page_slug ] ) ) {
            return;
        }

        if ( $plugin_page == $admin_page_slug ) {
            $this->handle_admin_page( $this->admin_pages[ $admin_page_slug ] );
        } else if ( isset( $this->admin_pages[ $admin_page_slug ]->subpages[ $plugin_page ] ) ) {
            $this->handle_admin_page( $this->admin_pages[ $admin_page_slug ]->subpages[ $plugin_page ] );
        }
    }

    private function handle_admin_page( $current_page ) {
        if ( is_null( $current_page->handler ) || ! is_callable( $current_page->handler ) ) {
            return;
        }

        $request_handler = call_user_func( $current_page->handler );

        if ( method_exists( $request_handler, 'enqueue_scripts' ) ) {
            $request_handler->enqueue_scripts();
        }

        echo $this->render_admin_page( $current_page, $request_handler->dispatch() );
    }

    public function render_admin_page( $page, $content ) {
        // necesary to use the admin-page template without having to modify it
        $this->current_page = $page;
        $this->page = $this->current_page->slug;

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

    private function show_sidebar() {
        return awpcp_current_user_is_admin();
    }
}
