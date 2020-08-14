<?php
/**
 * @package AWPCP\Tests\Plugin\Admin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Unit testss for Listing Information Metabox class.
 */
class AWPCP_ListingInformationMetaboxTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->listings_logic    = Mockery::mock( 'AWPCP_ListingsAPI' );
        $this->listing_renderer  = Mockery::mock( 'AWPCP_ListingRenderer' );
        $this->payments          = Mockery::mock( 'AWPCP_Payments' );
        $this->template_renderer = Mockery::mock( 'AWPCP_TemplateRenderer' );
        $this->request           = Mockery::mock( 'AWPCP_Request' );
    }

    /**
     * @since 4.0.0
     */
    public function test_render_when_the_listing_has_no_payment_term_associated() {
        $post = (object) [
            'post_author' => wp_rand() + 1,
        ];

        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( true );

        $this->listing_renderer->shouldReceive(
            [
                'get_renewed_date_formatted' => '',
                'get_end_date_formatted'     => '',
                'get_access_key'             => '',
                'get_payment_term'           => null,
            ]
        );

        $this->payments->shouldReceive(
            [
                'get_user_payment_terms' => [],
            ]
        );

        $this->template_renderer->shouldReceive( 'render_template' );

        $metabox = $this->get_test_subject();

        $metabox->render( $post );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListingInfromationMetabox(
            $this->listings_logic,
            $this->listing_renderer,
            $this->payments,
            $this->template_renderer,
            $this->request
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_save_when_the_listing_has_no_previous_payment_term() {
        $post = (object) [
            'ID'          => wp_rand() + 1,
            'post_author' => wp_rand() + 1,
            'post_status' => 'draft',
        ];

        $payment_term = (object) [
            'id'   => wp_rand() + 1,
            'type' => 'fee',
        ];

        $transaction = Mockery::mock( 'AWPCP_Payment_Transaction' );

        $transaction->shouldReceive( 'set' );

        Functions\when( 'awpcp_current_user_is_moderator' )->justReturn( true );

        $this->request->shouldReceive( 'post' )
            ->with( 'payment_term' )
            ->andReturn( 'fee-1' );

        $this->payments->shouldReceive(
            [
                'get_payment_term'                       => $payment_term,
                'create_transaction'                     => $transaction,
                'set_transaction_item_from_payment_term' => null,
                'set_transaction_status_to_completed'    => null,
            ]
        );

        $this->listing_renderer->shouldReceive(
            [
                'get_payment_term'   => null,
                'get_categories_ids' => [ 1 ],
            ]
        );

        $this->listings_logic->shouldReceive( 'update_listing_payment_term' );

        $metabox = $this->get_test_subject();

        $metabox->save( $post->ID, $post );
    }
}
