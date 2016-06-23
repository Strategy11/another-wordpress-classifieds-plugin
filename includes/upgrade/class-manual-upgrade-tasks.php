<?php

function awpcp_manual_upgrade_tasks() {
    return new AWPCP_Manual_Upgrade_Tasks( awpcp_manual_upgrade_tasks_manager() );
}

class AWPCP_Manual_Upgrade_Tasks {

    private $upgrade_tasks;

    public function __construct( $upgrade_tasks ) {
        $this->upgrade_tasks = $upgrade_tasks;
    }

    public function register_upgrade_tasks() {
        $this->upgrade_tasks->register_upgrade_task(
            'awpcp-import-payment-transactions',
            __( 'Import Payment Transactions', 'another-wordpress-classifieds-plugin' ),
            'awpcp_import_payment_transactions_task_handler',
            'plugin'
        );

        $this->upgrade_tasks->register_upgrade_task(
            'awpcp-migrate-regions-information',
            __( 'Migrate Regions Information', 'another-wordpress-classifieds-plugin' ),
            'awpcp_migrate_regions_information_task_handler',
            'plugin'
        );

        $this->upgrade_tasks->register_upgrade_task(
            'awpcp-migrate-media-information',
            __( 'Migrate Media Information', 'another-wordpress-classifieds-plugin' ),
            'awpcp_migrate_media_information_task_handler',
            'plugin'
        );

        $this->upgrade_tasks->register_upgrade_task(
            'awpcp-update-media-status',
            __( 'Update Image/Attachments Status', 'another-wordpress-classifieds-plugin' ),
            'awpcp_update_media_status_task_handler',
            'plugin'
        );

        $this->upgrade_tasks->register_upgrade_task(
            'awpcp-store-listing-categories-as-custom-taxonomies',
            __( 'Store Listing Categories as Custom Taxonomies', 'another-wordpress-classifieds-plugin' ),
            'awpcp_store_listing_categories_as_custom_taxonomies_upgrade_task_handler',
            'plugin'
        );

        $this->upgrade_tasks->register_upgrade_task(
            'awpcp-store-listings-as-custom-post-types',
            __( 'Store Listings as Custom Post Types', 'another-wordpress-classifieds-plugin' ),
            'awpcp_store_listings_as_custom_post_types_upgrade_task_handler',
            'plugin'
        );

        $this->upgrade_tasks->register_upgrade_task(
            'awpcp-store-media-as-attachments-upgrade-task-handler',
            __( 'Store Media as Attachments', 'another-wordpress-classifieds-plugin' ),
            'awpcp_store_media_as_attachments_upgrade_task_handler',
            'plugin'
        );
    }
}
