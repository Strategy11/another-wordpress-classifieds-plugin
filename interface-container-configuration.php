<?php
/**
 * @package AWPCP
 */

/**
 * Interface for a Container Configuration object.
 */
interface AWPCP_ContainerConfigurationInterface {

    /**
     * Modifies the given dependency injection container.
     *
     * @param AWPCP_Container $container
     */
    public function modify( $container );
}
