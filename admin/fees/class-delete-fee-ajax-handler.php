<?php

function awpcp_delete_fee_ajax_handler() {
    return new AWPCP_DeleteFeeAjaxHandler(
        awpcp_fees_admin_page(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_DeleteFeeAjaxHandler extends AWPCP_TableEntryActionAjaxHandler {

    protected function process_entry_action() {
        $fee = AWPCP_Fee::find_by_id( $this->request->post( 'id' ) );

        if ( is_null( $fee ) ) {
            $message = __( "The specified Fee doesn't exists.", 'another-wordpress-classifieds-plugin' );
            return $this->error( array( 'message' => $message ) );
        }

        $errors = array();

        if ( $this->request->post( 'remove' ) ) {
            if ( AWPCP_Fee::delete( $fee->id, $errors ) ) {
                return $this->success();
            } else {
                return $this->error( array( 'message' => join( '<br/>', $errors ) ) );
            }
        } else {
            $params = array( 'columns' => count( $this->page->get_table()->get_columns() ) );
            $template = AWPCP_DIR . '/admin/templates/delete_form.tpl.php';
            return $this->success( array( 'html' => awpcp_render_template( $template, $params ) ) );
        }
    }
}
