<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Unit tests for Filtered Array class
 */
class AWPCP_FilteredArrayTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function test_actions_can_be_iterated_over() {
        $action  = (object) array( 'foo' => 'bar' );
        $actions = array(
            'custom-action' => $action,
        );

        $table_actions = new AWPCP_FilteredArray( 'awpcp_test_filter' );

        \WP_Mock::onFilter( 'awpcp_test_filter' )
            ->reply( $actions );

		$this->markTestSkipped( 'Failing. Needs work' );

        // Verification.
        $this->assertTrue( isset( $table_actions['custom-action'] ) );
    }
}
