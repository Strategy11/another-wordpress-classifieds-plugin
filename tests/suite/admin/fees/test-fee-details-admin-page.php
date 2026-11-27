<?php
/**
 * @package AWPCP\Tests\Admin\Fees
 */

use Brain\Monkey\Functions;

/**
 * Tests for the Fee Details admin page.
 */
class AWPCP_Fee_Details_Admin_PageTest extends AWPCP_UnitTestCase {

    /**
     * @var mixed
     */
    private $fee;

    /**
     * @var mixed
     */
    private $fee_details_form;

    /**
     * @var mixed
     */
    private $fees;

    /**
     * @var mixed
     */
    private $html_renderer;

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->fee              = Mockery::mock( 'AWPCP_Fee' );
        $this->fee_details_form = Mockery::mock( 'AWPCP_Fee_Details_Form' );
        $this->fees             = Mockery::mock( 'AWPCP_Fees_Collection' );
        $this->html_renderer    = Mockery::mock( 'AWPCP_HTML_Renderer' );

        $this->fees->shouldReceive( 'get' )->with( 7 )->andReturn( $this->fee );
        $this->fee_details_form->shouldReceive( 'build' )->andReturn( array() );
        $this->html_renderer->shouldReceive( 'render' )->andReturn( 'form' );

        Functions\when( 'awpcp_flash' )->justReturn( null );
        Functions\when( 'awpcp_parse_money' )->returnArg();
        Functions\when( 'awpcp_get_var' )->alias(
            function ( $args ) {
                $values = array(
                    'id'                      => 7,
                    'save'                    => 'Save',
                    'awpcp-fee-details-nonce' => 'nonce',
                );

                return isset( $values[ $args['param'] ] ) ? $values[ $args['param'] ] : null;
            }
        );
    }

    /**
     * @since x.x
     */
    public function test_saving_a_fee_without_a_valid_nonce_does_not_update_it() {
        Functions\expect( 'wp_verify_nonce' )->once()->with( 'nonce', 'awpcp-fee-details' )->andReturn( false );

        $this->fee->shouldReceive( 'update' )->never();
        $this->fee->shouldReceive( 'save' )->never();

        $this->assertSame( 'form', $this->get_test_subject()->dispatch() );
    }

    /**
     * @since x.x
     */
    public function test_saving_a_fee_with_a_valid_nonce_updates_it() {
        Functions\expect( 'wp_verify_nonce' )->once()->with( 'nonce', 'awpcp-fee-details' )->andReturn( 1 );

        $this->fee->shouldReceive( 'update' )->once();
        $this->fee->shouldReceive( 'save' )->once()->andReturn( false );

        $this->assertSame( 'form', $this->get_test_subject()->dispatch() );
    }

    /**
     * @since x.x
     */
    private function get_test_subject() {
        return new AWPCP_Fee_Details_Admin_Page( $this->fee_details_form, $this->fees, $this->html_renderer, null );
    }
}
