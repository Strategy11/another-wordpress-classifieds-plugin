<?php
/**
 * @package AWPCP\Tests\Plugin\Admin
 */

/**
 * Unit tests for Admin class.
 *
 * @backupGlobals disabled
 */
class AWPCP_AdminTest extends AWPCP_UnitTestCase {

    // phpcs:disable WordPress.Variables.GlobalVariables.OverrideProhibited

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->post_type    = 'post_type';
        $this->container    = array(
            'ListingFieldsMetabox'      => Mockery::mock( 'AWPCP_ListingFieldsMetabox' ),
            'ListingInformationMetabox' => Mockery::mock( 'AWPCP_ListingInformationMetabox' ),
        );
        $this->views        = null;
        $this->actions      = null;
        $this->tablenav     = null;
        $this->search       = null;
        $this->columns      = null;
        $this->restrictions = Mockery::mock( 'AWPCP_ListTableRestrictions' );

        $this->post = (object) [
            'ID'          => wp_rand(),
            'post_type'   => $this->post_type,
            'post_status' => 'publish',
        ];
    }

    /**
     * @since 4.0.0
     */
    public function test_admin_init_configures_necessary_handlers() {
        // @phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $GLOBALS['typenow'] = 'post_type';

        $this->views    = Mockery::mock( 'AWPCP_ListTableViewsHandler' );
        $this->actions  = Mockery::mock( 'AWPCP_ListTableActionsHandler' );
        $this->tablenav = Mockery::mock( 'AWPCP_ListingsTableNavHandler' );
        $this->search   = Mockery::mock( 'AWPCP_ListTableSearchHandler' );
        $this->columns  = Mockery::mock( 'AWPCP_ListingsTableColumnsHandler' );

        $admin = $this->get_test_subject();

        // Execution.
        $admin->admin_init();

        // Verification.
        $this->assertEquals( 10, has_action( 'pre_get_posts', [ $this->restrictions, 'pre_get_posts' ] ) );

        $this->assertEquals( 10, has_action( 'admin_head-edit.php', array( $this->actions, 'admin_head' ) ) );
        $this->assertEquals( 10, has_filter( 'post_type_row_actions', array( $this->actions, 'row_actions_buttons' ) ) );
        $this->assertEquals( 10, has_filter( 'bulk_actions-edit-post_type', array( $this->actions, 'get_bulk_actions' ) ) );
        $this->assertEquals( 10, has_filter( 'handle_bulk_actions-edit-post_type', array( $this->actions, 'handle_action' ) ) );
        $this->assertEquals( 10, has_filter( 'awpcp_list_table_actions_listings', array( $admin, 'register_listings_table_actions' ) ) );

        $this->assertEquals( 10, has_filter( 'views_edit-post_type', array( $this->views, 'views' ) ) );
        $this->assertEquals( 10, has_filter( 'awpcp_list_table_views_listings', array( $admin, 'register_listings_table_views' ) ) );

        $this->assertEquals( 10, has_action( 'pre_get_posts', array( $this->tablenav, 'pre_get_posts' ) ) );
        $this->assertEquals( 10, has_action( 'restrict_manage_posts', array( $this->tablenav, 'restrict_listings' ) ) );

        $this->assertEquals( 10, has_action( 'pre_get_posts', array( $this->search, 'pre_get_posts' ) ) );
        $this->assertEquals( 10, has_filter( 'get_search_query', [ $this->search, 'get_search_query' ] ) );
        $this->assertEquals( 10, has_action( 'manage_posts_extra_tablenav', [ $this->search, 'render_search_mode_dropdown' ] ) );
        $this->assertEquals( 10, has_filter( 'awpcp_list_table_search_listings', array( $admin, 'register_listings_table_search_modes' ) ) );

        $this->assertEquals( 10, has_filter( 'manage_' . $this->post_type . '_posts_columns', [ $this->columns, 'manage_posts_columns' ] ) );
        $this->assertEquals( 10, has_action( 'manage_' . $this->post_type . '_posts_custom_column', [ $this->columns, 'manage_posts_custom_column' ] ), 10, 2 );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_Admin(
            $this->post_type,
            $this->container,
            $this->views,
            $this->actions,
            $this->tablenav,
            $this->search,
            $this->columns,
            $this->restrictions
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_admin_init_configures_no_handler_for_the_wrong_post_type() {
        // @phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $GLOBALS['typenow'] = 'another_post_type';

        $admin = $this->get_test_subject();

        // Execution.
        $admin->admin_init();

        // Verification.
        $this->assertEquals( false, has_action( 'admin_head-edit.php', array( $this->actions, 'admin_head' ) ) );
        $this->assertEquals( false, has_action( 'post_row_actions', array( $this->actions, 'row_actions' ) ) );
        $this->assertEquals( false, has_filter( 'handle_bulk_actions-edit-post_type', array( $this->actions, 'handle_action' ) ) );
    }

    // phpcs:enable

    /**
     * @since 4.0.0
     */
    public function test_register_listings_table_views() {
        $this->container['NewListingTableView']                    = null;
        $this->container['FeaturedListingTableView']               = null;
        $this->container['ExpiredListingTableView']                = null;
        $this->container['AwaitingApprovalListingTableView']       = null;
        $this->container['ImagesAwaitingApprovalListingTableView'] = null;
        $this->container['FlaggedListingTableView']                = null;
        $this->container['IncompleteListingTableView']             = null;
        $this->container['UnverifiedListingTableView']             = null;
        $this->container['CompleteListingTableView']               = null;

        $admin = $this->get_test_subject();

        // Execution.
        $views = $admin->register_listings_table_views( array() );

        // Verification.
        $this->assertArrayHasKey( 'new', $views );
        $this->assertArrayHasKey( 'expired', $views );
        $this->assertArrayHasKey( 'awaiting-approval', $views );
        $this->assertArrayHasKey( 'images-awaiting-approval', $views );
        $this->assertArrayHasKey( 'flagged', $views );
        $this->assertArrayHasKey( 'incomplete', $views );
        $this->assertArrayHasKey( 'unverified', $views );
        $this->assertArrayHasKey( 'complete', $views );
    }

    /**
     * @since 4.0.0
     */
    public function test_register_listings_table_actions() {
        $this->container['EnableListingTableAction']              = null;
        $this->container['ApproveImagesTableAction']              = null;
        $this->container['DisableListingTableAction']             = null;
        $this->container['SendAccessKeyListingTableAction']       = null;
        $this->container['MarkAsSPAMListingTableAction']          = null;
        $this->container['UnflagListingTableAction']              = null;
        $this->container['ModeratorRenewListingTableAction']      = null;
        $this->container['SubscriberRenewListingTableAction']     = null;
        $this->container['MakeFeaturedListingTableAction']        = null;
        $this->container['MakeStandardListingTableAction']        = null;
        $this->container['MarkReviewedListingTableAction']        = null;
        $this->container['MarkPaidListingTableAction']            = null;
        $this->container['MarkVerifiedListingTableAction']        = null;
        $this->container['SendVerificationEmailTableAction']      = null;
        $this->container['SendToFacebookPageListingTableAction']  = null;
        $this->container['SendToFacebookGroupListingTableAction'] = null;

        $admin = $this->get_test_subject();

        // Execution.
        $actions = $admin->register_listings_table_actions( array() );

        // Verification.
        // phpcs:disable Squiz.PHP.CommentedOutCode.Found
        $this->assertArrayHasKey( 'enable', $actions );
        $this->assertArrayHasKey( 'disable', $actions );
        $this->assertArrayHasKey( 'send-access-key', $actions );
        // // $this->assertArrayHasKey( 'manage-images', $actions );
        $this->assertArrayHasKey( 'spam', $actions );
        $this->assertArrayHasKey( 'unflag', $actions );
        $this->assertArrayHasKey( 'renew', $actions );
        $this->assertArrayHasKey( 'make-featured', $actions );
        $this->assertArrayHasKey( 'make-standard', $actions );
        $this->assertArrayHasKey( 'send-to-facebook-page', $actions );
        $this->assertArrayHasKey( 'send-to-facebook-group', $actions );
        $this->assertArrayHasKey( 'mark-reviewed', $actions );
        // // $this->assertArrayHasKey( 'manage-attachments', $actions );
        // // phpcs:enable Squiz.PHP.CommentedOutCode.Found
    }

    /**
     * @since 4.0.0
     */
    public function test_register_listings_table_search_modes() {
        $this->container['KeywordListingsTableSearchMode']      = null;
        $this->container['TitleListingsTableSearchMode']        = null;
        $this->container['UserListingsTableSearchMode']         = null;
        $this->container['ContactNameListingsTableSearchMode']  = null;
        $this->container['ContactPhoneListingsTableSearchMode'] = null;
        $this->container['ContactEmailListingsTableSearchMode'] = null;
        $this->container['PayerEmailListingsTableSearchMode']   = null;
        $this->container['LocationListingsTableSearchMode']     = null;
        $this->container['IDListingsTableSearchMode']           = null;

        // Execution.
        $search_modes = $this->get_test_subject()->register_listings_table_search_modes( [] );

        // Verification.
        $this->assertArrayHasKey( 'keyword', $search_modes );
        $this->assertArrayHasKey( 'title', $search_modes );
        $this->assertArrayHasKey( 'user', $search_modes );
        $this->assertArrayHasKey( 'contact-name', $search_modes );
        $this->assertArrayHasKey( 'contact-phone', $search_modes );
        $this->assertArrayHasKey( 'payer-email', $search_modes );
        $this->assertArrayHasKey( 'location', $search_modes );
    }

    /**
     * @since 4.0.0
     */
    public function test_save_classifieds_meta_boxes_does_nothing_for_the_wrong_post_type() {
        $this->post->post_type = 'another_post_type';

        $this->check_save_is_never_called();
    }

    /**
     * @since 4.0.0
     */
    private function check_save_is_never_called() {
        $this->container['ListingFieldsMetabox'] = Mockery::mock( 'AWPCP_ListingFieldsMetabox' );
        $this->container['ListingFieldsMetabox']->shouldReceive( 'save' )->never();

        $this->get_test_subject()->save_classifieds_meta_boxes( $this->post->ID, $this->post );
    }

    /**
     * @since 4.0.0
     */
    public function test_save_classifieds_meta_boxes_does_nothing_for_the_wrong_post_status() {
        $this->post->post_status = 'auto-draft';

        $this->check_save_is_never_called();
    }

    /**
     * @since 4.0.0
     */
    public function test_save_classified_meta_boxes_prevents_infinite_loops() {
        $test_subject = $this->get_test_subject();

        $this->container['ListingFieldsMetabox']->shouldReceive( 'save' )
            ->once()
            ->with( $this->post->ID, $this->post )
            ->andReturnUsing(
                function() use ( $test_subject ) {
                    // Simulate a recursive call to do_action( 'save_post' ), to verify
                    // save() methods are executed only once.
                    $test_subject->save_classifieds_meta_boxes( $this->post->ID, $this->post );
                }
            );

        $this->container['ListingInformationMetabox']->shouldReceive( 'save' );

        $test_subject->save_classifieds_meta_boxes( $this->post->ID, $this->post );
    }
}
