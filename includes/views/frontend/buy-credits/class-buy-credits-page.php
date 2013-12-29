<?php

require_once(AWPCP_DIR . '/includes/helpers/page.php');

/**
 * @since 3.0.2
 */
class AWPCP_BuyCreditsPage extends AWPCP_Page {

    private $request = null;

    private $do_next_step = true;
    private $output = '';

    public $messages = array();
    public $errors = array();

    public function __construct( $steps, $request ) {
        parent::__construct( 'awpcp-buy-credits', __( 'Buy Credits', 'AWPCP' ) );

        $this->steps = $steps;
        $this->request = $request;
    }

    public function get_transaction( $create = true ) {
        $id = $this->request->param( 'transaction_id' );

        // TODO: inject PaymentTransaction dependecny
        if ( ! isset( $this->transaction ) && $create === true  ) {
            $this->transaction = AWPCP_Payment_Transaction::find_or_create( $id );
        } else if ( ! isset( $this->transaction ) ) {
            $this->transaction = AWPCP_Payment_Transaction::find_by_id( $id );
        }

        if ( ! is_null( $this->transaction ) && $this->transaction->is_new() ) {
            $this->transaction->user_id = wp_get_current_user()->ID;
            $this->transaction->set( 'context', 'add-credit' );
            $this->transaction->set( 'redirect', $this->url() );
            $this->transaction->set( 'redirect-data', array( 'action' => 'payment-completed' ) );
        }

        return $this->transaction;
    }

    public function render( $template, $params=array() ) {
        $this->output = parent::render( $template, $params );
    }

    public function dispatch() {
        if ( $this->is_user_allowed_to_buy_credits() ) {
            $this->do_page();
        } else {
            $this->errors[] = __( 'You are not allowed to buy credits.', 'AWPCP' );
            $this->render_page_error();
        }

        return $this->output;
    }

    private function is_user_allowed_to_buy_credits() {
        return awpcp_current_user_is_admin() ? false : true;
    }

    private function do_page() {
        try {
            $this->handle_request();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            $this->render_page_error();
        }
    }

    private function handle_request() {
        $current_step = $this->get_current_step();
        $this->do_steps( $current_step );
    }

    private function get_current_step() {
        if ( ! isset( $this->step ) ) {
            $step_name = $this->request->param( 'step', 'select-credit-plan' );
            $this->step = $this->get_step_by_name( $step_name );
        }

        return $this->step;
    }

    private function get_step_by_name( $step_name ) {
        if ( isset( $this->steps[ $step_name ] ) ) {
            return $this->steps[ $step_name ];
        } else {
            throw new Exception( __( 'Unkown step. Please contact the administrator about this error.', 'AWPCP' ) );
        }
    }

    private function do_steps( $current_step ) {
        try {
            $this->do_step_method( $current_step );
            $this->do_next_step();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            $this->handle_step_exception( $current_step );
        }
    }

    private function do_step_method( $step ) {
        switch ( $this->request->method() ) {
            case 'POST':
                $step->post( $this );
                break;
            case 'GET':
            default:
                $step->get( $this );
                break;
        }
    }

    private function do_next_step() {
        if ( $this->do_next_step ) {
            $step = $this->get_next_step();
            $step->get( $this );
        }
    }

    private function get_next_step() {
        if ( ! isset( $this->next_step ) ) {
            $current_step = $this->get_current_step();
            $this->next_step = $this->calculate_next_step( $current_step );
        }

        return $this->next_step;
    }

    public function calculate_next_step( $current_step ) {
        throw new Exception( __( 'Not yet implemented.', 'AWPCP' ) );
    }

    private function handle_step_exception( $step ) {
        if ( $this->request->method() === 'POST' ) {
            $step->get( $this );
        } else {
            $message = __( 'Your request cannot be processed at this time. Please try again or contact the administrator about the incident.', 'AWPCP' );
            throw new Exception( $message );
        }
    }

    private function render_page_error() {
        $template = AWPCP_DIR . '/frontend/templates/page-error.tpl.php';
        $params = array( 'errors' => $this->errors );
        $this->render( $template, $params );
    }

    public function set_next_step( $step_name ) {
        $this->next_step = $this->get_step_by_name( $step_name );
    }

    public function skip_next_step() {
        $this->do_next_step = false;
    }
}

function awpcp_buy_credits_page() {
    $request = new AWPCP_Request();
    $payments = awpcp_payments_api();

    $steps = array(
        'select-credit-plan' =>
            new AWPCP_SetTransactionStatusToOpenStepDecorator(
                new AWPCP_SetCreditPlanStepDecorator(
                    new AWPCP_VerifyCreditPlanWasSetStepDecorator(
                        new AWPCP_PrepareTransactionForPaymentStepDecorator(
                            new AWPCP_BuyCreditsPageSelectCreditPlanStep( $payments ),
                            $payments,
                            'payment-completed',
                            'checkout'
                        ),
                        $payments
                    ),
                    $payments
                ),
                $payments
            ),
        'checkout' =>
            new AWPCP_VerifyTransactionExistsStepDecorator(
                new AWPCP_SetTransactionStatusToCheckoutStepDecorator(
                    new AWPCP_VerifyPaymentCanBeProcessedStepDecorator(
                        new AWPCP_SetPaymentMethodStepDecorator(
                            new AWPCP_BuyCreditsPageCheckoutStep( $payments ),
                            $payments
                        )
                    ),
                    $payments
                )
            ),
        'payment-completed' => new AWPCP_BuyCreditsPagePaymentCompletedStep( $payments ),
        'final' =>
            new AWPCP_SetTransactionStatusToCompletedStepDecorator(
                new AWPCP_BuyCreditsPageFinalStep( $payments ),
                $payments
            ),
    );

    return new AWPCP_BuyCreditsPage( $steps, $request );
}
