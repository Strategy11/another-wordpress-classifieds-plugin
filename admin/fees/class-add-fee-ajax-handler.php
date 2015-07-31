<?php

function awpcp_add_fee_ajax_handler() {
    return new AWPCP_AddFeeAjaxHandler(
        awpcp_fees_admin_page(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_AddFeeAjaxHandler extends AWPCP_AddEditTableEntryAjaxHandler {

    public function process_entry_action() {
        $fee = new AWPCP_Fee( $_POST );

        if ( $this->request->post( 'save' ) ) {
            $this->save_new_fee( $fee );
        } else {
            $template = AWPCP_DIR . '/admin/templates/admin-panel-fees-entry-form.tpl.php';
            $this->success( array( 'html' => $this->render_entry_form( $template, $fee ) ) );
        }
    }

    private function save_new_fee( $fee ) {
        $errors = array();

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
