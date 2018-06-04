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
                $container['SubmitListingSectionsGenerator']
            );
        } );

        $container['SubmitListingSectionsGenerator'] = $container->service( function( $container ) {
            return new AWPCP_SubmitLisitngSectionsGenerator();
        } );

        $container['CreateEmptyListingAjaxHandler'] = $container->service( function( $container ) {
            return new AWPCP_CreateEmptyListingAjaxHandler(
                $container['listing_category_taxonomy'],
                $container['ListingsLogic'],
                awpcp_ajax_response(),
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
    }
}
