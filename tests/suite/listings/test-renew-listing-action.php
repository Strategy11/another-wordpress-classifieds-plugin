<?php
/**
 * @package AWPCP\Tests
 */

use Brain\Monkey\Functions;

/**
 * When the Renew button is shown for a listing.
 */
class AWPCP_RenewListingActionTest extends AWPCP_UnitTestCase {

    /**
     * @since x.x
     *
     * @dataProvider listings
     *
     * @param string $state        One of running, expiring, expired, disabled or pending.
     * @param bool   $is_moderator Whether the current user is a moderator.
     * @param bool   $expected     Whether the button is shown.
     */
    public function test_is_enabled_for_listing( $state, $is_moderator, $expected ) {
        $listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );

        $listing_renderer->shouldReceive( 'has_expired_or_is_about_to_expire' )->andReturn( 'running' !== $state );
        $listing_renderer->shouldReceive( 'is_disabled' )->andReturn( 'disabled' === $state );
        $listing_renderer->shouldReceive( 'is_pending_approval' )->andReturn( 'pending' === $state );

        Functions\when( 'awpcp_listing_renderer' )->justReturn( $listing_renderer );
        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( $is_moderator );

        $action = new AWPCP_RenewListingAction();

        $this->assertSame( $expected, $action->is_enabled_for_listing( (object) array( 'ID' => 1 ) ) );
    }

    /**
     * @since x.x
     */
    public function listings() {
        return array(
            'running listing'                => array( 'running', false, false ),
            'listing about to expire'        => array( 'expiring', false, true ),
            'expired listing'                => array( 'expired', false, true ),
            'disabled listing, owner'        => array( 'disabled', false, false ),
            'pending listing, owner'         => array( 'pending', false, false ),
            'disabled listing, moderator'    => array( 'disabled', true, true ),
            'pending listing, moderator'     => array( 'pending', true, true ),
        );
    }
}
