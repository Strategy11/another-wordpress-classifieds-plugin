<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Entry point for all plugin features available from the Admin Dashboard.
 */
class AWPCP_Admin {

    /**
     * @var array
     */
    private $container;

    /**
     * @var object
     */
    private $table_actions;

    /**
     * @param array  $container         An instance of Container.
     * @param object $table_actions     An instance of List Table Actions.
     * @since 4.0.0
     */
    public function __construct( $container, $table_actions ) {
        $this->container     = $container;
        $this->table_actions = $table_actions;
    }

    /**
     * @since 4.0.0
     */
    public function admin_init() {
        add_action( 'admin_head-edit.php', array( $this->table_actions, 'admin_head' ), 10, 2 );
        add_action( 'post_row_actions', array( $this->table_actions, 'row_actions' ), 10, 2 );
        add_filter( 'handle_bulk_actions-edit-' . $this->table_actions->get_post_type(), array( $this->table_actions, 'handle_action' ), 10, 3 );

        add_filter( 'awpcp_list_table_actions_listings', array( $this, 'register_listings_table_actions' ) );
    }

    /**
     * @param array $actions    An array of actions for the Listings table.
     * @since 4.0.0
     */
    public function register_listings_table_actions( $actions ) {
        $actions['quick-view']      = $this->container['QuickViewListingTableAction'];
        $actions['enable']          = $this->container['EnableListingTableAction'];
        $actions['disable']         = $this->container['DisableListingTableAction'];
        $actions['send-access-key'] = $this->container['SendAccessKeyListingTableAction'];
        $actions['spam']            = $this->container['MarkAsSPAMListingTableAction'];
        $actions['unflag']          = $this->container['UnflagListingTableAction'];
        $actions['renew']           = $this->container['RenewListingTableAction'];
        $actions['make-featured']   = $this->container['MakeFeaturedListingTableAction'];

        return $actions;
    }
}
