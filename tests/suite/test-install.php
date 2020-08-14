<?php

/**
 * @group core
 */
class AWPCP_TestInstall extends AWPCP_UnitTestCase {

    function test_plugin_initialization() {
        global $awpcp;
        $this->assertTrue(isset($awpcp) && is_object($awpcp), 'AWPCP was properly initialized.');
    }

	function test_install() {
        global $awpcp_db_version, $wpdb;

        $installed_version = get_option( 'awpcp_db_version' );

        $this->assertFalse( empty( $installed_version ), 'DB version is stored in WP options table.' );
        $this->assertEquals( $awpcp_db_version, $installed_version, 'DB version matches plugin version.' );
        $this->assertNotNull($wpdb->get_results("DESCRIBE " . AWPCP_TABLE_ADFEES), 'Ad Fees table was created.');
	}
}
