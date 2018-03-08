<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Register constructors for classes necessary to provide the Listing custom
 * post type.
 */
class AWPCP_ListingPostTypeContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * @param object $container     An instance of Container.
     * @since 4.0.0
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
    }
}
