<?php

/**
 * @group core
 * @group broken    broken because of memoization in awppc_get_pages_ids
 */
class AWPCP_TestSecureURLRedirectionHandler extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        global $wp_filter;
        $this->wp_redirect_filters = awpcp_array_data( 'wp_redirect', array(), $wp_filter );

        // make WP think current request is not using SSL
        unset( $_SERVER['HTTPS'] );
        unset( $_SERVER['SERVER_PORT'] );

        awpcp()->settings->set_or_update_option( 'force-secure-urls', true );
    }

    public function teardown() {
        parent::teardown();

        global $wp_filter;
        $wp_filter['wp_redirect'] = $this->wp_redirect_filters;
    }

    public function test_url_redirection() {
        $filter = new MockAction;
        add_filter( 'wp_redirect', array( $filter, 'action' ) );
        add_filter( 'wp_redirect', '__return_false' );

        $query = Phake::mock( 'AWPCP_Query' );

        Phake::when( $query )->is_page_that_accepts_payments->thenReturn( true );

        $redirection_handler = new AWPCP_SecureURLRedirectionHandler( $query );
        $redirection_handler->dispatch();

        remove_filter( 'wp_redirect', array( $filter, 'action' ) );
        remove_filter( 'wp_redirect', '__return_false' );

        $this->assertEquals( 1, $filter->get_call_count( 'action' ) );
    }
}
