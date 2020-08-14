<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

/**
 * Unit tests for Location Listings Table Search Mode-
 */
class AWPCP_LocationListingsTableSearchModeTest extends AWPCP_ListingsTableSearchModeTestCase {

    /**
     * @since 4.0.0
     */
    protected function get_test_subject() {
        return new AWPCP_LocationListingsTableSearchMode();
    }

    /**
     * @param string $search_term   The search term.
     * @since 4.0.0
     */
    protected function verify_pre_get_posts_execution( $search_term ) {
        $this->assertEquals( $search_term, $this->query->query_vars['classifieds_query']['region'] );
    }
}
