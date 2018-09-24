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
        awpcp_payments_api(),
        awpcp()->settings,
        awpcp_request()
    );
}

class AWPCP_FormStepsComponent {

    private $payments;
    private $settings;
    private $request;

    public function __construct( $payments, $settings, $request ) {
        $this->payments = $payments;
        $this->settings = $settings;
        $this->request = $request;
    }

    public function render( $selected_step, $transaction ) {
        return $this->render_steps( $selected_step, $this->get_steps( $transaction ) );
    }

    private function get_steps( $transaction ) {
        $steps = array();

        if ( $this->should_show_login_step( $transaction ) ) {
            $steps['login'] = __( 'Login/Registration', 'another-wordpress-classifieds-plugin' );
        }

        $should_show_payment_steps = $this->payments->payments_enabled();
        $should_pay_before         = $this->settings->get_option( 'pay-before-place-ad' );

        if ( $should_show_payment_steps && $should_pay_before ) {
            $steps['listing-category'] = __( 'Select a Category', 'another-wordpress-classifieds-plugin' );
            $steps['checkout']         = __( 'Checkout', 'another-wordpress-classifieds-plugin' );
            $steps['payment']          = __( 'Payment', 'another-wordpress-classifieds-plugin' );
        }

        $steps['listing-information']  = __( 'Enter Ad Information', 'another-wordpress-classifieds-plugin' );

        if ( $should_show_payment_steps && ! $should_pay_before ) {
            $steps['checkout']        = __( 'Checkout', 'another-wordpress-classifieds-plugin' );
            $steps['payment']         = __( 'Payment', 'another-wordpress-classifieds-plugin' );
        }

        $steps['finish'] = __( 'Finish', 'another-wordpress-classifieds-plugin' );

        return $steps;
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function should_show_login_step( $transaction ) {
        if ( ! is_user_logged_in() && ! $this->settings->get_option( 'requireuserregistration' ) ) {
            return false;
        }

        if ( ! is_user_logged_in() ) {
            return true;
        }

        if ( ! is_null( $transaction ) ) {
            return $transaction->get( 'user-just-logged-in', false );
        }

        return $this->request->param( 'loggedin', false );
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
