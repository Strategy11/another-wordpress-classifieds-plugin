<?php

function awpcp_add_credit_plan_ajax_handler() {
    return new AWPCP_AddCreditPlanAjaxHandler(
        awpcp_credit_plans_admin_page(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_AddCreditPlanAjaxHandler extends AWPCP_AddEditTableEntryAjaxHandler {

    protected function process_entry_action() {
        $plan = new AWPCP_CreditPlan( $_POST );

        if ( $this->request->post( 'save' ) ) {
            $this->save_new_credit_plan( $plan );
        } else {
            $template = AWPCP_DIR . '/admin/templates/admin-panel-credit-plans-entry-form.tpl.php';
            $this->success( array( 'html' => $this->render_entry_form( $template, $plan ) ) );
        }
    }

    private function save_new_credit_plan( $plan ) {
        $errors = array();

        if ( $plan->save( $errors ) === false ) {
            return $this->error( array(
                'message' => __( 'The form has errors', 'AWPCP' ),
                'errors' => $errors
            ) );
        } else {
            return $this->success( array( 'html' => $this->render_entry_row( $plan ) ) );
        }
    }
}
