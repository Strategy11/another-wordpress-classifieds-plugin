<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Container configuration for objects used on the frontend.
 */
class AWPCP_FrontendContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * @param AWPCP_Container $container    The plugin's container.
     * @since 4.0.0
     */
    public function modify( $container ) {
        $container['SubmitListingPage'] = $container->service( function( $container ) {
            return new AWPCP_SubmitListingPage(
                $container['SubmitListingSectionsGenerator'],
                $container['ListingsLogic'],
                $container['ListingsCollection'],
                $container['Payments'],
                $container['Settings'],
                $container['Request']
            );
        } );

        $container['EditListingPage'] = $container->service( function( $container ) {
            return new AWPCP_EditListingPage(
                $container['SubmitListingSectionsGenerator'],
                $container['ListingsCollection'],
                $container['Request']
            );
        } );

        $container['SubmitListingSectionsGenerator'] = $container->service( function( $container ) {
            return new AWPCP_SubmitLisitngSectionsGenerator();
        } );

        $container['CreateEmptyListingAjaxHandler'] = $container->service( function( $container ) {
            return new AWPCP_CreateEmptyListingAjaxHandler(
                $container['listing_category_taxonomy'],
                $container['ListingsLogic'],
                $container['PaymentInformationValidator'],
                $container['Payments'],
                $container['RolesAndCapabilities'],
                awpcp_ajax_response(),
                $container['Settings'],
                $container['Request']
            );
        } );

        $container['UpdateSubmitListingSectionsAjaxHandler'] = $container->service( function( $container ) {
            return new AWPCP_UpdateSubmitListingSectionsAjaxHandler(
                $container['SubmitListingSectionsGenerator'],
                $container['ListingsCollection'],
                awpcp_ajax_response(),
                $container['Request']
            );
        } );

        $container['SaveListingInformationAjaxHandler'] = $container->service( function( $container ) {
            return new AWPCP_SaveListingInformationAjaxHandler(
                $container['listing_category_taxonomy'],
                $container['ListingsLogic'],
                $container['ListingRenderer'],
                $container['ListingsCollection'],
                $container['Payments'],
                $container['ListingAuthorization'],
                $container['RolesAndCapabilities'],
                $container['FormFieldsValidator'],
                $container['PaymentInformationValidator'],
                $container['FormFieldsData'],
                $container['Settings'],
                awpcp_ajax_response(),
                $container['Request']
            );
        } );
    }
}
