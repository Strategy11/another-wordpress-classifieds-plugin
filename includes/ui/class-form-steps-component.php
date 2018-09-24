<?php
/**
 * @package AWPCP\UI
 */

// phpcs:disable

function awpcp_render_listing_form_steps( $selected_step, $transaction = null ) {
    return awpcp_listing_form_steps_componponent()->render( $selected_step, $transaction );
}

function awpcp_listing_form_steps_componponent() {
    return new AWPCP_FormStepsComponent(
        new AWPCP_SubmitListingFormSteps(
            awpcp_payments_api(),
            awpcp()->settings,
            awpcp_request()
        )
    );
}

class AWPCP_FormStepsComponent {

    /**
     * @var FormSteps
     */
    private $form_steps;

    public function __construct( AWPCP_FormSteps $form_steps ) {
        $this->form_steps = $form_steps;
    }

    public function render( $selected_step, $transaction ) {
        $steps = $this->form_steps->get_steps( compact( 'transaction' ) );

        return $this->render_steps( $selected_step, $steps );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function render_steps( $selected_step, $steps ) {
        $form_steps = $this->prepare_steps( $steps, $selected_step );

        ob_start();
        include( AWPCP_DIR . '/templates/components/form-steps.tpl.php' );
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function prepare_steps( $steps, $selected_step ) {
        $form_steps = array();

        $previous_steps = array();
        $steps_count = 0;

        foreach ( $steps as $step => $name ) {
            $steps_count = $steps_count + 1;

            if ( $selected_step == $step ) {
                $step_class = 'current';
            } else if ( ! in_array( $selected_step, $previous_steps ) ) {
                $step_class = 'completed';
            } else {
                $step_class = 'pending';
            }

            $form_steps[ $step ] = array( 'number' => $steps_count, 'name' => $name, 'class' => $step_class );

            $previous_steps[] = $step;
        }

        return $form_steps;
    }
}
