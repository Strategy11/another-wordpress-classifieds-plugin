<?php
/**
 * @package AWPCP\Tests\Plugin\Admin
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for List Table Actions Handler class.
 */
class AWPCP_ListTableActionsHandlerTest extends AWPCP_UnitTestCase {

    public function tearDown(): void {
        parent::tearDown();

       unset($_REQUEST['awpcp-action']);
       unset($_REQUEST['awpcp-result']);
       unset($_REQUEST['REQUEST_URI']);
    }
    /**
     * @since 4.0.0
     */
    public function test_admin_head() {
        $result_codes = array(
            'success' => 1,
        );
        $message      = 'admin notice';
        $return_uri   = 'https://example.org';

        $action_handler = Mockery::mock( 'AWPCP_ListTableActionsHandler' );

        $action_handler->shouldReceive( 'get_messages' )
            ->once()
            ->with( $result_codes )
            ->andReturn( array( $message ) );

        $_REQUEST['awpcp-action'] = 'custom-action';
        $_REQUEST['awpcp-result'] = 'success~1';

        Functions\expect( 'remove_query_arg' )
            ->zeroOrMoreTimes()
            ->with( Mockery::any(), $return_uri );

        Functions\expect( 'awpcp_current_user_is_moderator' )
            ->once()
            ->andReturn( true );

        $actions = array(
            'custom-action' => $action_handler,
        );

        $_SERVER['REQUEST_URI'] = $return_uri;

        $actions_handler = new AWPCP_ListTableActionsHandler( $actions, null );

        // Execution.
        ob_start();
        $actions_handler->admin_head();
        $output = ob_get_contents();
        ob_end_clean();

        // Verification.
        $this->assertStringContainsString( $message, $output );
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
        $current_url = 'https://example.org';

        Functions\when( 'wp_doing_ajax' )->justReturn( false );
        Functions\expect( 'add_query_arg' )
            ->once()
            ->with( [] )
            ->andReturn( $current_url );
        Functions\when( 'wp_nonce_url' )
            ->justReturn( '');
        $table_actions = new AWPCP_ListTableActionsHandler( $actions, null );

        $actions = $table_actions->row_actions_links( array(), $post );
        // Verification.
        $this->assertContains( 'custom-action', array_keys( $actions ) );
        $this->assertStringContainsString( 'Label', $actions['custom-action'] );
    }

    /**
     * During AJAX requests, like Quick Edit, action links are built from the referer.
     */
    public function test_row_actions_links_uses_referer_during_ajax() {
        $referer     = 'https://example.org/wp-admin/edit.php?post_type=awpcp_listing&paged=2&awpcp-action=disable&awpcp-result=1';
        $current_url = 'https://example.org/wp-admin/edit.php?post_type=awpcp_listing&paged=2';

        Functions\when( 'wp_doing_ajax' )->justReturn( true );
        Functions\when( 'wp_get_referer' )->justReturn( $referer );
        Functions\expect( 'remove_query_arg' )
            ->once()
            ->with( array( 'awpcp-action', 'awpcp-result' ), $referer )
            ->andReturn( $current_url );

        $this->assert_row_action_built_from( $current_url );
    }

    /**
     * Without a referer, action links fall back to the listings table URL.
     */
    public function test_row_actions_links_falls_back_to_listings_table_during_ajax() {
        if ( ! defined( 'AWPCP_LISTING_POST_TYPE' ) ) {
            define( 'AWPCP_LISTING_POST_TYPE', 'awpcp_listing' );
        }

        $current_url = 'https://example.org/wp-admin/edit.php?post_type=' . AWPCP_LISTING_POST_TYPE;

        Functions\when( 'wp_doing_ajax' )->justReturn( true );
        Functions\when( 'wp_get_referer' )->justReturn( false );
        Functions\expect( 'admin_url' )
            ->once()
            ->with( 'edit.php?post_type=' . AWPCP_LISTING_POST_TYPE )
            ->andReturn( $current_url );

        $this->assert_row_action_built_from( $current_url );
    }

    /**
     * @param string $current_url The URL the action link should be built from.
     */
    private function assert_row_action_built_from( $current_url ) {
        $post           = (object) array();
        $action_handler = Mockery::mock( 'AWPCP_ListTableAction' );

        $action_handler->shouldReceive( 'should_show_action_for' )->once()->with( $post )->andReturn( true );
        $action_handler->shouldReceive( 'get_label' )->once()->with( $post )->andReturn( 'Label' );
        $action_handler->shouldReceive( 'get_url' )->once()->with( $post, $current_url )->andReturn( 'URL' );

        Functions\expect( 'add_query_arg' )->never();
        Functions\when( 'wp_nonce_url' )->justReturn( '' );

        $table_actions = new AWPCP_ListTableActionsHandler( array( 'custom-action' => $action_handler ), null );
        $actions       = $table_actions->row_actions_links( array(), $post );

        $this->assertArrayHasKey( 'custom-action', $actions );
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
            $listings_collection
        );

        // Execution.
        $actions_handler->handle_action( '', 'custom-action', array( $post->ID ) );

        // Verification.
        $this->assertArrayHasKey( 'awpcp-action', $query_params );
        $this->assertArrayHasKey( 'awpcp-result', $query_params );
        $this->assertStringContainsString( 'success~1', $query_params['awpcp-result'] );
    }
}
