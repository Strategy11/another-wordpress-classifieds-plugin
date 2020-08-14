<?php
/**
 * @package AWPCP\Tests\Listings
 */

/**
 * Tests for Listings Finder class.
 */
class AWPCP_TestListingsFinder extends AWPCP_UnitTestCase {

    public function __construct() {
        $this->db = Phake::mock( 'wpdb' );
    }

    public function test_find_handles_regions_parameter_set_to_empty_string() {
        Phake::when( $this->db )->get_results->thenReturn( array() );

        $finder = new AWPCP_ListingsFinder( $this->db );

        // Execution.
        $results = $finder->find( array( 'regions' => '' ) );

        // Verification. We expect the above call to find() to be executed without
        // errors.
        $this->assertEmpty( $results );
    }

    /**
     * @since 3.8.3
     */
    public function test_group_conditions() {
        $conditions = array( 'A', 'B' );

        $finder = new AWPCP_ListingsFinder( null );

        // Execution
        $grouped_conditions = $finder->group_conditions( $conditions );

        // Verification
        $this->assertEquals( '( A OR B )', $grouped_conditions );
    }

    /**
     * @since 3.8.3
     */
    public function test_group_conditions_with_strings() {
        $finder = new AWPCP_ListingsFinder( null );

        // Execution
        $conditions = $finder->group_conditions( '' );

        // Verification
        $this->assertEquals( '', $conditions );
    }

    /**
     * @since 3.8.3
     */
    public function test_group_conditions_with_arrays_with_one_element() {
        $conditions = array( 'condition' );

        $finder = new AWPCP_ListingsFinder( null );

        // Execution
        $grouped_conditions = $finder->group_conditions( $conditions );

        // Verification
        $this->assertEquals( $conditions[0], $grouped_conditions );
    }

    /**
     * @since 3.8.3
     */
    public function test_group_conditions_with_empty_array() {
        $conditions = array();

        $finder = new AWPCP_ListingsFinder( null );

        // Execution
        $grouped_conditions = $finder->group_conditions( $conditions );

        // Verification
        $this->assertEquals( '', $grouped_conditions );
    }
}
