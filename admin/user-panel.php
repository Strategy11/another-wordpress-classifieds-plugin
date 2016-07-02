<?php
/**
 * User Ad Management Panel functions
 */

require_once(AWPCP_DIR . '/admin/user-panel-listings.php');

function awpcp_user_panel() {
    return new AWPCP_User_Panel( awpcp_upgrade_tasks_manager() );
}

class AWPCP_User_Panel {

    private $upgrade_tasks;

	public function __construct( $upgrade_tasks ) {
        $this->upgrade_tasks = $upgrade_tasks;

        $this->account = awpcp_account_balance_page();
        $this->listings = awpcp_manage_listings_user_panel_page();

		add_action('awpcp_add_menu_page', array($this, 'menu'));
	}

	/**
	 * Register Ad Management Panel menu
	 */
	public function menu() {
        /* Profile Menu */

        // We are using read as an alias for edit_classifieds_listings. If a user can `read`,
        // he or she can `edit_classifieds_listings`.
        $capability = 'read';

        // // Account Balance
        // if (awpcp_payments_api()->credit_system_enabled() && !awpcp_current_user_is_admin()) {
        //     $parts = array($this->account->title, $this->account->menu, $this->account->page);
        //     $hook = add_users_page($parts[0], $parts[1], $capability, $parts[2], array($this->account, 'dispatch'));
        //     add_action("admin_print_styles-{$hook}", array($this->account, 'scripts'));
        // }

        $current_user_is_non_admin_moderator = awpcp_current_user_is_moderator() && ! awpcp_current_user_is_admin();

		if ( get_awpcp_option( 'enable-user-panel' ) != 1 || $current_user_is_non_admin_moderator ) {
            return;
        }

		/* Ad Management Menu */

        // $slug = 'awpcp-panel';
        // $title = sprintf(__('%s Ad Management Panel', 'another-wordpress-classifieds-plugin'), get_bloginfo());
        // $menu = __('Ad Management', 'another-wordpress-classifieds-plugin');
        // $hook = add_menu_page($title, $menu, $capability, $slug, array($this->listings, 'dispatch'), MENUICO);
        // 
        // // Listings
        // $title = __('Manage Ad Listings', 'another-wordpress-classifieds-plugin');
        // $menu = __('Listings', 'another-wordpress-classifieds-plugin');
        // $hook = add_submenu_page($slug, $title, $menu, $capability, $slug, array($this->listings, 'dispatch'));
        // add_action("admin_print_styles-{$hook}", array($this->listings, 'scripts'));
        // 
        // do_action('awpcp_panel_add_submenu_page', $slug, $capability);
	}

    public function configure_routes( $router ) {
        if ( $this->upgrade_tasks->has_pending_tasks( array( 'context' => 'plugin', 'blocking' => true ) ) ) {
            return;
        }

        if ( awpcp_payments_api()->credit_system_enabled() && ! awpcp_current_user_is_admin() ) {
            $this->add_users_page( $router );
        }

        $user_is_not_a_moderator = awpcp_current_user_is_admin() || ! awpcp_current_user_is_moderator();

        if ( get_awpcp_option( 'enable-user-panel' ) && $user_is_not_a_moderator ) {
            $this->configure_user_panel_routes( $router );
        }
    }

    private function add_users_page( $router ) {
        $router->add_admin_users_page(
            __( 'Account Balance', 'another-wordpress-classifieds-plugin' ),
            __( 'Account Balance', 'another-wordpress-classifieds-plugin' ),
            'awpcp-user-account',
            'awpcp_account_balance_page',
            awpcp_user_capability()
        );
    }

    /**
     * Register Ad Management Panel menu
     */
    public function configure_user_panel_routes( $router ) {
        $parent_page = $router->add_admin_page(
            __( 'Ad Management', 'another-wordpress-classifieds-plugin' ),
            awpcp_admin_page_title( __( 'Manage Listings', 'another-wordpress-classifieds-plugin' ) ),
            'awpcp-panel',
            'awpcp_manage_listings_user_panel_page',
            awpcp_user_capability(),
            MENUICO
        );

        $router->add_admin_subpage(
            'awpcp-panel',
            __( 'Listings', 'another-wordpress-classifieds-plugin' ),
            awpcp_admin_page_title( __( 'Manage Listings', 'another-wordpress-classifieds-plugin' ) ),
            'awpcp-panel',
            'awpcp_manage_listings_user_panel_page',
            awpcp_user_capability(),
            10
        );
    }
}
