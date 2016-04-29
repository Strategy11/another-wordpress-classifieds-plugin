<?php

function awpcp_edit_fee_ajax_handler() {

    return new AWPCP_TableEntryActionAjaxHandler (
        new AWPCP_Edit_Fee_Action_Handler(
            awpcp_fee_entry_form(),
            awpcp_add_edit_fee_rendering_helper(),
            awpcp_request()
        ),
        awpcp_ajax_response()
    );
}

class AWPCP_Edit_Fee_Action_Handler implements AWPCP_Table_Entry_Action_Handler {

    private $fee_entry_form;
    private $fee_rendering_helper;
    private $request;

    public function __construct( $fee_entry_form, $fee_rendering_helper, $request ) {
        $this->fee_entry_form = $fee_entry_form;
        $this->fee_rendering_helper = $fee_rendering_helper;
        $this->request = $request;
    }

    public function process_entry_action( $ajax_handler ) {
        $fee = $this->find_fee_by_id( $this->request->post( 'id' ) );

        if ( is_null( $fee ) ) {
            $message = __( "The specified Fee doesn't exists.", 'another-wordpress-classifieds-plugin' );
            return $ajax_handler->error( array( 'message' => $message ) );
        }

        if ( $this->request->post( 'save' ) ) {
            $this->save_existing_fee( $fee, $ajax_handler );
        } else {
            $ajax_handler->success(array(
                'html' => $this->fee_rendering_helper->render_entry_form( $fee, $this->fee_entry_form ),
            ));
        }
    }

    protected function find_fee_by_id( $id ) {
        return AWPCP_Fee::find_by_id( $id );
    }

    private function save_existing_fee( $fee, $ajax_handler ) {
        $errors = array();

        $fee->update( array(
            'name' => $this->request->post( 'name' ),
            'price' => $this->request->post( 'price' ),
            'credits' => $this->request->post( 'credits' ),
            'duration_amount' => $this->request->post( 'duration_amount' ),
            'duration_interval' => $this->request->post( 'duration_interval' ),
            'images' => $this->request->post( 'images' ),
            'characters' => $this->request->post( 'characters' ),
            'title_characters' => $this->request->post( 'title_characters' ),
            'private' => $this->request->post( 'private', false ),
            'featured' => $this->request->post( 'featured', false ),
            'categories' => array_filter( $this->request->post( 'categories', array() ) ),
        ) );

        if ( $fee->save( $errors ) === false ) {
            return $ajax_handler->error( array(
                'message' => __( 'The form has errors', 'another-wordpress-classifieds-plugin' ),
                'errors' => $errors,
            ) );
        } else {
            return $ajax_handler->success( array( 'html' => $this->fee_rendering_helper->render_entry_row( $fee ) ) );
        }
    }
}
