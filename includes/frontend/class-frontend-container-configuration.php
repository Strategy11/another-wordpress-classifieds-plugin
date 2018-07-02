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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modify( $container ) {
        $container['SubmitListingPage'] = $container->service( function( $container ) {
            return new AWPCP_SubmitListingPage(
                $container['SubmitListingSectionsGenerator'],
                $container['ListingsLogic'],
                $container['ListingsCollection'],
                $container['ListingAuthorization'],
                $container['Payments'],
                $container['Settings'],
                $container['Request']
            );
        } );

        $container['EditListingPage'] = $container->service( function( $container ) {
            return new AWPCP_EditListingPage(
                $container['SubmitListingSectionsGenerator'],
                $container['ListingRenderer'],
                $container['ListingsCollection'],
                $container['ListingAuthorization'],
                $container['Settings'],
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
                $container['CAPTCHA'],
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

        $container['ListingPostedData'] = $container->service( function( $container ) {
            return new AWPCP_ListingPostedData(
                $container['listing_category_taxonomy'],
                $container['FormFieldsData'],
                $container['ListingsLogic'],
                $container['ListingRenderer'],
                $container['Payments'],
                $container['ListingAuthorization'],
                $container['RolesAndCapabilities'],
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
                $container['FormFieldsValidator'],
                $container['PaymentInformationValidator'],
                $container['ListingPostedData'],
                $container['RolesAndCapabilities'],
                $container['Settings'],
                awpcp_ajax_response(),
                $container['Request']
            );
        } );

        $container['ClearListingInformationAjaxHandler'] = $container->service( function( $container ) {
            return new AWPCP_ClearListingInformationAjaxHandler(
                $container['ListingsLogic'],
                $container['ListingsCollection'],
                $container['RolesAndCapabilities'],
                $container['Settings'],
                awpcp_ajax_response(),
                $container['Request']
            );
        } );

        $container['CAPTCHA'] = $container->service( function( $container ) {
            return new AWPCP_CAPTCHA(
                $container['CAPTCHAProviderFactory']->get_captcha_provider(),
                $container['RolesAndCapabilities'],
                $container['Settings']
            );
        } );

        $container['CAPTCHAProviderFactory'] = $container->service( function( $container ) {
            return new AWPCP_CAPTCHAProviderFactory(
                $container['Settings'],
                $container['Request']
            );
        } );
    }
}
