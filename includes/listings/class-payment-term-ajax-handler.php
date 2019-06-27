<?php
/**
 * @package AWPCP\Media
 */

/**
 * Updates the payment term of a listing.
 */
class AWPCP_PaymentTermAjaxHandler extends AWPCP_AjaxHandler {

    private $request;
    private $metabox;
    private $listings;

    public function __construct( $request, $metabox, $response, $listings ) {
        parent::__construct( $response );

        $this->request  = $request;
        $this->metabox  = $metabox;
        $this->listings = $listings;
    }

    public function ajax() {
        $listing = $this->listings->get( $this->request->post( 'listing' ) );
        if ( wp_verify_nonce( $this->request->post( 'nonce' ), 'awpcp-upload-media-for-listing-' . $listing->ID ) ) {
            $this->metabox->save( $listing->ID, $listing );
        }
        return false;
    }
}
