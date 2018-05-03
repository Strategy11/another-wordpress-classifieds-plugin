<?php
/**
 * @package AWPCP
 */

/**
 * Main Container Configuration.
 */
class AWPCP_ContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * Modifies the given dependency injection container.
     *
     * @param AWPCP_Container $container    The Dependency Injection Container.
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function modify( $container ) {
        $container['Request'] = $container->service( function( $container ) {
            return awpcp_request();
        } );

        $container['Settings'] = $container->service( function( $container ) {
            return awpcp_settings_api();
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
                $container['ListingRenderer'],
                awpcp_request()
            );
        } );

        $container['FormFieldsValidator'] = $container->service( function( $container ) {
            return new AWPCP_FormFieldsValidator(
                $container['Settings']
            );
        } );

        $container['HTMLRenderer'] = $container->service( function( $container ) {
            return new AWPCP_HTML_Renderer();
        } );
    }
}
