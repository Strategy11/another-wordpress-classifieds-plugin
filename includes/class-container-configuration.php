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
        // @phpcs:disable PEAR.Functions.FunctionCallSignature.CloseBracketLine
        // @phpcs:disable PEAR.Functions.FunctionCallSignature.ContentAfterOpenBracket
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

        $container['ModulesManager'] = $container->service( function( $container ) {
            return new AWPCP_ModulesManager(
                $container['Plugin'],
                awpcp_upgrade_tasks_manager(),
                awpcp_licenses_manager(),
                awpcp_modules_updater(),
                $container['LicensesSettings'],
                $container['Request']
            );
        } );

        $container['Request'] = $container->service( function( $container ) {
            return new AWPCP_Request();
        } );

        $container['Payments'] = $container->service( function( $container ) {
            return new AWPCP_PaymentsAPI(
                $container['Request']
            );
        } );

        $container['RolesAndCapabilities'] = $container->service( function( $container ) {
            return new AWPCP_RolesAndCapabilities(
                $container['Settings'],
                $container['Request']
            );
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
                awpcp_facebook_integration(),
                $container['ListingRenderer'],
                $container['ListingsCollection'],
                $container['Settings'],
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
                $container['Request']
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

        $container['EmailHelper'] = $container->service( function( $container ) {
            return new AWPCP_EmailHelper(
                $container['Settings']
            );
        } );
        // @phpcs:enable

        $this->register_upgrade_task_handlers( $container );
    }

    /**
     * Register constructors for Upgrade Task Handlers.
     *
     * @since 4.0.0
     */
    private function register_upgrade_task_handlers( $container ) {
        $container['FixIDCollisionForListingCategoriesUpgradeTaskHandler'] = $container->service(
            function( $container ) {
                return new AWPCP_FixIDCollisionForListingCategoriesUpgradeTaskHandler(
                    $container['listing_category_taxonomy'],
                    awpcp_categories_registry(),
                    $container['WordPress'],
                    $container['wpdb']
                );
            }
        );

        $container['StoreCategoriesOrderAsTermMetaTaskHandler'] = $container->service(
            function( $container ) {
                return new AWPCP_StoreCategoriesOrderAsTermMetaTaskHandler(
                    awpcp_categories_collection(),
                    awpcp_categories_registry(),
                    $container['WordPress'],
                    $container['wpdb']
                );
            }
        );

        $container['ListingsRegistry'] = $container->service(
            function( $container ) {
                return new AWPCP_ListingsRegistry(
                    $container['ArrayOptions']
                );
            }
        );

        $container['FixIDCollisionForListingsUpgradeTaskHandler'] = $container->service(
            function( $container ) {
                return new AWPCP_FixIDCollisionForListingsUpgradeTaskHandler(
                    $container['ListingsRegistry'],
                    $container['ListingsCollection'],
                    $container['WordPress'],
                    $container['wpdb']
                );
            }
        );
    }
}
