<?php

function awpcp_delete_listing_ajax_handler() {
    return new AWPCP_DeleteListingAjaxHandler(
        awpcp_manage_listings_admin_page(),
        awpcp_listings_api(),
        awpcp_listings_collection(),
        awpcp_listing_authorization(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_DeleteListingAjaxHandler extends AWPCP_TableEntryActionAjaxHandler {

    private $listings_logic;
    private $listings;
    private $authorization;

    public function __construct( $page, $listings_logic, $listings, $authorization, $request, $response ) {
        parent::__construct( $page, $request, $response );

        $this->listings_logic = $listings_logic;
        $this->listings = $listings;
        $this->authorization = $authorization;
    }

    protected function process_entry_action() {
        $listing_id = $this->request->post( 'id', 0 );

        try {
            $listing = $this->listings->get( $listing_id );
        } catch ( AWPCP_Exception $e ) {
            $message = _x( "The specified Ad doesn't exists.", 'ajax delete ad', 'AWPCP' );
            return $this->error( array( 'message' => $message ) );
        }

        if ( ! $this->authorization->is_current_user_allowed_to_edit_listing( $listing ) ) {
            $message = _x( 'You are not authorized to edit this listing.', 'ajax delete ad', 'AWPCP' );
            return $this->error( array( 'message' => $message ) );
        }

        if ( $this->request->post( 'remove' ) ) {
            $this->delete_listing( $listing );
        } else {
            $params = array( 'columns' => count( $this->page->get_table()->get_columns() ) );
            $template = AWPCP_DIR . '/admin/templates/delete_form.tpl.php';
            return $this->success( array( 'html' => awpcp_render_template( $template, $params ) ) );
        }
    }

    private function delete_listing( $listing ) {
        if ( $this->listings_logic->delete_listing( $listing ) ) {
            return $this->success();
        } else {
            return $this->error( array( 'message' => implode( '<br/>', $errors ) ) );
        }
    }
}
