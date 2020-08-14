<?php
/**
 * @package AWPCP\Tests\Plugin\Admin
 */

/**
 * Unit tests for List Table Restrictions.
 */
class AWPCP_ListTableRestrictionsTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->roles_and_capabilities = Mockery::mock( 'AWPCP_RolesAndCapabilities' );
        $this->request                = Mockery::mock( 'AWPCP_Request' );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts() {
        $current_user = (object) [
            'ID' => wp_rand(),
        ];

        $query = Mockery::mock( 'WP_Query' );

        $query->shouldReceive( 'is_main_query' )->andReturn( true );
        $query->query_vars = [];

        $this->roles_and_capabilities->shouldReceive( 'current_user_is_moderator' )->andReturn( false );

        $this->request->shouldReceive( 'get_current_user_id' )->andReturn( $current_user->ID );

        // Execution.
        $this->get_test_subject()->pre_get_posts( $query );

        // Verification.
        $this->assertEquals( $current_user->ID, $query->query_vars['author'] );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListTableRestrictions(
            'awpcp_listing',
            $this->roles_and_capabilities,
            $this->request
        );
    }
}
