<?php

function awpcp_edit_fee_ajax_handler() {
    return new AWPCP_EditFeeAjaxHandler(
        awpcp_fees_admin_page(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_EditFeeAjaxHandler extends AWPCP_AddEditTableEntryAjaxHandler {

    public function process_entry_action() {
        $fee = $this->find_fee_by_id( $this->request->post( 'id' ) );

        if ( is_null( $fee ) ) {
            $message = __( "The specified Fee doesn't exists.", 'AWPCP' );
            return $this->error( array( 'message' => $message ) );
        }

        if ( $this->request->post( 'save' ) ) {
            $this->save_existing_fee( $fee );
        } else {
            $template = AWPCP_DIR . '/admin/templates/admin-panel-fees-entry-form.tpl.php';
            $this->success( array( 'html' => $this->render_entry_form( $template, $fee ) ) );
        }
    }

    protected function find_fee_by_id( $id ) {
        return AWPCP_Fee::find_by_id( $id );
    }

    private function save_existing_fee( $fee ) {
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
        ) );

        if ( $fee->save( $errors ) === false ) {
            return $this->error( array(
                'message' => __( 'The form has errors', 'AWPCP' ),
                'errors' => $errors,
            ) );
        } else {
            return $this->success( array( 'html' => $this->render_entry_row( $fee ) ) );
        }
    }
}
