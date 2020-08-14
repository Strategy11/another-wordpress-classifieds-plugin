<?php
/**
 * @package AWPCP\Tests\Plugin\Admin
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for List Table Actions Handler class.
 */
class AWPCP_ListTableActionsHandlerTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function test_admin_head() {
        $result_codes = array(
            'success' => 1,
        );
        $message      = 'admin notice';
        $return_uri   = 'https://example.org';

        $action_handler = Mockery::mock( 'AWPCP_ListTableAction' );
        $request        = Mockery::mock( 'AWPCP_Request' );

        $action_handler->shouldReceive( 'get_messages' )
            ->once()
            ->with( $result_codes )
            ->andReturn( array( $message ) );

        $request->shouldReceive( 'param' )
            ->with( 'awpcp-action' )
            ->andReturn( 'custom-action' );

        $request->shouldReceive( 'param' )
            ->with( 'awpcp-result' )
            ->andReturn( 'success~1' );

        Functions\expect( 'remove_query_arg' )
            ->once()
            ->with( Mockery::any(), $return_uri );

        Functions\expect( 'awpcp_current_user_is_moderator' )
            ->once()
            ->andReturn( true );

        $actions = array(
            'custom-action' => $action_handler,
        );

        $_SERVER['REQUEST_URI'] = $return_uri;

        $actions_handler = new AWPCP_ListTableActionsHandler( $actions, null, $request );

        // Execution.
        ob_start();
        $actions_handler->admin_head();
        $output = ob_get_contents();
        ob_end_clean();

        // Verification.
        $this->assertContains( $message, $output );
    }

    /**
     * @since 4.0.0
     */
    public function test_row_actions_links_returns_defined_actions() {
        $post = (object) array();

        $action_handler = Mockery::mock( 'AWPCP_ListTableAction' );

        $action_handler->shouldReceive( 'should_show_action_for' )
            ->once()
            ->with( $post )
            ->andReturn( true );

        $action_handler->shouldReceive( 'get_label' )
            ->once()
            ->with( $post )
            ->andReturn( 'Label' );

        $action_handler->shouldReceive( 'get_url' )
            ->once()
            ->with( $post, Mockery::any() )
            ->andReturn( 'URL' );

        $actions = array(
            'custom-action' => $action_handler,
        );

        $table_actions = new AWPCP_ListTableActionsHandler( $actions, null, null );

        $actions = $table_actions->row_actions_links( array(), $post );

        // Verification.
        $this->assertContains( 'custom-action', array_keys( $actions ) );
        $this->assertContains( 'Label', $actions['custom-action'] );
        $this->assertContains( 'URL', $actions['custom-action'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_handle_action() {
        $post = (object) array(
            'ID' => wp_rand() + 1,
        );

        $action_handler      = Mockery::mock( 'AWPCP_ListTableAction' );
        $listings_collection = Mockery::mock( 'AWPCP_ListingsCollection' );
        $request             = Mockery::mock( 'AWPCP_Request' );

        $action_handler->shouldReceive( 'process_item' )
            ->once()
            ->with( $post )
            ->andReturn( 'success' );

        $listings_collection->shouldReceive( 'get' )
            ->once()
            ->with( $post->ID )
            ->andReturn( $post );

        $request->shouldReceive( 'param' )
            ->with( 'redirect_to' )
            ->andReturn( '' );

        $query_params = null;
        $actions      = array(
            'custom-action' => $action_handler,
        );

        Functions\when( 'add_query_arg' )->alias(
            function( $params, $url ) use ( &$query_params ) {
                $query_params = $params;
                return $url;
            }
        );

        $actions_handler = new AWPCP_ListTableActionsHandler(
            $actions,
            $listings_collection,
            $request
        );

        // Execution.
        $actions_handler->handle_action( '', 'custom-action', array( $post->ID ) );

        // Verification.
        $this->assertArrayHasKey( 'awpcp-action', $query_params );
        $this->assertArrayHasKey( 'awpcp-result', $query_params );
        $this->assertContains( 'success~1', $query_params['awpcp-result'] );
    }
}
