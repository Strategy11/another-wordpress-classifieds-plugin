<?php
/**
 * @package AWPCP
 */

/**
 * Main Container Configuration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AWPCP_ContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * Modifies the given dependency injection container.
     *
     * @param AWPCP_Container $container    The Dependency Injection Container.
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function modify( $container ) {
        $container['wpdb'] = function( $container ) {
            return $GLOBALS['wpdb'];
        };

        $container['Uninstaller'] = $container->service( function( $container ) {
            return new AWPCP_Uninstaller(
                $container['plugin_basename'],
                $container['listing_post_type'],
                $container['ListingsLogic'],
                $container['ListingsCollection'],
                $container['CategoriesLogic'],
                $container['CategoriesCollection'],
                $container['RolesAndCapabilities'],
                $container['Settings'],
                $container['wpdb']
            );
        } );

        $container['Request'] = $container->service( function( $container ) {
            return awpcp_request();
        } );

        $container['Settings'] = $container->service( function( $container ) {
            return awpcp_settings_api();
        } );

        $container['Payments'] = $container->service( function( $container ) {
            return new AWPCP_PaymentsAPI(
                $container['Request']
            );
        } );

        $container['RolesAndCapabilities'] = $container->service( function( $container ) {
            return awpcp_roles_and_capabilities();
        } );

        $container['UsersCollection'] = $container->service( function( $container ) {
            return new AWPCP_UsersCollection(
                $container['Payments'],
                $container['Settings'],
                $container['wpdb']
            );
        } );

        $container['EmailFactory'] = $container->service( function( $container ) {
            return new AWPCP_EmailFactory();
        } );

        $container['AkismetWrapperFactory'] = $container->service( function( $container ) {
            return new AWPCP_AkismetWrapperFactory();
        } );

        $container['ListingAkismetDataSource'] = $container->service( function( $container ) {
            return new AWPCP_ListingAkismetDataSource(
                $container['ListingRenderer']
            );
        } );

        $container['SPAMSubmitter'] = $container->service( function( $container ) {
            return new AWPCP_SpamSubmitter(
                $container['AkismetWrapperFactory'],
                $container['ListingAkismetDataSource']
            );
        } );

        $container['TemplateRenderer'] = $container->service( function( $container ) {
            return new AWPCP_Template_Renderer();
        } );

        $container['SendListingToFacebookHelper'] = $container->service( function( $container ) {
            return new AWPCP_SendToFacebookHelper(
                AWPCP_Facebook::instance(),
                awpcp_attachment_properties(),
                awpcp_attachments_collection(),
                $container['ListingRenderer'],
                $container['WordPress']
            );
        } );

        $container['FormFields'] = $container->service( function( $container ) {
            return new AWPCP_FormFields();
        } );

        $container['FormFieldsData'] = $container->service( function( $container ) {
            return new AWPCP_FormFieldsData(
                $container['ListingAuthorization'],
                $container['ListingRenderer'],
                awpcp_request()
            );
        } );

        $container['FormFieldsValidator'] = $container->service( function( $container ) {
            return new AWPCP_FormFieldsValidator(
                $container['ListingAuthorization'],
                $container['Settings']
            );
        } );

        $container['ListingDetailsFormFieldsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_FormFieldsRenderer(
                'awpcp_listing_details_form_fields'
            );
        } );

        $container['ListingDateFormFieldsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_FormFieldsRenderer(
                'awpcp_listing_date_form_fields'
            );
        } );

        $container['HTMLRenderer'] = $container->service( function( $container ) {
            return new AWPCP_HTML_Renderer();
        } );

        // Media.
        $container['FileTypes'] = $container->service( function( $container ) {
            return new AWPCP_FileTypes( $container['Settings'] );
        } );

        // Components.
        $container['UserSelector'] = $container->service( function( $container ) {
            return new AWPCP_UserSelector(
                $container['UsersCollection'],
                $container['TemplateRenderer'],
                $container['Request']
            );
        } );

        $container['MediaCenterComponent'] = $container->service( function ( $container ) {
            return new AWPCP_MediaCenterComponent(
                $container['ListingUploadLimits'],
                $container['AttachmentsCollection'],
                $container['TemplateRenderer'],
                $container['Settings']
            );
        } );
    }
}
