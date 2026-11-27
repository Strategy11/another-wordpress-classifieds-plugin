<?php
/**
 * @package AWPCP\Tests
 */

use Brain\Monkey\Functions;

/**
 * Which listings the renew page lets the owner renew.
 */
class AWPCP_RenewListingPageEligibilityTest extends AWPCP_UnitTestCase {

    const NO_RENEW = 'That Ad doesn\'t need to be renewed.';

    const INVALID_URL = 'The URL is not valid';

    const MODERATED = 'disabled or waiting for approval';

    /**
     * @var \Mockery\MockInterface
     */
    public $listing_renderer;

    /**
     * @var \Mockery\MockInterface
     */
    public $listings;

    /**
     * @var \Mockery\MockInterface
     */
    public $payments;

    /**
     * @var object
     */
    public $listing;

    /**
     * @var array
     */
    public $params = array();

    /**
     * @var bool
     */
    public $is_moderator = false;

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->listing          = (object) array( 'ID' => 7 );
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->listings         = Mockery::mock( 'AWPCP_ListingsCollection' );
        $this->payments         = Mockery::mock( 'AWPCP_PaymentsAPI' );

        $this->listings->shouldReceive( 'get' )->andReturn( $this->listing );
        $this->listing_renderer->shouldReceive( 'get_payment_term' )->andReturn( null );

        $params = &$this->params;

        Functions\when( 'awpcp_get_var' )->alias(
            function ( $args ) use ( &$params ) {
                $default = isset( $args['default'] ) ? $args['default'] : '';

                return isset( $params[ $args['param'] ] ) ? $params[ $args['param'] ] : $default;
            }
        );
        Functions\when( 'awpcp_verify_renew_ad_hash' )->alias(
            function () use ( &$params ) {
                return isset( $params['awpcprah'] ) && 'valid' === $params['awpcprah'];
            }
        );

        $is_moderator = &$this->is_moderator;

