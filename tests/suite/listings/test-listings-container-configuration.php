<?php
/**
 * @package AWPCP\Tests\Plugin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Tests for Listings Container Configuration.
 */
class AWPCP_ListingsContainerConfigurationTest extends AWPCP_ContainerConfigurationTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        Functions\when( 'awpcp_roles_and_capabilities' )->justReturn( null );
        Functions\when( 'awpcp_categories_registry' )->justReturn( null );
    }

    /**
     * @since 4.0.0
     */
    public function class_definitions_provider() {
        return [
            [ 'CategoriesLogic' ],
            [ 'CategoriesCollection' ],
            [ 'CategoryPresenter' ],
            [ 'ListingAuthorization' ],
        ];
    }

    /**
     * @since 4.0.0
     */
    public function test_listings_logic_definition() {
        $this->markTestSkipped();

        $this->test_class_definition(
            'ListingsLogic',
            $this->get_test_subject()
        );
    }

    /**
     * @since 4.0.0
     */
    protected function get_test_subject() {
        return new AWPCP_ListingsContainerConfiguration();
    }

    /**
     * @since 4.0.0
     */
    public function test_query_integration_definition() {
        // phpcs:disable
        $GLOBALS['wpdb'] = null;
        // phpcs:enable

        $this->test_class_definition( 'QueryIntegration' );
    }

    /**
     * @since 4.0.0
     */
    public function test_listing_renewed_email_notifications_definition() {
        $this->test_class_definition(
            'ListingRenewedEmailNotifications',
            $this->get_test_subject(),
            function( $container ) {
                $container['ListingRenderer']  = null;
                $container['TemplateRenderer'] = null;
                $container['Settings']         = null;
            }
        );
    }
}
