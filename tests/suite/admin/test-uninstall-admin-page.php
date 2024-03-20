<?php

class Test_Uninstall_Admin_Page extends AWPCP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		// Mock WordPress functions related to nonce verification and current user capabilities.
		\WP_Mock::userFunction(
			'wp_verify_nonce', [
				'times' => 1,
				'return' => true,
			]
		);
		\WP_Mock::userFunction(
			'wp_create_nonce', [
				'times' => 1,
				'return' => '2weer2445',
			]
		);
		\WP_Mock::userFunction(
			'current_user_can', [
				'times' => 1,
				'return' => true,
			]
		);
		\WP_Mock::userFunction(
			'is_ssl',
			[
				'times' => 1,
				'return' => true,
			]
		);

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php';
	}

	public function test_uninstall_admin_page_dispatch_with_valid_nonce_and_authorization() {
		$uninstaller = Mockery::mock( 'AWPCP_Uninstaller' );
		$settings    = $this->get_settings_class();

		$uninstaller->shouldReceive( 'uninstall' )->once();

		$this->expectAddQueryArg();
		$page = new AWPCP_UninstallAdminPage( $uninstaller, $settings );

		// Simulate a valid request with correct nonce and authorization.
		$_REQUEST['nonce'] = 'valid_nonce';
		$this->assertContains( 'Almost done...', $page->dispatch() );
	}

	public function test_uninstall_admin_page_dispatch_with_invalid_nonce() {
		\WP_Mock::userFunction(
			'wp_verify_nonce',
			[
				'times' => 1,
				'return' => false, // Simulate nonce verification failure.
			]
		);

		$this->expectException(\Exception::class); // Expect an exception due to invalid nonce.

		$uninstaller = Mockery::mock( 'AWPCP_Uninstaller' );
		$settings    = $this->get_settings_class();

		$this->expectAddQueryArg();
		$page = new AWPCP_UninstallAdminPage( $uninstaller, $settings );
		$page->dispatch();
	}

	private function get_settings_class() {
		$settings = Mockery::mock( 'AWPCP_Settings' );

		// 'Uploads' is not the right value, but it will do the job for this test.
		$settings->shouldReceive( 'get_runtime_option' )->with( 'awpcp-uploads-dir' )->andReturn( 'uploads' );
		return $settings;
	}

	public function test_uninstall_admin_page_dispatch_without_authorization() {
		\WP_Mock::userFunction(
			'current_user_can',
			[
				'times' => 1,
				'return' => false, // Simulate lack of authorization.
			]
		);

		$this->expectException(\Exception::class); // Expect an exception due to lack of authorization.

		$uninstaller = Mockery::mock( 'AWPCP_Uninstaller' );
		$settings    = $this->get_settings_class();
		$page = new AWPCP_UninstallAdminPage( $uninstaller, $settings );

		$page->dispatch();
	}

	public function test_uninstall_admin_page_dispatch() {
		$uninstaller = Mockery::mock( 'AWPCP_Uninstaller' );
		$settings    = $this->get_settings_class();

		$uninstaller->shouldReceive( 'uninstall' )->once();

		$page = new AWPCP_UninstallAdminPage( $uninstaller, $settings );

		$this->assertContains( 'action=uninstall', $page->dispatch() );
	}
}
