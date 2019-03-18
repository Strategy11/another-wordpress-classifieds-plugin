<?php
/**
 * Upgrade Container Configuration class.
 *
 * @package AWPCP\Upgrade
 */

/**
 * Container configuration object responsible for registering classes used
 * in manual upgrade routines.
 */
class AWPCP_UpgradeContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * @since 4.0.0
     *
     * @see AWPCP_ContainerConfigurationInterface::modify()
     */
    public function modify( $container ) {
        $container['ImportPaymentTransactionsTaskHandler'] = $container->service(
            function( $container ) {
                return new AWPCP_Import_Payment_Transactions_Task_Handler();
            }
        );

        $container['MigrateMediaInformationTaskHandler'] = $container->service(
            function( $container ) {
                return new AWPCP_Migrate_Media_Information_Task_Handler(
                    $container['Settings'],
                    $container['wpdb']
                );
            }
        );

        $container['MigrateRegionsInformationTaskHandler'] = $container->service(
            function( $container ) {
                return new AWPCP_Migrate_Regions_Information_Task_Handler();
            }
        );

        $container['StoreListingCategoriesAsCustomTaxonomiesUpgradeTaskHandler'] = $container->service(
            function( $container ) {
                return new AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler(
                    awpcp_categories_registry(),
                    $container['WordPress'],
                    $container['wpdb']
                );
            }
        );

        $container['StoreListingsAsCustomPostTypesUpgradeTaskHandler'] = $container->service(
            function( $container ) {
                return new AWPCP_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler(
                    awpcp_categories_registry(),
                    awpcp_legacy_listings_metadata(),
                    $container['WordPress'],
                    $container['wpdb']
                );
            }
        );
    }
}