        Functions\when( 'awpcp_current_user_is_moderator' )->alias(
            function () use ( &$is_moderator ) {
                return $is_moderator;
            }
        );
        Functions\when( 'awpcp_print_error' )->returnArg();
        Functions\when( 'is_admin' )->justReturn( false );
        Functions\when( 'url_showad' )->justReturn( 'https://example.org/listing' );
    }

    /**
     * Any non-empty step used to skip the eligibility check.
     *
     * @since x.x
     *
     * @dataProvider steps
     *
     * @param string $step The requested step.
     */
    public function test_step_alone_does_not_skip_eligibility_check( $step ) {
        $this->given_listing( 'running' );
        $this->given_transaction( null );

        $this->params = array(
            'step'     => $step,
            'awpcprah' => 'valid',
        );

        $this->assertStringContainsString( self::NO_RENEW, $this->dispatch() );
    }

    /**
     * @since x.x
     */
    public function steps() {
        return array(
            array( 'order' ),
            array( 'checkout' ),
            array( 'payment-completed' ),
            array( 'finish' ),
        );
    }

    /**
     * @since x.x
     */
    public function test_paid_transaction_for_another_listing_does_not_skip_eligibility_check() {
        $this->given_listing( 'running' );
        $this->given_transaction( $this->mock_transaction( 8, 'completed' ) );

        $this->params = array(
            'step'     => 'finish',
            'awpcprah' => 'valid',
        );

        $this->assertStringContainsString( self::NO_RENEW, $this->dispatch() );
    }

    /**
     * @since x.x
     */
    public function test_unpaid_transaction_for_this_listing_does_not_skip_eligibility_check() {
        $this->given_listing( 'running' );
        $this->given_transaction( $this->mock_transaction( 7, 'checkout' ) );

        $this->params = array(
            'step'     => 'finish',
            'awpcprah' => 'valid',
        );

        $this->assertStringContainsString( self::NO_RENEW, $this->dispatch() );
    }

    /**
     * Returning from checkout after the listing was renewed, with or without step.
     *
     * @since x.x
     *
     * @dataProvider paid_states
     *
     * @param string $state Transaction state.
     */
    public function test_paid_transaction_for_this_listing_skips_eligibility_check( $state ) {
        $this->given_listing( 'running' );
        $this->given_transaction( $this->mock_transaction( 7, $state ) );

        // A wrong hash stops dispatch right after the eligibility check.
        $this->params = array( 'awpcprah' => 'wrong' );

        $content = $this->dispatch();

        $this->assertStringNotContainsString( self::NO_RENEW, $content );
        $this->assertStringContainsString( self::INVALID_URL, $content );
    }

    /**
     * @since x.x
     */
    public function paid_states() {
        return array(
            array( 'payment-completed' ),
            array( 'completed' ),
        );
    }

    /**
     * @since x.x
     *
     * @dataProvider moderated_listings
     *
     * @param string $state Listing state.
     */
    public function test_owner_cannot_renew_moderated_listing( $state ) {
        $this->given_listing( $state );
        $this->given_transaction( null );

        $this->params = array( 'awpcprah' => 'valid' );

        $this->assertStringContainsString( self::MODERATED, $this->dispatch() );
    }

    /**
     * @since x.x
     *
     * @dataProvider moderated_listings
     *
     * @param string $state Listing state.
     */
    public function test_moderator_can_renew_moderated_listing( $state ) {
        $this->is_moderator = true;

        $this->given_listing( $state );
        $this->given_transaction( null );

        $this->params = array( 'awpcprah' => 'valid' );

        $this->assertStringNotContainsString( self::MODERATED, $this->dispatch() );
    }

    /**
     * @since x.x
     */
    public function moderated_listings() {
        return array(
            'disabled past end date'  => array( 'disabled' ),
            'pending about to expire' => array( 'pending' ),
        );
    }

    /**
     * @since x.x
     */
    public function test_owner_can_renew_expired_listing() {
        $this->given_listing( 'expired' );
        $this->given_transaction( null );

        $this->params = array( 'awpcprah' => 'valid' );

        $content = $this->dispatch();

        $this->assertStringNotContainsString( self::NO_RENEW, $content );
        $this->assertStringNotContainsString( self::MODERATED, $content );
    }

    /**
     * @since x.x
     *
     * @param string $state One of running, expired, disabled or pending.
     */
    private function given_listing( $state ) {
        $this->listing_renderer->shouldReceive( 'is_about_to_expire' )->andReturn( 'pending' === $state );
        $this->listing_renderer->shouldReceive( 'has_expired' )->andReturn( in_array( $state, array( 'expired', 'disabled' ), true ) );
        $this->listing_renderer->shouldReceive( 'is_disabled' )->andReturn( 'disabled' === $state );
        $this->listing_renderer->shouldReceive( 'is_pending_approval' )->andReturn( 'pending' === $state );
    }

    /**
     * @since x.x
     *
     * @param object|null $transaction The transaction in the request.
     */
    private function given_transaction( $transaction ) {
        $this->payments->shouldReceive( 'get_transaction' )->andReturn( $transaction );
    }

    /**
     * @since x.x
     *
     * @param int    $listing_id ID of the listing the transaction renews.
     * @param string $state      One of checkout, payment-completed or completed.
     */
    private function mock_transaction( $listing_id, $state ) {
        $transaction = Mockery::mock( 'AWPCP_Payment_Transaction' );

        $transaction->shouldReceive( 'is_new' )->andReturn( false );
        $transaction->shouldReceive( 'get' )->with( 'context' )->andReturn( 'renew-ad' );
        $transaction->shouldReceive( 'get' )->with( 'ad-id' )->andReturn( (string) $listing_id );
        $transaction->shouldReceive( 'is_payment_completed' )->andReturn( 'payment-completed' === $state );
        $transaction->shouldReceive( 'is_completed' )->andReturn( 'completed' === $state );

        return $transaction;
    }

    /**
     * @since x.x
     */
    private function dispatch() {
        $template_renderer = Mockery::mock( 'AWPCP_Template_Renderer' );
        $template_renderer->shouldReceive( 'render_page_template' )->andReturnUsing(
            function ( $page, $page_template, $content_template, $params ) {
                return 'content' === $content_template ? $params : $content_template;
            }
        );

        $page = new AWPCP_RenewAdPage(
            'awpcp-renew-ad',
            null,
            null,
            null,
            null,
            $this->listing_renderer,
            null,
            $this->listings,
            $this->payments,
            $template_renderer,
            null
        );

        $method = new ReflectionMethod( $page, '_dispatch' );
        $method->setAccessible( true );

        return (string) $method->invoke( $page );
    }
}
