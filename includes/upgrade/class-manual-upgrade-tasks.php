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
            'handler' => 'awpcp_import_payment_transactions_task_handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-migrate-regions-information',
            'name' => __( 'Migrate Regions Information', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'awpcp_migrate_regions_information_task_handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-migrate-media-information',
            'name' => __( 'Migrate Media Information', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'awpcp_migrate_media_information_task_handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-update-media-status',
            'name' => __( 'Update Image/Attachments Status', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'awpcp_update_media_status_task_handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-store-phone-number-digits',
            'name' => __( 'Store phone number digits', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'awpcp_store_phone_number_digits_upgrade_task_handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-store-listing-categories-as-custom-taxonomies',
            'name' => __( 'Store Listing Categories as Custom Taxonomies', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'awpcp_store_listing_categories_as_custom_taxonomies_upgrade_task_handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-store-listings-as-custom-post-types',
            'name' => __( 'Store Listings as Custom Post Types', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'awpcp_store_listings_as_custom_post_types_upgrade_task_handler',
            'context' => 'plugin',
        ) );

        $this->upgrade_tasks->register_upgrade_task( array(
            'slug' => 'awpcp-store-media-as-attachments-upgrade-task-handler',
            'name' => __( 'Store Media as Attachments', 'another-wordpress-classifieds-plugin' ),
            'handler' => 'awpcp_store_media_as_attachments_upgrade_task_handler',
            'context' => 'plugin',
            'blocking' => false,
        ) );
    }
}
