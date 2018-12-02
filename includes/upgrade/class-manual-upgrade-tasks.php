<?php
/**
 * @package AWPCP/Upgrade
 */

/**
 * Constructor function for Manual Upgrade Tasks class.
 */
function awpcp_manual_upgrade_tasks() {
    return new AWPCP_Manual_Upgrade_Tasks( awpcp_upgrade_tasks_manager() );
}

/**
 * Registers the plugin manual upgrade routines that are enabled from other
 * routines on Installer during upgrade.
 *
 * Manual Upgrade Routines are routines that require the user to initiate them
 * from the admin dashboard and keep the browser tab open until all the steps
 * have been completed.
 *
 * If a manual upgrade routine is defined with blocking = true, then all other
 * plugin features are disabled until that upgrade routine is completed.
 */
class AWPCP_Manual_Upgrade_Tasks {

    /**
     * @var UpgradeTasksManager
     */
    private $upgrade_tasks;

    /**
     * Constructor.
     */
    public function __construct( $upgrade_tasks ) {
        $this->upgrade_tasks = $upgrade_tasks;
    }

    /**
     * Register manual upgrade rotuines.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function register_upgrade_tasks() {
        // @phpcs:disable PEAR.Functions.FunctionCallSignature.CloseBracketLine
        // @phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
        // @phpcs:disable PEAR.Functions.FunctionCallSignature.ContentAfterOpenBracket

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-import-payment-transactions',
            'name' => __( 'Import Payment Transactions', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'AWPCP_Import_Payment_Transactions_Task_Handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-migrate-regions-information',
            'name' => __( 'Migrate Regions Information', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'AWPCP_Migrate_Regions_Information_Task_Handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-migrate-media-information',
            'name' => __( 'Migrate Media Information', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'AWPCP_Migrate_Media_Information_Task_Handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-update-media-status',
            'name' => __( 'Update Image/Attachments Status', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'AWPCP_Update_Media_Status_Task_Handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-store-phone-number-digits',
            'name' => __( 'Store phone number digits', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'AWPCP_Store_Phone_Number_Digits_Upgrade_Task_Handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-store-listing-categories-as-custom-taxonomies',
            'name' => __( 'Store Listing Categories as Custom Taxonomies', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-store-listings-as-custom-post-types',
            'name' => __( 'Store Listings as Custom Post Types', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'AWPCP_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-store-media-as-attachments-upgrade-task-handler',
            'name' => __( 'Store Media as Attachments', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'AWPCP_Store_Media_As_Attachments_Upgrade_Task_Handler',
            'context' => 'plugin',
            'blocking' => false,
        ) );

        // @phpcs:enable

        $this->upgrade_tasks->register_upgrade_task(
            [
                'slug'     => 'awpcp-fix-id-collision-for-listing-categories',
                'name'     => __( 'Fix ID Collision for Listing Categories', 'another-wordpress-classifieds-plugin' ),
                'handler'  => 'FixIDCollisionForListingCategoriesUpgradeTaskHandler',
                'context'  => 'plugin',
                'blocking' => true,
            ]
        );

        $this->upgrade_tasks->register_upgrade_task(
            [
                'slug'     => 'awpcp-store-categories-order-as-term-meta',
                'name'     => __( 'Store Categories Order as Term Meta', 'another-wordpress-classifieds-plugin' ),
                'handler'  => 'StoreCategoriesOrderAsTermMetaTaskHandler',
                'context'  => 'plugin',
                'blocking' => false,
            ]
        );

        $this->upgrade_tasks->register_upgrade_task(
            [
                'slug'     => 'awpcp-maybe-force-post-id',
                'name'     => __( 'Maybe force the value for the next Post ID', 'another-wordpress-classifieds-plugin' ),
                'handler'  => 'MaybeForcePostIDUpgradeTaskHandler',
                'context'  => 'plugin',
                'blocking' => true,
            ]
        );

        $this->upgrade_tasks->register_upgrade_task(
            [
                'slug'     => 'awpcp-fix-id-collision-for-listings',
                'name'     => __( 'Fix ID Collision for Listings', 'another-wordpress-classifieds-plugin' ),
                'handler'  => 'FixIDCollisionForListingsUpgradeTaskHandler',
                'context'  => 'plugin',
                'blocking' => true,
            ]
        );
    }
}
