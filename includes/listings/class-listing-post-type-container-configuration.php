<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Register constructors for classes necessary to provide the Listing custom
 * post type.
 */
class AWPCP_ListingPostTypeContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    public function modify( $container ) {
        $container['listing_post_type'] = 'awpcp_listing';
        $container['listing_category_taxonomy'] = 'awpcp_listing_category';

        $container['ListingsPermalinks'] = $container->service( function( $container ) {
            // TODO: Add dependencies to the container.
            return new AWPCP_ListingsPermalinks(
                $container['listing_post_type'],
                $container['listing_category_taxonomy'],
                awpcp_listing_renderer(),
                awpcp_settings_api()
            );
        } );
    }
}
