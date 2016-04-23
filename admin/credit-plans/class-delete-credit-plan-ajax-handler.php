<?php

function awpcp_delete_credit_plan_ajax_handler() {
    return new AWPCP_DeleteCreditPlanAjaxHandler(
        awpcp_credit_plans_admin_page(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_DeleteCreditPlanAjaxHandler extends AWPCP_TableEntryActionAjaxHandler {

    protected function process_entry_action() {
        $plan = AWPCP_CreditPlan::find_by_id( $this->request->post( 'id' ) );

        if ( is_null( $plan ) ) {
            $message = _x( "The specified Credit Plan doesn't exists.", 'credit plans ajax', 'another-wordpress-classifieds-plugin' );
            return $this->error( array( 'message' => $message ) );
        }

        $errors = array();

        if ( $this->request->post( 'remove' ) ) {
            if ( AWPCP_CreditPlan::delete( $plan->id, $errors ) ) {
                return $this->success();
            } else {
                return $this->error( array( 'message' => join( '<br/>', $errors ) ) );
            }
        } else {
            $params = array( 'columns' => 5 );
            $template = AWPCP_DIR . '/admin/templates/delete_form.tpl.php';
            return $this->success( array( 'html' => awpcp_render_template( $template, $params ) ) );
        }
    }
}
