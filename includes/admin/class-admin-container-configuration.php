<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Container configuration for common classes used on the Admin Dashboard.
 */
class AWPCP_AdminContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * @param object $container     An instance of Container.
     */
    public function modify( $container ) {
        $container['Admin'] = $container->service( function( $container ) {
            return new AWPCP_Admin(
                $container,
                $container['ListingsTableActionsHandler']
            );
        } );

        /* Listings Container */

        $container['ListingsCollection'] = $container->service( function( $container ) {
            return new AWPCP_ListingsCollection(
                // TODO: add all these to the container.
                awpcp_listings_finder(),
                awpcp()->settings,
                $container['WordPress'],
                $GLOBALS['wpdb']
            );
        } );

        $container['ListingsTableActionsHandler'] = $container->service( function( $container ) {
            return new AWPCP_ListTableActionsHandler(
                $container['listing_post_type'],
                $container['ListingsTableActions'],
                awpcp_listings_collection(),
                awpcp_request()
            );
        } );

        $container['ListingsTableActions'] = $container->service( function( $container ) {
            return new AWPCP_ListTableActions( 'listings' );
        } );

        $container['QuickViewListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_QuickViewListingTableAction();
        } );

        $container['QuickViewListingAdminPage'] = $container->service( function( $container ) {
            return new AWPCP_QuickViewListingAdminPage(
                $container['ListingsContentRenderer'],
                awpcp_listing_renderer(),
                $container['ListingsCollection'],
                awpcp_template_renderer(),
                $container['WordPress'],
                awpcp_request()
            );
        } );

        $container['EnableListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_EnableListingTableAction(
                awpcp_listings_api(),
                awpcp_listing_renderer()
            );
        } );

        $container['DisableListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_DisableListingTableAction(
                awpcp_listings_api(),
                awpcp_listing_renderer()
            );
        } );
    }
}
