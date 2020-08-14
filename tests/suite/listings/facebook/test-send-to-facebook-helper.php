<?php
/**
 * @package AWPCP\Tests\Plugin\Listings\Facebook
 */

/**
 * Test for utility class used to post listings to Facebook pages and groups.
 */
class AWPCP_SendToFacebookHelperTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->facebook         = Mockery::mock( 'AWPCP_SendToFacebookHelper' );
        $this->listing_renderer = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->settings         = Mockery::mock( 'AWPCP_Settings' );
        $this->wordpress        = Mockery::mock( 'AWPCP_WordPress' );
    }

    /**
     * @expectedException        AWPCP_NoFacebookObjectSelectedException
     * @expectedExceptionMessage There is no Facebook Group selected.
     */
    public function test_send_listing_to_facebook_group_when_no_group_is_selected() {
        $listing = (object) array(
            'ID' => wp_rand(),
        );

        $this->facebook->shouldReceive( 'set_access_token' )
            ->andReturn( null );

        $this->facebook->shouldReceive( 'is_group_set' )
            ->andReturn( false );

        $this->listing_renderer->shouldReceive( 'is_public' )
            ->andReturn( true );

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'facebook-integration-method' )
            ->andReturn( 'facebook-api' );

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->andReturn( false );

        $helper = $this->get_test_subject();

        // Execution.
        $helper->send_listing_to_facebook_group( $listing );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_SendToFacebookHelper(
            $this->facebook,
            null,
            $this->listing_renderer,
            null,
            $this->settings,
            $this->wordpress
        );
    }

    /**
     * @expectedException        AWPCP_ListingAlreadySharedException
     * @expectedExceptionMessage The ad was already sent to a Facebook Group.
     */
    public function test_send_listing_to_facebook_group_when_listing_was_already_sent() {
        $listing = (object) array(
            'ID' => wp_rand(),
        );

        $this->facebook->shouldReceive( 'set_access_token' )
            ->andReturn( null );

        $this->facebook->shouldReceive( 'is_group_set' )
            ->andReturn( true );

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $listing->ID, '_awpcp_sent_to_facebook_group', true )
            ->andReturn( true );

        $helper = $this->get_test_subject();

        // Execution.
        $helper->send_listing_to_facebook_group( $listing );
    }

    /**
     * @expectedException           AWPCP_ListingDisabledException
     * @expectedExceptionMessage    The ad is currently disabled. If you share it, Facebook servers and users won't be able to access it.
     */
    public function test_send_listing_to_facebook_group_when_listing_is_not_public() {
        $listing = (object) array(
            'ID' => wp_rand(),
        );

        $this->facebook->shouldReceive( 'set_access_token' )
            ->andReturn( null );

        $this->facebook->shouldReceive( 'is_group_set' )
            ->andReturn( true );

        $this->wordpress->shouldReceive( 'get_post_meta' )
            ->with( $listing->ID, '_awpcp_sent_to_facebook_group', true )
            ->andReturn( false );

        $this->listing_renderer->shouldReceive( 'is_public' )
            ->with( $listing )
            ->andReturn( false );

        $helper = $this->get_test_subject();

        // Execution.
        $helper->send_listing_to_facebook_group( $listing );
    }
}
