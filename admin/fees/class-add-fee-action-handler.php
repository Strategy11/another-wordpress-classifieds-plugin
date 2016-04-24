<?php

function awpcp_add_fee_ajax_handler() {
    return new AWPCP_TableEntryActionAjaxHandler(
        new AWPCP_Add_Fee_Action_Handler(
            awpcp_add_edit_table_entry_rendering_helper( awpcp_fees_admin_page() ),
            awpcp_request()
        ),
        awpcp_ajax_response()
    );
}

class AWPCP_Add_Fee_Action_Handler implements AWPCP_Table_Entry_Action_Handler {

    private $rendering_helper;
    private $request;

    public function __construct( $rendering_helper, $request ) {
        $this->rendering_helper = $rendering_helper;
        $this->request = $request;
    }

    public function process_entry_action( $ajax_handler ) {
        $fee = new AWPCP_Fee( $_POST );

        if ( $this->request->post( 'save' ) ) {
            $this->save_new_fee( $fee, $ajax_handler );
        } else {
            $template = AWPCP_DIR . '/admin/templates/admin-panel-fees-entry-form.tpl.php';
            $ajax_handler->success( array( 'html' => $this->rendering_helper->render_entry_form( $template, $fee ) ) );
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
            return $ajax_handler->success( array( 'html' => $this->rendering_helper->render_entry_row( $fee ) ) );
        }
    }
}
