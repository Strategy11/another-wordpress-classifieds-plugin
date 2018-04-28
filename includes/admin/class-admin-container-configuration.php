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
                $container['listing_post_type'],
                $container,
                $container['ListingsTableViewsHandler'],
                $container['ListingsTableActionsHandler']
            );
        } );

        /* Listings Container */

        $container['ListingsTableActionsHandler'] = $container->service( function( $container ) {
            return new AWPCP_ListTableActionsHandler(
                $container['ListingsTableActions'],
                awpcp_listings_collection(),
                awpcp_request()
            );
        } );

        $container['ListingsTableActions'] = $container->service( function( $container ) {
            return new AWPCP_FilteredArray( 'awpcp_list_table_actions_listings' );
        } );

        $container['ListingsTableViewsHandler'] = $container->service( function( $container ) {
            return new AWPCP_ListTableViewsHandler(
                $container['ListingsTableViews'],
                awpcp_request()
            );
        } );

        $container['ListingsTableViews'] = $container->service( function( $container ) {
            return new AWPCP_FilteredArray( 'awpcp_list_table_views_listings' );
        } );

        $container['NewListingTableView'] = $container->service( function( $container ) {
            return new AWPCP_NewListingTableView(
                $container['ListingsCollection']
            );
        } );

        $container['FeaturedListingTableView'] = $container->service( function( $container ) {
            return new AWPCP_FeaturedListingTableView(
                $container['ListingsCollection']
            );
        } );

        $container['ExpiredListingTableView'] = $container->service( function( $container ) {
            return new AWPCP_ExpiredListingTableView(
                $container['ListingsCollection']
            );
        } );

        $container['AwaitingApprovalListingTableView'] = $container->service( function( $container ) {
            return new AWPCP_AwaitingApprovalListingTableView(
                $container['ListingsCollection']
            );
        } );

        $container['ImagesAwaitingApprovalListingTableView'] = $container->service( function( $container ) {
            return new AWPCP_ImagesAwaitingApprovalListingTableView(
                $container['ListingsCollection']
            );
        } );

        $container['QuickViewListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_QuickViewListingTableAction();
        } );

        $container['QuickViewListingAdminPage'] = $container->service( function( $container ) {
            return new AWPCP_QuickViewListingAdminPage(
                $container['ListingsContentRenderer'],
                awpcp_listing_renderer(),
                $container['ListingsCollection'],
                $container['TemplateRenderer'],
                $container['WordPress'],
                awpcp_request()
            );
        } );

        $container['EnableListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_EnableListingTableAction(
                awpcp_listings_api(),
                $container['ListingRenderer']
            );
        } );

        $container['DisableListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_DisableListingTableAction(
                awpcp_listings_api(),
                $container['ListingRenderer']
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

        $container['SendToFacebookPageListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_SendToFacebookPageListingTableAction(
                $container['SendListingToFacebookHelper'],
                $container['WordPress']
            );
        } );

        $container['SendToFacebookGroupListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_SendToFacebookGroupListingTableAction(
                $container['SendListingToFacebookHelper'],
                $container['WordPress']
            );
        } );

        $container['ListingFieldsMetabox'] = $container->service( function( $container ) {
            return new AWPCP_ListingFieldsMetabox(
                $container['listing_post_type'],
                $container['ListingsLogic'],
                $container['FormFieldsData'],
                $container['FormFieldsValidator'],
                $container['FormFields'],
                $container['WordPress']
            );
        } );
    }
}
