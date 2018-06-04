<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Ajax handler for the action that creates a listing for the selected categories
 * and payment term.
 */
class AWPCP_CreateEmptyListingAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @var string
     */
    private $listing_category_taxonomy;

    /**
     * @var AWPCP_Listings_API
     */
    private $listings_logic;

    /**
     * @var AWPCP_Request
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $listing_category_taxonomy, $listings_logic, $response, $request ) {
        parent::__construct( $response );

        $this->listing_category_taxonomy = $listing_category_taxonomy;
        $this->listings_logic            = $listings_logic;
        $this->request                   = $request;
    }

    /**
     * @since 4.0.0
     */
    public function ajax() {
        $categories        = array_map( 'intval', $this->request->post( 'categories' ) );
        $payment_term_id   = $this->request->post( 'payment_term_id' );
        $payment_term_type = $this->request->post( 'payment_term_type' );

        // TODO: Handle 500 errors on frontend.
        // TODO: Add nonce verificiation.
        // TODO: Add validation to create_listing.
        // TODO: Handle exceptions and return errors.
        // TODO: Show errors on the frontend.
        $listing = $this->listings_logic->create_listing( [
            'post_fields' => [
                'post_title'  => __( 'Classified Auto Draft', 'another-wordpress-classifieds-plugin' ),
                'post_status' => 'auto-draft',
                'post_author' => $this->request->get_current_user_id(),
            ],
            'metadata'    => [
                '_awpcp_payment_term_id'   => $payment_term_id,
                '_awpcp_payment_term_type' => $payment_term_type,
            ],
            // TODO: Update create_listing to store terms as well.
            'terms'       => [
                $this->listing_category_taxonomy => $categories,
            ],
        ] );

        $response = [
            'listing' => [
                'ID' => $listing->ID,
            ],
        ];

        return $this->success( $response );
    }
}
