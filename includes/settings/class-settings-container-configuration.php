<?php
/**
 * @package AWPCP\Settings
 */

/**
 * Register constructor for classes necessary to support plugin settings.
 */
class AWPCP_SettingsContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * Modifies the given dependency injection container.
     *
     * @param AWPCP_Container $container    An instance of Container.
     */
    public function modify( $container ) {
        $container['Settings'] = $container->service( function() {
            return new AWPCP_Settings_API();
        } );

        $container['WordPressPageSettingsType'] = $container->service( function() {
            return new AWPCP_WordPressPageSettingsType();
        } );

        $container['WordPressPageEvents'] = $container->service( function() {
            return new AWPCP_WordPressPageEvents();
        } );
    }
}
