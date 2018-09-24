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
                $container['ListingsTableActionsHandler'],
                $container['ListingsTableNavHandler'],
                $container['ListingsTableSearchHandler'],
                $container['ListingsTableColumnsHandler'],
                $container['ListTableRestrictions']
            );
        } );

        $container['ListTableRestrictions'] = $container->service( function( $container ) {
            return new AWPCP_ListTableRestrictions(
                $container['RolesAndCapabilities'],
                $container['Request']
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

        $container['FlaggedListingTableView'] = $container->service( function( $container ) {
            return new AWPCP_FlaggedListingTableView(
                $container['ListingsCollection']
            );
        } );

        $container['IncompleteListingTableView'] = $container->service( function( $container ) {
            return new AWPCP_IncompleteListingTableView(
                $container['ListingsCollection']
            );
        } );

        $container['UnverifiedListingTableView'] = $container->service( function( $container ) {
            return new AWPCP_UnverifiedListingTableView(
                $container['ListingsCollection']
            );
        } );

        $container['CompleteListingTableView'] = $container->service( function( $container ) {
            return new AWPCP_CompleteListingTableView(
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
                $container['RolesAndCapabilities'],
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
                $container['RolesAndCapabilities'],
                $container['WordPress']
            );
        } );

        $container['MarkUnsoldListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_MarkUnsoldListingTableAction(
                $container['RolesAndCapabilities'],
                $container['WordPress']
            );
        } );

        $container['MarkPaidListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_MarkPaidListingTableAction(
                $container['RolesAndCapabilities'],
                $container['ListingsLogic'],
                $container['ListingRenderer']
            );
        } );

        $container['MarkVerifiedListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_MarkVerifiedListingTableAction(
                $container['RolesAndCapabilities'],
                $container['ListingsLogic'],
                $container['ListingRenderer']
            );
        } );

        $container['SendToFacebookPageListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_SendToFacebookPageListingTableAction(
                $container['SendListingToFacebookHelper'],
                $container['RolesAndCapabilities'],
                $container['WordPress']
            );
        } );

        $container['SendToFacebookGroupListingTableAction'] = $container->service( function( $container ) {
            return new AWPCP_SendToFacebookGroupListingTableAction(
                $container['SendListingToFacebookHelper'],
                $container['RolesAndCapabilities'],
                $container['WordPress']
            );
        } );

        $container['ListingsTableNavHandler'] = $container->service( function( $container ) {
            return new AWPCP_ListingsTableNavHandler(
                $container['HTMLRenderer'],
                $container['Request']
            );
        } );

        $container['ListingsTableSearchHandler'] = $container->service( function( $container ) {
            return new AWPCP_ListTableSearchHandler(
                $container['ListingsTableSearchModes'],
                $container['HTMLRenderer'],
                $container['Request']
            );
        } );

        $container['ListingsTableSearchModes'] = $container->service( function( $container ) {
            return new AWPCP_FilteredArray( 'awpcp_list_table_search_listings' );
        } );

        $container['IDListingsTableSearchMode'] = $container->service( function( $container ) {
            return new AWPCP_IDListingsTableSearchMode();
        } );

        $container['KeywordListingsTableSearchMode'] = $container->service( function( $container ) {
            return new AWPCP_KeywordListingsTableSearchMode();
        } );

        $container['TitleListingsTableSearchMode'] = $container->service( function( $container ) {
            return new AWPCP_TitleListingsTableSearchMode();
        } );

        $container['UserListingsTableSearchMode'] = $container->service( function( $container ) {
            return new AWPCP_UserListingsTableSearchMode();
        } );

        $container['ContactNameListingsTableSearchMode'] = $container->service( function( $container ) {
            return new AWPCP_ContactNameListingsTableSearchMode();
        } );

        $container['ContactPhoneListingsTableSearchMode'] = $container->service( function( $container ) {
            return new AWPCP_ContactPhoneListingsTableSearchMode();
        } );

        $container['ContactEmailListingsTableSearchMode'] = $container->service( function( $container ) {
            return new AWPCP_ContactEmailListingsTableSearchMode();
        } );

        $container['PayerEmailListingsTableSearchMode'] = $container->service( function( $container ) {
            return new AWPCP_PayerEmailListingsTableSearchMode();
        } );

        $container['LocationListingsTableSearchMode'] = $container->service( function( $container ) {
            return new AWPCP_LocationListingsTableSearchMode();
        } );

        $container['ListingInformationMetabox'] = $container->service( function( $container ) {
            return new AWPCP_ListingInfromationMetabox(
                $container['ListingsPayments'],
                $container['ListingRenderer'],
                $container['Payments'],
                $container['TemplateRenderer'],
                $container['Request']
            );
        } );

        $container['ListingFieldsMetabox'] = $container->service( function( $container ) {
            return new AWPCP_ListingFieldsMetabox(
                $container['listing_post_type'],
                $container['RolesAndCapabilities'],
                $container['ListingsLogic'],
                $container['FormFieldsData'],
                $container['FormFieldsValidator'],
                $container['ListingDetailsFormFieldsRenderer'],
                $container['ListingDateFormFieldsRenderer'],
                $container['MediaCenterComponent'],
                $container['TemplateRenderer'],
                $container['WordPress']
            );
        } );

        $container['ListingsTableColumnsHandler'] = $container->service( function( $container ) {
            return new AWPCP_ListingsTableColumnsHandler(
                $container['listing_post_type'],
                $container['listing_category_taxonomy'],
                $container['ListingRenderer'],
                $container['ListingsCollection']
            );
        } );

        $container['TestSSLClientAjaxHandler'] = $container->service( function( $container ) {
            return new AWPCP_TestSSLClientAjaxHandler();
        } );

        $this->register_importer_objects( $container );
    }

    /**
     * @since 4.0.0
     */
    private function register_importer_objects( $container ) {
        $container['ImporterDelegateFactory'] = $container->service( function( $container ) {
            return new AWPCP_CSV_Importer_Delegate_Factory( $container );
        } );

        $container['CSVImporterColumns'] = $container->service( function( $container ) {
            return new AWPCP_CSVImporterColumns();
        } );

        $container['ImporterFormStepsComponent'] = $container->service( function( $container ) {
            return new AWPCP_FormStepsComponent( new AWPCP_ImporterFormSteps() );
        } );

        $container['ImportListingsAdminPage'] = $container->service( function( $container ) {
            return new AWPCP_ImportListingsAdminPage(
                awpcp_csv_import_sessions_manager(),
                awpcp_csv_importer_factory(),
                $container['ImporterFormStepsComponent'],
                awpcp()->js,
                $container['Settings'],
                $container['Request']
            );
        } );

        $container['SupportedCSVHeadersAdminPage'] = $container->service( function( $container ) {
            return new AWPCP_SupportedCSVHeadersAdminPage(
                $container['CSVImporterColumns'],
                $container['TemplateRenderer']
            );
        } );

        $container['ExampleCSVFileAdminPage'] = $container->service( function( $container ) {
            return new AWPCP_ExampleCSVFileAdminPage(
                $container['CSVImporterColumns'],
                $container['TemplateRenderer']
            );
        } );
    }
}
