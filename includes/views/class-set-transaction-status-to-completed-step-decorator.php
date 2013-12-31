<?php

class AWPCP_SetTransactionStatusToCompletedStepDecorator extends AWPCP_StepDecorator {

    private $payments;

    public function __construct( $decorated, $payments ) {
        parent::__construct( $decorated );
        $this->payments = $payments;
    }

    public function before_get( $controller ) {
        $this->set_transaction_status_to_completed( $controller );
    }

    public function before_post( $controller ) {
        $this->set_transaction_status_to_completed( $controller );
    }

    private function set_transaction_status_to_completed( $controller ) {
        $transaction = $controller->get_transaction();
        $this->payments->set_transaction_status_to_completed( $transaction );
    }
}
