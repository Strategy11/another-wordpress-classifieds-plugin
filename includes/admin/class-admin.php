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
        add_action( 'post_row_actions', array( $this->table_actions, 'row_actions' ), 10, 2 );
    }

    /**
     * @param array $actions    An array of actions for the Listings table.
     * @since 4.0.0
     */
    public function register_listings_table_actions( $actions ) {
        return $actions;
    }
}
