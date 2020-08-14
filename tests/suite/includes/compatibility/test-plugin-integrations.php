<?php

class AWPCP_Test_Plugin_Integrations extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        delete_option( 'awpcp_plugin_integrations' );
    }

    public function test_maybe_enable_plugin_integration() {
        $plugin_integrations = new AWPCP_Plugin_Integrations();
        $plugin_integrations->add_plugin_integration( 'plugin-name/plugin-name.php', 'awpcp_plugin_integrations' );
        $plugin_integrations->maybe_enable_plugin_integration( 'plugin-name/plugin-name.php', null );

        $enabled_integrations = get_option( 'awpcp_plugin_integrations' );

        $this->assertContains( 'plugin-name/plugin-name.php', $enabled_integrations );
    }

    public function test_load_plugin_integrations() {
        $mock = Phake::mock( 'AWPCP_Plugin_Integration' );

        $plugin_integrations = new AWPCP_Plugin_Integrations();
        $plugin_integrations->add_plugin_integration( 'plugin-name/plugin-name.php', function() use ( $mock ) {
            return $mock;
        } );
        $plugin_integrations->maybe_enable_plugin_integration( 'plugin-name/plugin-name.php', null );

        $plugin_integrations->load_plugin_integrations();

        Phake::verify( $mock, Phake::times( 1 ) )->load();
    }

    public function test_discover_supported_plugin_integrations() {
        update_option( 'awpcp_plugin_integrations', array( 'inactive-plugin/inactive-plugin.php' ) );
        update_option( 'active_plugins', array( 'plugin-name/plugin-name.php', 'foo/bar.php' ) );
        update_option( 'active_sitewide_plugins', array( 'sitewide-plugin-name/sitewide-plugin-name.php' ) );

        $plugin_integrations = new AWPCP_Plugin_Integrations();
        $plugin_integrations->add_plugin_integration( 'plugin-name/plugin-name.php', function() {} );
        $plugin_integrations->add_plugin_integration( 'sitewide-plugin-name/sitewide-plugin-name.php', function() {} );

        $plugin_integrations->discover_supported_plugin_integrations();

        $enabled_integrations = $plugin_integrations->get_enabled_plugin_integrations();

        $this->assertContains( 'plugin-name/plugin-name.php', $enabled_integrations );
        $this->assertContains( 'sitewide-plugin-name/sitewide-plugin-name.php', $enabled_integrations );
        $this->assertNotContains( 'inactive-plugin/inactive-plugin.php', $enabled_integrations );
    }

    public function test_maybe_disable_plugin_integration() {
        update_option( 'awpcp_plugin_integrations', array( 'plugin-name/plugin-name.php' ) );

        $plugin_integrations = new AWPCP_Plugin_Integrations();
        $plugin_integrations->maybe_disable_plugin_integration( 'plugin-name/plugin-name.php', null );

        $enabled_integrations = get_option( 'awpcp_plugin_integrations' );

        $this->assertNotContains( 'plugin-name/plugin-name.php', $enabled_integrations );
    }
}
