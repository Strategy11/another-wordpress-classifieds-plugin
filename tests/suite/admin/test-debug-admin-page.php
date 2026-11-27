<?php
/**
 * @package AWPCP\Tests\Admin
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for the settings included in the debug information.
 */
class AWPCP_DebugAdminPageTest extends AWPCP_UnitTestCase {

    /**
     * @dataProvider secret_settings_provider
     *
     * @since x.x
     *
     * @param string $setting_id   Setting ID.
     * @param string $setting_type Setting type.
     */
    public function test_plugin_settings_exclude_secrets( $setting_id, $setting_type ) {
        $plugin_settings = $this->get_plugin_settings(
            [
                [
                    'id'   => 'recaptcha-public-key',
                    'type' => 'textfield',
                ],
                [
                    'id'   => $setting_id,
                    'type' => $setting_type,
                ],
            ]
        );

        $this->assertSame( 'value of recaptcha-public-key', $plugin_settings['recaptcha-public-key'] );
        $this->assertArrayNotHasKey( $setting_id, $plugin_settings );
    }

    /**
     * @since x.x
     */
    public function secret_settings_provider() {
        return [
            [ '2checkout_secret_word', 'password' ],
            [ 'recaptcha-private-key', 'textfield' ],
            [ 'facebook-app-secret', 'textfield' ],
            [ 'facebook-user-access-token', 'textfield' ],
            [ 'facebook-page-access-token', 'textfield' ],
            [ 'zapier-webhook-for-facebook-page-integration', 'textfield' ],
            [ 'ifttt-webhook-base-url-for-facebook-page-integration', 'textfield' ],
            [ 'addon-api-secret', 'password' ],
        ];
    }

    /**
     * @since x.x
     *
     * @param array $registered_settings Settings returned by the settings manager.
     *
     * @return array
     */
    private function get_plugin_settings( $registered_settings ) {
        $settings         = Mockery::mock( 'AWPCP_Settings_API' );
        $settings_manager = Mockery::mock( 'AWPCP_SettingsManager' );

        $settings_manager->shouldReceive( 'get_settings' )->andReturn( $registered_settings );
        $settings->shouldReceive( 'get_option' )->andReturnUsing(
            function ( $setting_id ) {
                return 'value of ' . $setting_id;
            }
        );

        Functions\when( 'get_option' )->justReturn( '' );
        Functions\when( 'home_url' )->justReturn( 'https://example.org' );
        Functions\when( 'site_url' )->justReturn( 'https://example.org' );
        Functions\when( 'wp_parse_url' )->alias( 'parse_url' );

        $reflection = new ReflectionClass( 'AWPCP_DebugAdminPage' );
        $page       = $reflection->newInstanceWithoutConstructor();

        foreach ( [ 'settings' => $settings, 'settings_manager' => $settings_manager ] as $name => $value ) {
            $property = $reflection->getProperty( $name );
            $property->setAccessible( true );
            $property->setValue( $page, $value );
        }

        $method = $reflection->getMethod( 'get_plugin_settings' );
        $method->setAccessible( true );

        return $method->invoke( $page );
    }
}
