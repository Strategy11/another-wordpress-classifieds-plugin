<?php
/**
 * @package AWPCP\Tests
 */

/**
 * Unit tests for Listings Table Search Modes.
 */
abstract class AWPCP_ListingsTableSearchModeTestCase extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function test_get_name() {
        // Execution and Verification.
        $this->assertNotEmpty( $this->get_test_subject()->get_name() );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts() {
        $search_term = 'Something';

        $this->query = (object) [
            'query_vars' => [
                's' => $search_term,
            ],
        ];

        // Execution.
        $this->get_test_subject()->pre_get_posts( $this->query );

        // Verification.
        $this->verify_pre_get_posts_execution( $search_term );
    }
}
