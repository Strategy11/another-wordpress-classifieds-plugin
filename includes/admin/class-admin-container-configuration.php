<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Container configuration for common classes used on the Admin Dashboard.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AWPCP_AdminContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * @param object $container     An instance of Container.
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modify( $container ) {
        $container['Admin'] = $container->service( function( $container ) {
            return new AWPCP_Admin(
                $container,
                $container['ListingsTableActionsHandler']
            );
        } );

        /* Listings Container */

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

        $container['SendAccessKeyListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_SendAccessKeyListingTableAction(
                $container['EmailFactory'],
                $container['ListingRenderer']
            );
        } );

        $container['MarkAsSPAMListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_MarkAsSPAMListingTableAction(
                $container['SPAMSubmitter'],
                $container['ListingsLogic'],
                $container['WordPress']
            );
        } );

        $container['UnflagListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_UnflagListingTableAction(
                $container['ListingsLogic'],
                $container['ListingRenderer']
            );
        } );

        $container['RenewListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_RenewListingTableAction(
                $container['ListingsLogic'],
                $container['ListingRenderer'],
                $container['ListingRenewedEmailNotifications']
            );
        } );

        $container['MakeFeaturedListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_MakeFeaturedListingTableAction(
                $container['ListingRenderer'],
                $container['WordPress']
            );
        } );

        $container['MakeStandardListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_MakeStandardListingTableAction(
                $container['ListingRenderer'],
                $container['WordPress']
            );
        } );

        $container['MarkReviewedListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_MarkReviewedListingTableAction(
                $container['ListingRenderer'],
                $container['WordPress']
            );
        } );

        $container['MarkSoldListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_MarkSoldListingTableAction(
                $container['WordPress']
            );
        } );

        $container['MarkUnsoldListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_MarkUnsoldListingTableAction(
                $container['WordPress']
            );
        } );
    }
}
