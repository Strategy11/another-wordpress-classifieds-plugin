<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Ajax handler for Clear Listing Information action.
 */
class AWPCP_ClearListingInformationAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @var ListingsCollection
     */
    private $listings;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ListingsLogic
     */
    private $listings_logic;

    /**
     * @since 4.0.0
     */
    public function __construct( $listings_logic, $listings, $response, $request ) {
        parent::__construct( $response );

        $this->listings_logic = $listings_logic;
        $this->listings       = $listings;
        $this->request        = $request;
    }

    /**
     * TODO: This shouldn't be possible for already paid listings.
     *
     * @since 4.0.0
     */
    public function ajax() {
        $listing   = $this->listings->get( $this->request->post( 'ad_id' ) );
        $post_data = $this->get_new_post_data( $listing );

        // TODO: Delete uploaded media.
        $this->listings_logic->update_listing( $listing, $post_data );

        return $this->success();
    }

    /**
     * @since 4.0.0
     */
    public function get_new_post_data( $listing ) {
        $data = [
            'post_fields' => [
                'post_title'   => __( 'Classified Auto Draft', 'another-wordpress-classifieds-plugin' ),
                'post_content' => '',
            ],
            'metadata'    => [
                '_awpcp_start_date'             => null,
                '_awpcp_end_date'               => null,
                '_awpcp_most_recent_start_date' => null,
                '_awpcp_contact_name'           => null,
                '_awpcp_contact_phone'          => null,
                '_awpcp_contact_email'          => null,
                '_awpcp_website_url'            => null,
                '_awpcp_price'                  => null,
            ],
        ];

        if ( ! $this->listings_logic->can_payment_information_be_modified_during_submit( $listing ) ) {
            return $data;
        }

        $data = array_merge_recursive( $data, [
            'terms'    => [
                $this->listing_category_taxonomy => [],
            ],
            'metadata' => [
                '_awpcp_payment_term_id'   => null,
                '_awpcp_payment_term_type' => null,
                '_awpcp_payment_status'    => null,
            ],
        ] );

        return $data;
    }
}
