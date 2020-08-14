<?php
/**
 * @package AWPCP\Tests\Admin\Listings
 */

/**
 * Test helper for Listing Table View.
 */
class AWPCP_ListingTableViewTestHelper {

    /**
     * @var object
     */
    private $test_case;

    /**
     * @param object $test_case     An instance of AWPCP_UnitTestCase.
     * @since 4.0.0
     */
    public function __construct( $test_case ) {
        $this->test_case = $test_case;
    }

    /**
     * @param object $view  View under test.
     * @since 4.0.0
     */
    public function check_common_table_view_methods( $view ) {
        $this->check_get_label( $view );
        $this->check_get_url( $view );
    }

    /**
     * @param object $view  View under test.
     * @since 4.0.0
     */
    public function check_get_label( $view ) {
        // Execution.
        $label = $view->get_label();

        // Verification.
        $this->test_case->assertNotEmpty( $label );
    }

    /**
     * @param object $view  View under test.
     * @since 4.0.0
     */
    public function check_get_url( $view ) {
        $current_url = 'https://example.org';

        // Execution.
        $url = $view->get_url( $current_url );

        // Verification.
        $this->test_case->assertNotEmpty( $url );
    }
}
