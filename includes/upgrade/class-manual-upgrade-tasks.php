<?php

function awpcp_manual_upgrade_tasks() {
    return new AWPCP_Manual_Upgrade_Tasks( awpcp_upgrade_tasks_manager() );
}

class AWPCP_Manual_Upgrade_Tasks {

    private $upgrade_tasks;

    public function __construct( $upgrade_tasks ) {
        $this->upgrade_tasks = $upgrade_tasks;
    }

    public function register_upgrade_tasks() {
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
    }
}
