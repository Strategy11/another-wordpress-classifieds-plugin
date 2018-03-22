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
     */
    public function modify( $container ) {
        $container['EmailFactory'] = $container->service( function( $container ) {
            return new AWPCP_EmailFactory();
        } );
    }
}
