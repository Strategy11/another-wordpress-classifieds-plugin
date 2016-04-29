<?php

function awpcp_add_fee_ajax_handler() {
    return new AWPCP_TableEntryActionAjaxHandler(
        new AWPCP_Add_Fee_Action_Handler(
            awpcp_fee_entry_form(),
            awpcp_add_edit_fee_rendering_helper(),
            awpcp_request()
        ),
        awpcp_ajax_response()
    );
}

class AWPCP_Add_Fee_Action_Handler implements AWPCP_Table_Entry_Action_Handler {

    private $fee_entry_form;
    private $fee_rendering_helper;
    private $request;

    public function __construct( $fee_entry_form, $fee_rendering_helper, $request ) {
        $this->fee_entry_form = $fee_entry_form;
        $this->fee_rendering_helper = $fee_rendering_helper;
        $this->request = $request;
    }

    public function process_entry_action( $ajax_handler ) {
        $fee = new AWPCP_Fee( $_POST );

        if ( $this->request->post( 'save' ) ) {
            $this->save_new_fee( $fee, $ajax_handler );
        } else {
            $ajax_handler->success(array(
                'html' => $this->fee_rendering_helper->render_entry_form( $fee, $this->fee_entry_form ),
            ));
        }
    }

    private function save_new_fee( $fee, $ajax_handler ) {
        $errors = array();

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
