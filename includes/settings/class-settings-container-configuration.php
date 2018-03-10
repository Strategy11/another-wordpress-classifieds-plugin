<?php
/**
 * @package AWPCP\Settings
 */

/**
 * Register constructor for classes necessary to support plugin settings.
 */
class AWPCP_SettingsContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    public function modify( $container ) {
        $container['WordPressPageSettingsType'] = $container->service( function() {
            return new AWPCP_WordPressPageSettingsType();
        } );

        $container['WordPressPageEvents'] = $container->service( function() {
            return new AWPCP_WordPressPageEvents();
        } );
    }
}
