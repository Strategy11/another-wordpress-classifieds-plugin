<?php

$container->share(
    'AWPCP_Import_Payment_Transactions_Task_Handler',
    AWPCP_DIR . '/includes/upgrade/class-import-payment-transactions-task-handler.php',
    'awpcp_import_payment_transactions_task_handler'
);

function awpcp_import_payment_transactions_task_handler() {
    return new AWPCP_Import_Payment_Transactions_Task_Handler();
}


$container->share(
    'AWPCP_Migrate_Media_Information_Task_Handler',
    AWPCP_DIR . '/includes/upgrade/class-migrate-media-information-task-handler.php',
    'awpcp_migrate_media_information_task_handler'
);

function awpcp_migrate_media_information_task_handler() {
    return new AWPCP_Migrate_Media_Information_Task_Handler( $GLOBALS['wpdb'] );
}


$container->share(
    'AWPCP_Migrate_Regions_Information_Task_Handler',
    AWPCP_DIR . '/includes/upgrade/class-migrate-regions-information-task-handler.php',
    'awpcp_migrate_regions_information_task_handler'
);

function awpcp_migrate_regions_information_task_handler() {
    return new AWPCP_Migrate_Regions_Information_Task_Handler();
}


$container->share(
    'AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler',
    AWPCP_DIR . '/includes/upgrade/class-store-listing-categories-as-custom-taxonomies-upgrade-task-handler.php',
    'awpcp_store_listing_categories_as_custom_taxonomies_upgrade_task_handler'
);

function awpcp_store_listing_categories_as_custom_taxonomies_upgrade_task_handler() {
    return new AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler(
        awpcp_categories_registry(),
        awpcp_wordpress(),
        $GLOBALS['wpdb']
    );
}


$container->share(
    'AWPCP_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler',
    AWPCP_DIR . '/includes/upgrade/class-store-listings-as-custom-post-types-upgrade-task-handler.php',
    'awpcp_store_listings_as_custom_post_types_upgrade_task_handler'
);

function awpcp_store_listings_as_custom_post_types_upgrade_task_handler( $container ) {
    return new AWPCP_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler(
        awpcp_categories_registry(),
        awpcp_legacy_listings_metadata(),
        awpcp_wordpress(),
        $GLOBALS['wpdb']
    );
}


$container->share(
    'AWPCP_Store_Media_As_Attachments_Upgrade_Task_Handler',
    AWPCP_DIR . '/includes/upgrade/class-store-media-as-attachments-upgrade-task-handler.php',
    'awpcp_store_media_as_attachments_upgrade_task_handler'
);

function awpcp_store_media_as_attachments_upgrade_task_handler( $container ) {
    return new AWPCP_Store_Media_As_Attachments_Upgrade_Task_Handler(
        awpcp_settings_api(),
        awpcp_wordpress(),
        $GLOBALS['wpdb']
    );
}


$container->share(
    'AWPCP_Store_Phone_Number_Digits_Upgrade_Task_Handler',
    AWPCP_DIR . '/includes/upgrade/class-store-phone-number-digits-upgrade-task-handler.php',
    'awpcp_store_phone_number_digits_upgrade_task_handler'
);

function awpcp_store_phone_number_digits_upgrade_task_handler() {
    return new AWPCP_Store_Phone_Number_Digits_Upgrade_Task_Handler(
        $GLOBALS['wpdb']
    );
}


$container->share(
    'AWPCP_Update_Media_Status_Task_Handler',
    AWPCP_DIR . '/includes/upgrade/class-update-media-status-task-handler.php',
    'awpcp_update_media_status_task_handler'
);

function awpcp_update_media_status_task_handler() {
    return new AWPCP_Update_Media_Status_Task_Handler();
}


$container->share(
    'AWPCP_Upgrade_Task_Ajax_Handler',
    AWPCP_DIR . '/includes/upgrade/class-upgrade-task-ajax-handler.php',
    'awpcp_upgrade_task_ajax_handler'
);

function awpcp_upgrade_task_ajax_handler( $container ) {
    return new AWPCP_Upgrade_Task_Ajax_Handler(
        awpcp_upgrade_tasks_manager(),
        $container->get( 'AWPCP_Upgrade_Task_Handler_Factory' ),
        awpcp_request(),
        awpcp_ajax_response()
    );
}


$container->share(
    'AWPCP_Upgrade_Task_Handler_Factory',
    AWPCP_DIR . '/includes/upgrade/class-upgrade-task-handler-factory.php',
    'awpcp_upgrade_task_handler_factory'
);

function awpcp_upgrade_task_handler_factory( $container ) {
    return new AWPCP_Upgrade_Task_Handler_Factory( $container );
}


$container->share(
    'AWPCP_Upgrade_Sessions',
    array(
        AWPCP_DIR . '/includes/upgrade/class-upgrade-session.php',
        AWPCP_DIR . '/includes/upgrade/class-upgrade-sessions.php',
    ),
    'awpcp_upgrade_sessions'
);

function awpcp_upgrade_sessions() {
    return new AWPCP_Upgrade_Sessions(
        awpcp_upgrade_tasks_manager(),
        awpcp_wordpress()
    );
}
