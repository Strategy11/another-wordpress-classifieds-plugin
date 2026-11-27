<?php
/**
 * @package AWPCP\Tests\Admin\Fees
 */

use Brain\Monkey\Functions;

/**
 * Tests for the Fees admin page.
 */
class AWPCP_AdminFeesTest extends AWPCP_UnitTestCase {

    /**
     * @var array
     */
    private $deleted_fees = array();

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->deleted_fees = array();

        Functions\when( 'awpcp_flash' )->justReturn( null );
        Functions\when( 'awpcp_get_var' )->alias(
            function ( $args ) {
                $values = array(
                    'id'       => 7,
                    '_wpnonce' => 'nonce',
                );

                return isset( $values[ $args['param'] ] ) ? $values[ $args['param'] ] : null;
            }
        );

        $this->redefine(
            'AWPCP_Fee::find_by_id',
            function ( $id ) {
                return (object) array( 'id' => $id );
            }
        );
        $this->redefine(
            'AWPCP_Fee::delete',
            function ( $id ) {
                $this->deleted_fees[] = $id;
                return true;
            }
        );
        $this->redefine(
            'AWPCP_AdminFees::index',
            function () {
                return 'index';
            }
        );
    }

    /**
     * @since x.x
     */
    public function test_delete_refuses_requests_without_a_valid_nonce() {
        Functions\expect( 'wp_verify_nonce' )->once()->with( 'nonce', 'awpcp-delete-fee-7' )->andReturn( false );

        $this->get_test_subject()->delete();

        $this->assertSame( array(), $this->deleted_fees );
    }

    /**
     * @since x.x
     */
    public function test_delete_removes_the_fee_with_a_valid_nonce() {
        Functions\expect( 'wp_verify_nonce' )->once()->with( 'nonce', 'awpcp-delete-fee-7' )->andReturn( 1 );

        $this->get_test_subject()->delete();

        $this->assertSame( array( 7 ), $this->deleted_fees );
    }

    /**
     * @since x.x
     */
    private function get_test_subject() {
        return ( new ReflectionClass( 'AWPCP_AdminFees' ) )->newInstanceWithoutConstructor();
    }
}
