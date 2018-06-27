<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Ajax handler for Clear Listing Information action.
 */
class AWPCP_ClearListingInformationAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @var ListingsLogic
     */
    private $listings_logic;

    /**
     * @var ListingsCollection
     */
    private $listings;

    /**
     * @var RolesAndCapabilities
     */
    private $roles;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Request
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $listings_logic, $listings, $roles, $settings, $response, $request ) {
        parent::__construct( $response );

        $this->listings_logic = $listings_logic;
        $this->listings       = $listings;
        $this->roles          = $roles;
        $this->settings       = $settings;
        $this->request        = $request;
    }

    /**
     * TODO: This shouldn't be possible for already paid listings.
     *
     * @since 4.0.0
     */
    public function ajax() {
        try {
            return $this->try_to_clear_listing_information();
        } catch ( AWPCP_Exception $e ) {
            return $this->error_response( $e->getMessage() );
        }
    }

    /**
     * @since 4.0.0
     * @throws AWPCP_Exception  If current user is not allowed to clear the
     *                          listing's information.
     */
    private function try_to_clear_listing_information() {
        $listing   = $this->listings->get( $this->request->post( 'ad_id' ) );
        $post_data = $this->get_new_post_data( $listing );
        $nonce     = $this->request->post( 'nonce' );

        if ( ! wp_verify_nonce( $nonce, "awpcp-clear-listing-information-{$listing->ID}" ) ) {
            throw new AWPCP_Exception( __( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ) );
        }

        // Only admin users are allowed to post listings.
        if ( $this->settings->get_option( 'onlyadmincanplaceads' ) && ! $this->roles->current_user_is_administrator() ) {
            $message = __( 'You are not authorized to perform this action. Only administrator users are allowed to submit classifieds.', 'another-wordpress-classifieds-plugin' );

            throw new AWPCP_Exception( $message );
        }

        // Only registered users are allowed to place listings.
        if ( $this->settings->get_option( 'requireuserregistration' ) && ! is_user_logged_in() ) {
            $message = __( 'Your are not authorized to perform this action. Only logged in users are allowed to submit classifieds.', 'another-wordpress-classifieds-plugin' );

            throw new AWPCP_Exception( $message );
        }

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
                'post_title'   => 'Classified Auto Draft',
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
