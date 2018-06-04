<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Ajax handler for the action that retrieves up to date versions of the specified
 * submit listing sections.
 */
class AWPCP_UpdateSubmitListingSectionsAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @var SubmitListingSectionsGenerator
     */
    private $sections_generator;

    /**
     * @var ListingsCollection
     */
    private $listings;

    /**
     * @var Request
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $sections_generator, $listings, $response, $request ) {
        parent::__construct( $response );

        $this->sections_generator = $sections_generator;
        $this->listings           = $listings;
        $this->request            = $request;
    }

    /**
     * @since 4.0.0
     */
    public function ajax() {
        $listing_id = $this->request->param( 'listing' );

        try {
            $listing = $this->listings->get( $listing_id );
        } catch ( AWPCP_Exception $e ) {
            return $this->error_response( $e->getMessage() );
        }

        $response = [
            'sections' => $this->sections_generator->get_sections( $listing ),
        ];

        return $this->success( $response );
    }
}
