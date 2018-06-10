<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Register constructors for classes necessary to provide the Listing custom
 * post type.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AWPCP_ListingsContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * @param object $container     An instance of Container.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modify( $container ) {
        $container['listing_post_type']         = 'awpcp_listing';
        $container['listing_category_taxonomy'] = 'awpcp_listing_category';

        $container['ListingsPermalinks'] = $container->service(
            function( $container ) {
                // TODO: Add dependencies to the container.
                return new AWPCP_ListingsPermalinks(
                    $container['listing_post_type'],
                    $container['listing_category_taxonomy'],
                    awpcp_listing_renderer(),
                    awpcp_settings_api()
                );
            }
        );

        $container['ListingsContent'] = $container->service(
            function( $container ) {
                return new AWPCP_ListingsContent(
                    $container['listing_post_type'],
                    $container['ListingsContentRenderer'],
                    $container['WordPress']
                );
            }
        );

        $container['ListingsContentRenderer'] = $container->service(
            function( $container ) {
                return new AWPCP_ListingsContentRenderer(
                    $container['ListingRenderer']
                );
            }
        );

        $container['ListingRenderer'] = $container->service(
            function( $container ) {
                return new AWPCP_ListingRenderer(
                    awpcp_categories_collection(),
                    awpcp_basic_regions_api(),
                    awpcp_payments_api(),
                    $container['WordPress']
                );
            }
        );

        $container['QueryIntegration'] = $container->service(
            function( $container ) {
                return new AWPCP_QueryIntegration(
                    $container['listing_post_type'],
                    $container['listing_category_taxonomy'],
                    $container['Settings'],
                    $GLOBALS['wpdb']
                );
            }
        );

        $container['ListingsCollection'] = $container->service( function( $container ) {
            return new AWPCP_ListingsCollection(
                // TODO: add all these to the container.
                $container['Settings'],
                $container['WordPress'],
                $GLOBALS['wpdb']
            );
        } );

        $container['ListingsLogic'] = $container->service( function( $container ) {
            return new AWPCP_ListingsAPI(
                awpcp_attachments_logic(),
                awpcp_attachments_collection(),
                $container['ListingRenderer'],
                $container['ListingsCollection'],
                $container['RolesAndCapabilities'],
                awpcp_request(),
                $container['Settings'],
                $container['WordPress'],
                $GLOBALS['wpdb']
            );
        } );

        $container['ListingAuthorization'] = $container->service( function( $container ) {
            return new AWPCP_ListingAuthorization(
                $container['ListingRenderer'],
                awpcp_roles_and_capabilities(),
                $container['Settings'],
                $container['Request']
            );
        } );

        $container['ListingUploadLimits'] = $container->service( function( $container ) {
            return new AWPCP_ListingUploadLimits(
                awpcp_attachments_collection(),
                awpcp_file_types(),
                $container['ListingRenderer'],
                $container['Settings']
            );
        } );

        $container['ListingRenewedEmailNotifications'] = $container->service( function( $container ) {
            return new AWPCP_ListingRenewedEmailNotifications(
                $container['ListingRenderer'],
                $container['TemplateRenderer'],
                $container['Settings']
            );
        } );

        $container['PaymentInformationValidator'] = $container->service( function( $container ) {
            return new AWPCP_PaymentInformationValidator(
                $container['listing_category_taxonomy'],
                $container['Payments'],
                $container['RolesAndCapabilities']
            );
        } );

        $container['CategoriesLogic'] = $container->service( function( $container ) {
            return new AWPCP_Categories_Logic(
                $container['listing_category_taxonomy'],
                $container['ListingsLogic'],
                $container['ListingsCollection'],
                $container['WordPress']
            );
        } );

        $container['CategoriesCollection'] = $container->service( function( $container ) {
            return new AWPCP_Categories_Collection(
                $container['listing_category_taxonomy'],
                $container['WordPress']
            );
        } );

        $container['AttachmentsCollection'] = $container->service( function( $container ) {
            return new AWPCP_Attachments_Collection(
                $container['FileTypes'],
                $container['WordPress']
            );
        } );
    }
}
