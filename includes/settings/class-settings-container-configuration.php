<?php
/**
 * @package AWPCP\Settings
 */

/**
 * Register constructor for classes necessary to support plugin settings.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AWPCP_SettingsContainerConfiguration implements AWPCP_ContainerConfigurationInterface {

    /**
     * Modifies the given dependency injection container.
     *
     * @param AWPCP_Container $container    An instance of Container.
     */
    public function modify( $container ) {
        $container['Settings'] = $container->service( function() {
            return new AWPCP_Settings_API(
                awpcp()->container['SettingsManager']
            );
        } );

        $container['WordPressPageEvents'] = $container->service( function() {
            return new AWPCP_WordPressPageEvents();
        } );

        $container['SettingsIntegration'] = $container->service( function( $container ) {
            return new AWPCP_SettingsIntegration(
                [
                    'awpcp_admin_load_awpcp-admin-settings',
                    'awpcp_admin_load_awpcp-admin-credit-plans',
                ],
                $container['SettingsManager'],
                $container['SettingsValidator'],
                $container['SettingsRenderer'],
                $container['Settings']
            );
        } );

        $container['SettingsValidator'] = $container->service( function( $container ) {
            return new AWPCP_SettingsValidator(
                $container['Settings'],
                $container['Request']
            );
        } );

        $container['SettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_SettingsRenderer(
                $container['SettingsRenderers'],
                $container['SettingsManager']
            );
        } );

        $container['ListingsSettings'] = $container->service( function( $container ) {
            return new AWPCP_ListingsSettings(
                $container['Settings']
            );
        } );

        $container['PagesSettings'] = $container->service( function( $container ) {
            return new AWPCP_PagesSettings();
        } );

        $container['PaymentSettings'] = $container->service( function( $container ) {
            return new AWPCP_PaymentSettings( $container['Settings'] );
        } );

        $container['AppearanceSettings'] = $container->service( function( $container ) {
            return new AWPCP_AppearanceSettings();
        } );

        $container['LicensesSettings'] = $container->service( function( $container ) {
            return new AWPCP_LicensesSettings(
                $container['SettingsManager']
            );
        } );

        $this->define_settings_renderers( $container );
    }

    /**
     * @since 4.0.0
     */
    private function define_settings_renderers( $container ) {
        $container['SettingsRenderers'] = $container->service( function( $container ) {
            return new AWPCP_FilteredArray( 'awpcp_settings_renderers' );
        } );

        $container['CheckboxSettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_CheckboxSettingsRenderer(
                $container['Settings']
            );
        } );

        $container['SelectSettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_SelectSettingsRenderer(
                $container['Settings']
            );
        } );

        $container['TextareaSettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_TextareaSettingsRenderer(
                $container['Settings']
            );
        } );

        $container['RadioSettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_RadioSettingsRenderer(
                $container['Settings']
            );
        } );

        $container['TextfieldSettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_TextfieldSettingsRenderer(
                $container['Settings']
            );
        } );

        $container['ChoiceSettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_ChoiceSettingsRenderer(
                $container['Settings']
            );
        } );

        $container['CategoriesSettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_CategoriesSettingsRenderer(
                $container['Settings']
            );
        } );

        $container['LicenseSettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_LicenseSettingsRenderer(
                awpcp_licenses_manager(),
                $container['Settings']
            );
        } );

        $container['WordPressPageSettingsRenderer'] = $container->service( function( $container ) {
            return new AWPCP_WordPressPageSettingsRenderer(
                $container['Settings']
            );
        } );
    }
}
