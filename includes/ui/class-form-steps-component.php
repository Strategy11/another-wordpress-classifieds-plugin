<?php
/**
 * @package AWPCP\UI
 */

class AWPCP_FormStepsComponent {

    /**
     * @var FormSteps
     */
    private $form_steps;

    /**
     * @var bool
     */
    private $echo = false;

    public function __construct( AWPCP_FormSteps $form_steps ) {
        $this->form_steps = $form_steps;
    }

    /**
     * @since x.x
     */
    public function show( $selected_step, $params = [] ) {
        $this->echo = true;
        $this->render( $selected_step, $params );
        $this->echo = false;
    }

    /**
     * @since 4.0.0     $transaction parameter was replaced by an optional $params array.
     */
    public function render( $selected_step, $params = [] ) {
        return $this->render_steps( $selected_step, $this->form_steps->get_steps( $params ) );
    }

    private function render_steps( $selected_step, $steps ) {
        $form_steps = $this->prepare_steps( $steps, $selected_step );
        $file       = AWPCP_DIR . '/templates/components/form-steps.tpl.php';
        $echo       = $this->echo;

        return awpcp_get_file_contents( $file, compact( 'form_steps', 'echo' ) );
    }

    private function prepare_steps( $steps, $selected_step ) {
        $form_steps = array();

        $previous_steps = array();
        $steps_count = 0;

        foreach ( $steps as $step => $name ) {
            ++$steps_count;

            if ( $selected_step == $step ) {
                $step_class = 'current';
            } elseif ( ! in_array( $selected_step, $previous_steps ) ) {
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
