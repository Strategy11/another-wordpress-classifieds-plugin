<?php
/**
 * @package AWPCP\Listings
 */

/**
 * Renews ad from the front end.
 */
class AWPCP_ListingsRenewAction extends AWPCP_AjaxHandler {

    private $response;
    private $request;
    private $listings_logic;
    private $renew_listing_action;

    public function __construct( $response, $request, $listings_login, $renew_listing_action ) {
        parent::__construct( $response );
        $this->request        = $request;
        $this->response       = $response;
        $this->listings_logic = $listings_login;
        $this->renew_listing_action = $renew_listing_action;
    }

    public function ajax() {
        if ( $this->request->post( 'action' ) === 'awpcp-ad-renew' ) {
            $listing_id = $this->request->post( 'listing_id' );
            $listing    = get_post( $listing_id );
            if ( $listing ) {
                $result  = $this->renew_listing_action->process_item( $listing );
                $message = $this->renew_listing_action->get_message( $result, 1 );
                if ($result !== 'success') {
                    $this->error(array($message));
                }
                $this->success(array($message));
            }
        }
    }
}
