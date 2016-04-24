<?php

function awpcp_edit_fee_ajax_handler() {
    return new AWPCP_TableEntryActionAjaxHandler (
        new AWPCP_Edit_Fee_Action_Handler(
            awpcp_add_edit_table_entry_rendering_helper( awpcp_fees_admin_page() ),
            awpcp_request()
        ),
        awpcp_ajax_response()
    );
}

class AWPCP_Edit_Fee_Action_Handler implements AWPCP_Table_Entry_Action_Handler {

    private $rendering_helper;
    private $request;

    public function __construct( $rendering_helper, $request ) {
        $this->rendering_helper = $rendering_helper;
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
            // $this->html->render( $fee_form );
            $template = AWPCP_DIR . '/admin/templates/admin-panel-fees-entry-form.tpl.php';
            $ajax_handler->success( array( 'html' => $this->rendering_helper->render_entry_form( $template, $fee ) ) );
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
            return $ajax_handler->success( array( 'html' => $this->rendering_helper->render_entry_row( $fee ) ) );
        }
    }
}
