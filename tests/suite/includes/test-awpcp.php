<?php
/**
 * @package AWPCP\Tests
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for the plugin's main class.
 */
class AWPCP_TestAWPCP extends AWPCP_UnitTestCase {

    /**
     * Code executed at the before every test method is run.
     */
    public function setup() {
        parent::setup();

        $this->container = [];

        $this->awpcp = new AWPCP( $this->container );
    }

    /**
     * TODO: does this really tests something? We shuould be asking if the plugin integrations are properly wired up.
     */
    public function test_setup() {
        $this->markTestIncomplete();

        $this->awpcp->bootstrap();
        $this->awpcp->setup();

        $this->assertTrue( has_filter( 'awpcp-should-generate-opengraph-tags' ) );
        $this->assertTrue( has_filter( 'awpcp-should-generate-rel-canonical' ) );
        $this->assertTrue( has_filter( 'awpcp-should-generate-title' ) );
    }

    /**
     * @since 4.0.1
     */
    private function get_test_subject() {
        return new AWPCP( $this->container );
    }

    /**
     * Test that the Strip moduel was registered.
     */
    public function test_stripe_module_is_registered() {
        $modules = $this->awpcp->get_premium_modules_information();

        $this->assertContains( 'stripe', array_keys( $modules ) );
        $this->assertNotContains( 'private', $modules['stripe'] );
    }

    /**
     * TODO: The init() method is still too hard to test. We need to extract
     * smaller methods that are easier to test.
     *
     * @since 4.0.1
     */
    public function test_init_register_listing_payment_transaction_handler_hooks_on_ajax_requests() {
        $this->markTestIncomplete();

        $handler = Mockery::mock( 'AWPCP_ListingPaymentTransactionHandler' );

        $this->container['QueryIntegration']                = Mockery::mock( 'AWPCP_QueryIntegration' );
        $this->container['TermQueryIntegration']            = Mockery::mock( 'AWPCP_TermQueryIntegration' );
        $this->container['RemoveListingAttachmentsService'] = Mockery::mock( 'AWPCP_RemoveListingAttachmentsService' );
        $this->container['SendListingToFacebookHelper']     = Mockery::mock( 'AWPCP_SendToFacebookHelper' );
        $this->container['CategoriesListCache']             = Mockery::mock( 'AWPCP_CategoriesListCache' );

        Functions\when( 'awpcp_facebook_cache_helper' )->justReturn( Mockery::mock( 'AWPCP_FacebookCacheHelper' ) );

        Functions\when( 'wp_doing_ajax' )->justReturn( true );
        Functions\when( 'is_admin' )->justReturn( true );

        // Execution.
        $this->get_test_subject()->init();

        // Verification.
        $this->assertEquals( 20, has_action( 'awpcp-transaction-status-updated', [ $handler, 'transaction_status_updated' ] ) );
        $this->assertEquals( 20, has_action( 'awpcp-process-payment-transaction', [ $handler, 'transaction_status_updated' ] ) );
    }

    /**
     * @since 4.0.0.
     */
    public function test_admin_setup_configures_admin_init_handler() {
        $admin = Mockery::mock( 'AWPCP_Admin' );

        $this->container['Admin']                     = $admin;
        $this->container['SettingsIntegration']       = Mockery::mock( 'AWPCP_SettingsIntegration' );

        // Execution.
        $this->get_test_subject()->admin_setup();

        // Verification.
        $this->assertEquals( 10, has_action( 'admin_init', array( $admin, 'admin_init' ) ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_container_configurations() {
        // Execution.
        $configurations = $this->get_test_subject()->get_container_configurations();

        // Verification.
        $this->assertContains(
            'AWPCP_ContainerConfiguration',
            array_map(
                function( $obj ) {
                    return get_class( $obj );
                },
                $configurations
            )
        );
    }
}
