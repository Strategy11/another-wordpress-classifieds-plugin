<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

/**
 * Unit tests for Keyword Listings Table Search Mode.
 */
class AWPCP_KeywordListingsTableSearchModeTest extends AWPCP_ListingsTableSearchModeTestCase {

    /**
     * @since 4.0.0
     */
    protected function get_test_subject() {
        return new AWPCP_KeywordListingsTableSearchMode();
    }

    /**
     * @param string $search_term   The search term.
     * @since 4.0.0
     */
    protected function verify_pre_get_posts_execution( $search_term ) {
        $this->assertTrue( isset( $this->query->query_vars['s'] ) );
        $this->assertEquals( $search_term, $this->query->query_vars['s'] );
    }
}
