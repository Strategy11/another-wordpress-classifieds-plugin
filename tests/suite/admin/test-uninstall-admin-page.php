<?php

class Test_Uninstall_Admin_Page extends AWPCP_UnitTestCase {

	public function test_admin_page_is_registered() {
		$admin_pages = awpcp_admin_pages();

		$this->assertArrayHasKey( 'uninstall', $admin_pages );
	}

	public function test_uninstall_admin_page_dispatch() {
		$uninstaller = Mockery::mock( 'AWPCP_Uninstaller' );
		$settings    = Mockery::mock( 'AWPCP_Settings' );

		$uninstaller->shouldReceive( 'uninstall' )->once();

		$page = new AWPCP_UninstallAdminPage( $uninstaller, $settings );

		$this->assertContains( 'action=uninstall', $page->dispatch() );
	}

	public function test_uninstall_admin_page_dispatch_uninstall() {
		$uninstaller = Mockery::mock( 'AWPCP_Uninstaller' );
		$settings    = Mockery::mock( 'AWPCP_Settings' );

		$uninstaller->shouldReceive( 'uninstall' )->once();

		$_GET['_wpnonce'] = wp_create_nonce( 'uninstall' );
		$_GET['action']   = 'uninstall';

		$page = new AWPCP_UninstallAdminPage( $uninstaller, $settings );

		$this->assertEmpty( $page->dispatch() );
	}
}
