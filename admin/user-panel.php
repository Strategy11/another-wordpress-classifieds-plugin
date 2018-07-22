<?php
/**
 * User Ad Management Panel functions
 */

function awpcp_user_panel() {
    return new AWPCP_User_Panel( awpcp_upgrade_tasks_manager() );
}

class AWPCP_User_Panel {

    private $upgrade_tasks;

	public function __construct( $upgrade_tasks ) {
        $this->upgrade_tasks = $upgrade_tasks;

        $this->account = awpcp_account_balance_page();
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
    }
}
