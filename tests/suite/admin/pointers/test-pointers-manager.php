<?php
/**
 * @package AWPCP\Tests\Admin\Pointers
 */

use Brain\Monkey\Functions;

/**
 * Tests for the admin pointers manager.
 */
class AWPCP_PointersManagerTest extends AWPCP_UnitTestCase {

    /**
     * @var mixed
     */
    private $drip_autoresponder;

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->drip_autoresponder = Mockery::mock( 'AWPCP_DripAutoresponder' );

        Functions\when( 'awpcp_drip_autoresponder' )->justReturn( $this->drip_autoresponder );
    }

    /**
     * @since x.x
     */
    public function test_register_pointers_skips_drip_pointer_for_non_administrators() {
        $this->login_as_subscriber();

        $this->get_test_subject()->register_pointers();

        $this->assertFalse( has_filter( 'awpcp-admin-pointers', array( $this->drip_autoresponder, 'register_pointer' ) ) );
    }

    /**
     * @since x.x
     */
    public function test_register_pointers_adds_drip_pointer_for_administrators() {
        $this->login_as_administrator();

        $this->get_test_subject()->register_pointers();

        $this->assertNotFalse( has_filter( 'awpcp-admin-pointers', array( $this->drip_autoresponder, 'register_pointer' ) ) );
    }

    /**
     * @since x.x
     */
    private function get_test_subject() {
        $settings = Mockery::mock( 'AWPCP_Settings_API' );
        $settings->shouldReceive( 'get_option' )->with( 'show-drip-autoresponder' )->andReturn( true );

        $manager  = ( new ReflectionClass( 'AWPCP_PointersManager' ) )->newInstanceWithoutConstructor();
        $property = new ReflectionProperty( 'AWPCP_PointersManager', 'settings' );
        $property->setAccessible( true );
        $property->setValue( $manager, $settings );

        return $manager;
    }
}
