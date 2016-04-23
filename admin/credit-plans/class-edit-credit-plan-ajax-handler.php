<?php

function awpcp_edit_credit_plan_ajax_handler() {
    return new AWPCP_EditCreditPlanAjaxHandler(
        awpcp_credit_plans_admin_page(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_EditCreditPlanAjaxHandler extends AWPCP_AddEditTableEntryAjaxHandler {

    protected function process_entry_action() {
        $plan = AWPCP_CreditPlan::find_by_id( $this->request->post( 'id' ) );

        if ( is_null( $plan ) ) {
            $message = _x( "The specified Credit Plan doesn't exists.", 'credit plans ajax', 'another-wordpress-classifieds-plugin' );
            return $this->error( array( 'message' => $message ) );
        }

        if ( $this->request->post( 'save' ) ) {
            $this->save_existing_credit_plan( $plan );
        } else {
            $template = AWPCP_DIR . '/admin/templates/admin-panel-credit-plans-entry-form.tpl.php';
            $this->success( array( 'html' => $this->render_entry_form( $template, $plan ) ) );
        }
    }

    private function save_existing_credit_plan( $plan ) {
        $errors = array();

        $plan->name = $this->request->post( 'name' );
        $plan->description = $this->request->post( 'description' );
        $plan->credits = $this->request->post( 'credits' );
        $plan->price = $this->request->post( 'price' );

        if ( $plan->save( $errors ) === false ) {
            return $this->error( array(
                'message' => __( 'The form has errors', 'another-wordpress-classifieds-plugin' ),
                'errors' => $errors
            ) );
        } else {
            return $this->success( array( 'html' => $this->render_entry_row( $plan ) ) );
        }
    }
}
