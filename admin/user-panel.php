<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Constructor function.
 */
function awpcp_user_panel() {
    return new AWPCP_User_Panel( awpcp_upgrade_tasks_manager() );
}

/**
 * Register admin menu items for subscribers.
 */
class AWPCP_User_Panel {

    /**
     * @var UpgradeTasksManager
     */
    private $upgrade_tasks;

    /**
     * Constructor.
     */
	public function __construct( $upgrade_tasks ) {
        $this->upgrade_tasks = $upgrade_tasks;

        $this->account = awpcp_account_balance_page();
	}

    /**
     * Handler for the awpcp-configure-routes action.
     */
    public function configure_routes( $router ) {
        $params = [
            'context'  => 'plugin',
            'blocking' => true,
        ];

        if ( $this->upgrade_tasks->has_pending_tasks( $params ) ) {
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

    /**
     * Registers the page used by subscribers to see their credit account balance.
     */
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
        $router->add_admin_page(
            __( 'Ad Management', 'another-wordpress-classifieds-plugin' ),
            awpcp_admin_page_title( __( 'Manage Listings', 'another-wordpress-classifieds-plugin' ) ),
            'awpcp-panel',
            'awpcp_manage_listings_user_panel_page',
            awpcp_user_capability(),
            MENUICO
        );
    }
}
