<?php

class AWPCP_ListingsViewCounter extends AWPCP_AjaxHandler {

    private $response;
    private $request;
    private $listingsLogic;

    public function __construct( $response, $request, $listingsLogic ) {
        parent::__construct( $response );
        $this->request = $request;
        $this->response = $response;
        $this->listingsLogic = $listingsLogic;
    }

    public function ajax() {
        if ( ! $this->request->is_bot() ) {
            $listing_id = $this->request->post('listing_id');
            $listing = get_post($listing_id);
            $this->listingsLogic->increase_visits_count( $listing );
        }
    }
}
