<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Entry point for all plugin features available from the Admin Dashboard.
 */
class AWPCP_Admin {

    /**
     * @var string
     */
    private $post_type;

    /**
     * @var array
     */
    private $container;

    /**
     * @var object
     */
    private $table_views;

    /**
     * @var object
     */
    private $table_actions;

    /**
     * @param string $post_type         A post type identifier.
     * @param array  $container         An instance of Container.
     * @param object $table_views       An instance of List Table Views Handler.
     * @param object $table_actions     An instance of List Table Actions Handler.
     * @since 4.0.0
     */
    public function __construct( $post_type, $container, $table_views, $table_actions ) {
        $this->post_type     = $post_type;
        $this->container     = $container;
        $this->table_views   = $table_views;
        $this->table_actions = $table_actions;
    }

    /**
     * @since 4.0.0
     */
    public function admin_init() {
        global $typenow;

        if ( $this->post_type === $typenow ) {
            add_filter( 'pre_get_posts', array( $this->table_views, 'pre_get_posts' ) );
            add_filter( 'views_edit-' . $this->post_type, array( $this->table_views, 'views' ) );

            add_action( 'admin_head-edit.php', array( $this->table_actions, 'admin_head' ), 10, 2 );
            add_action( 'post_row_actions', array( $this->table_actions, 'row_actions' ), 10, 2 );
            add_filter( 'handle_bulk_actions-edit-' . $this->post_type, array( $this->table_actions, 'handle_action' ), 10, 3 );
        }

        add_action( 'add_meta_boxes_' . $this->post_type, array( $this, 'add_classifieds_meta_boxes' ) );
        add_action( 'save_post_' . $this->post_type, array( $this->container['ListingFieldsMetabox'], 'save' ), 10, 2 );

        add_filter( 'awpcp_list_table_views_listings', array( $this, 'register_listings_table_views' ) );
        add_filter( 'awpcp_list_table_actions_listings', array( $this, 'register_listings_table_actions' ) );
    }

    /**
     * @param array $views  An array of views for the Listings table.
     * @since 4.0.0
     */
    public function register_listings_table_views( $views ) {
        $views['new']                      = $this->container['NewListingTableView'];
        $views['featured']                 = $this->container['FeaturedListingTableView'];
        $views['expired']                  = $this->container['ExpiredListingTableView'];
        $views['awaiting-approval']        = $this->container['AwaitingApprovalListingTableView'];
        $views['images-awaiting-approval'] = $this->container['ImagesAwaitingApprovalListingTableView'];
        $views['flagged']                  = $this->container['FlaggedListingTableView'];
        $views['incomplete']               = $this->container['IncompleteListingTableView'];

        return $views;
    }

    /**
     * @param array $actions    An array of actions for the Listings table.
     * @since 4.0.0
     */
    public function register_listings_table_actions( $actions ) {
        $actions['quick-view']             = $this->container['QuickViewListingTableAction'];
        $actions['enable']                 = $this->container['EnableListingTableAction'];
        $actions['disable']                = $this->container['DisableListingTableAction'];
        $actions['send-access-key']        = $this->container['SendAccessKeyListingTableAction'];
        $actions['spam']                   = $this->container['MarkAsSPAMListingTableAction'];
        $actions['unflag']                 = $this->container['UnflagListingTableAction'];
        $actions['renew']                  = $this->container['RenewListingTableAction'];
        $actions['make-featured']          = $this->container['MakeFeaturedListingTableAction'];
        $actions['make-standard']          = $this->container['MakeStandardListingTableAction'];
        $actions['mark-reviewed']          = $this->container['MarkReviewedListingTableAction'];
        $actions['mark-sold']              = $this->container['MarkSoldListingTableAction'];
        $actions['mark-unsold']            = $this->container['MarkUnsoldListingTableAction'];
        $actions['send-to-facebook-page']  = $this->container['SendToFacebookPageListingTableAction'];
        $actions['send-to-facebook-group'] = $this->container['SendToFacebookGroupListingTableAction'];

        return $actions;
    }

    /**
     * @since 4.0.0
     */
    public function add_classifieds_meta_boxes() {
        add_meta_box(
            'awpcp_classified_fields',
            __( 'Classified Fields', 'another-wordpress-classifieds-plugin' ),
            array( $this->container['ListingFieldsMetabox'], 'render' ),
            $this->post_type,
            'advanced'
        );

        add_action( 'admin_enqueue_scripts', array( $this->container['ListingFieldsMetabox'], 'enqueue_scripts' ) );
    }
}
