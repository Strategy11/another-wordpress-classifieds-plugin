<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

/**
 * Unit tests for Payer Email Listings Table Search Mode.
 */
class AWPCP_PayerEmailListingsTableSearchModeTest extends AWPCP_ListingsTableSearchModeTestCase {

    /**
     * @since 4.0.0
     */
    protected function get_test_subject() {
        return new AWPCP_PayerEmailListingsTableSearchMode();
    }

    /**
     * @param string $search_term   The search term.
     * @since 4.0.0
     */
    protected function verify_pre_get_posts_execution( $search_term ) {
        $this->assertFalse( isset( $this->query->query_vars['s'] ) );
        $this->assertEquals( $search_term, $this->query->query_vars['classifieds_query']['payer_email'] );
    }
}

