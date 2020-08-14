<?php
/**
 * @package AWPCP\Tests\Frontend
 */

/**
 * Unit tests for Order Submit Listing Section class.
 */
class AWPCP_OrderSubmitListingSectionTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->payments          = null;
        $this->listings_logic    = Mockery::mock( 'AWPCP_ListingsAPI' );
        $this->listing_renderer  = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->roles             = null;
        $this->captcha           = null;
        $this->template_renderer = null;
        $this->settings          = null;
    }

    /**
     * The section should render in preview state if the payment information
     * can still be modified, but a payment term was already selected.
     *
     * @since 4.0.0
     */
    public function test_get_state_returns_preview() {
        $listing = (object) [];

        $this->listings_logic->shouldReceive( 'can_payment_information_be_modified_during_submit' )
            ->andReturn( true );

        $this->listing_renderer->shouldReceive( 'get_payment_term' )
            ->with( $listing )
            ->andReturn( (object) [] );

        $section = $this->get_test_subject();

        // Execution.
        $state = $section->get_state( $listing );

        // Verification.
        $this->assertEquals( 'preview', $state );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_OrderSubmitListingSection(
            $this->payments,
            $this->listings_logic,
            $this->listing_renderer,
            $this->roles,
            $this->captcha,
            $this->template_renderer,
            $this->settings
        );
    }
}
