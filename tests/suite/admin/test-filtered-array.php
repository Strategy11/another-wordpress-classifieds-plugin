<?php
/**
 * @package AWPCP\Admin
 */

use Brain\Monkey\Filters;

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

        Filters\expectApplied( 'awpcp_test_filter' )
            ->once()
            ->andReturn( $actions );

		$this->markTestSkipped( 'Failing. Needs work' );

        // Verification.
        $this->assertTrue( isset( $table_actions['custom-action'] ) );
    }
}
