<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

/**
 * Unit tests for Quick View Listing Table Action.
 */
class AWPCP_QuickViewListingTableActionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function test_should_show_action_for_is_always_shown() {
        $action = new AWPCP_QuickViewListingTableAction();

        // Execution.
        $should = $action->should_show_action_for( null );

        // Verification.
        $this->assertTrue( $should );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_label_returns_a_string() {
        $action = new AWPCP_QuickViewListingTableAction();

        // Execution.
        $label = $action->get_label( null );

        // Verification.
        $this->assertInternalType( 'string', $label );
    }
}
