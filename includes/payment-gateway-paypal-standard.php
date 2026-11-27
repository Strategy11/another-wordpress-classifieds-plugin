<?php
/**
 * @package AWPCP\Payments
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Returns instance AWPCP_PayPalStandardPaymentGateway.
 */
function awpcp_paypal_standard_payment_gateway() {
    return new AWPCP_PayPalStandardPaymentGateway( awpcp_request() );
}

/**
 * Class AWPCP_PayPalStandardPaymentGateway
 */
class AWPCP_PayPalStandardPaymentGateway extends AWPCP_PaymentGateway {

    const PAYPAL_URL  = 'https://www.paypal.com/cgi-bin/webscr';
    const SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    public $request;

    public function __construct( $request ) {
        parent::__construct(
            'paypal',
            _x( 'PayPal', 'payment gateways', 'another-wordpress-classifieds-plugin' ),
            '',
            AWPCP_URL . '/resources/images/payments-paypal.jpg'
        );

        $this->request = $request;
    }

    public function get_integration_type() {
        return self::INTEGRATION_BUTTON;
    }

    public function verify_transaction( $transaction ) {
        $errors = array();

        /*
        PayPal can redirect users using a GET request and issuing
        a POST request in the background. If the transaction was
        already verified during the POST request the result
        should be stored in the transaction's verified attribute.
        */
        $response = null;
        $verified = $transaction->get( 'verified', false );
        // phpcs:ignore WordPress.Security.NonceVerification
        if ( ! empty( $_POST ) ) {
            // Verification reads raw body from php://input for byte-for-byte accuracy.
            $response = awpcp_paypal_verify_received_data( array(), $errors );
            $verified = strcasecmp( $response, 'VERIFIED' ) === 0;
        }

        if ( ! $verified ) {
            // phpcs:ignore WordPress.Security.NonceVerification
            $variables = count( $_POST );
            $url       = awpcp_current_url();

            if ( $variables <= 0 ) {
                /* translators: %1$s and %2$s are the current URL. */
                $message  = __( "We haven't received your payment information from PayPal yet and we are unable to verify your transaction. Please reload this page or visit <a href=\"%1\$s\">%2\$s</a> in 30 seconds to continue placing your Ad.", 'another-wordpress-classifieds-plugin' );
                $errors[] = sprintf( $message, $url, $url );

                $transaction->errors['verification-get'] = $errors;
            } elseif ( 'INVALID' === $response ) {
                // The caller determines whether this is a return or an IPN.
                unset( $transaction->errors['verification-get'] );
                unset( $transaction->errors['verification-post'] );
            } elseif ( 'ERROR' === $response ) {
                // ERROR means something went wrong with the verification request itself.
                $errors[] = __( 'There was an error verifying your payment with PayPal. Please wait a moment and refresh this page.', 'another-wordpress-classifieds-plugin' );
                $errors[] = __( 'If you have any further questions, please contact this site administrator.', 'another-wordpress-classifieds-plugin' );

                $transaction->errors['verification-post'] = $errors;

                // Clear pending verification flag to avoid stale state.
                $transaction->set( 'pending_verification', false );
            }
        } else {
            // Clean up previous errors and pending state.
            unset( $transaction->errors['verification-get'] );
            unset( $transaction->errors['verification-post'] );
            $transaction->set( 'pending_verification', false );
        }

        $txn_id = awpcp_get_var( array( 'param' => 'txn_id' ), 'post' );
        $transaction->set( 'txn-id', $txn_id );
        $transaction->set( 'verified', $verified );

        return $response;
    }

    private function validate_transaction( $transaction ) {
        $errors = $transaction->errors;

        // PayPal can redirect users using a GET request and issuing
        // a POST request in the background. If the transaction was
        // already verified during the POST transaction the result
        // should be stored in the transaction's validated attribute.
        // phpcs:ignore WordPress.Security.NonceVerification
        if ( empty( $_POST ) ) {
            return $transaction->get( 'validated', false );
        }

        $txn_id      = awpcp_get_var( array( 'param' => 'txn_id' ), 'post' );
        $txn_type    = awpcp_get_var( array( 'param' => 'txn_type' ), 'post' );
        $custom      = awpcp_get_var( array( 'param' => 'custom' ), 'post' );
        $payer_email = awpcp_get_var( array( 'param' => 'payer_email' ), 'post' );

        if ( strcasecmp( (string) $custom, (string) $transaction->id ) !== 0 ) {
            $message                           = __( 'The payment transaction could not be verified. Please contact customer service for assistance.', 'another-wordpress-classifieds-plugin' );
            $transaction->errors['validation'] = $message;
            $transaction->payment_status       = AWPCP_Payment_Transaction::PAYMENT_STATUS_INVALID;
            $transaction->set( 'verified', false );
            awpcp_payment_failed_email( $transaction, $message );
            return false;
        }

        // this variables are not used for verification purposes.
        $item_name            = awpcp_get_var( array( 'param' => 'item_name' ), 'post' );
        $item_number          = awpcp_get_var( array( 'param' => 'item_number' ), 'post' );
        $quantity             = awpcp_get_var( array( 'param' => 'quantity' ), 'post' );
        $mc_fee               = awpcp_get_var( array( 'param' => 'mc_fee' ), 'post' );
        $exchange_rate        = awpcp_get_var( array( 'param' => 'exchange_rate' ), 'post' );
        $payment_status       = awpcp_get_var( array( 'param' => 'payment_status' ), 'post' );
        $payment_type         = awpcp_get_var( array( 'param' => 'payment_type' ), 'post' );
        $payment_date         = awpcp_get_var( array( 'param' => 'payment_date' ), 'post' );
        $first_name           = awpcp_get_var( array( 'param' => 'first_name' ), 'post' );
        $last_name            = awpcp_get_var( array( 'param' => 'last_name' ), 'post' );
        $address_street       = awpcp_get_var( array( 'param' => 'address_street' ), 'post' );
        $address_zip          = awpcp_get_var( array( 'param' => 'address_zip' ), 'post' );
        $address_city         = awpcp_get_var( array( 'param' => 'address_city' ), 'post' );
        $address_state        = awpcp_get_var( array( 'param' => 'address_state' ), 'post' );
        $address_country      = awpcp_get_var( array( 'param' => 'address_country' ), 'post' );
        $address_country_code = awpcp_get_var( array( 'param' => 'address_country_code' ), 'post' );
        $residence_country    = awpcp_get_var( array( 'param' => 'residence_country' ), 'post' );

        // Refund and reversal notifications have no txn_type and a negative amount.
        // They can only mark the payment as failed, so those checks don't apply.
        $is_failed_payment = $this->is_failed_payment_status( $payment_status );

        // TODO: Add support for recurring payments and subscriptions?
        if ( ! $is_failed_payment && ! in_array( $txn_type, array( 'web_accept', 'cart' ), true ) ) {
            // we do not support other forms of payment right now.
            return;
        }

        if ( ! $is_failed_payment && ! $this->payment_matches_transaction_total( $transaction ) ) {
            $message                           = __( 'The amount you have paid does not match the required amount for this transaction. Please contact us to clarify the problem.', 'another-wordpress-classifieds-plugin' );
            $transaction->errors['validation'] = $message;
            $transaction->payment_status       = AWPCP_Payment_Transaction::PAYMENT_STATUS_INVALID;
            awpcp_payment_failed_email( $transaction, $message );
            return false;
        }

        if ( ! $this->funds_were_sent_to_correct_receiver() ) {
            $message                           = __( 'There was an error processing your transaction. If funds have been deducted from your account, they have not been processed to our account. You will need to contact PayPal about the matter.', 'another-wordpress-classifieds-plugin' );
            $transaction->errors['validation'] = $message;
            $transaction->payment_status       = AWPCP_Payment_Transaction::PAYMENT_STATUS_INVALID;
            awpcp_payment_failed_email( $transaction, $message );
            return false;
        }

        // TODO: handle this filter for Ads and Subscriptions.
        $duplicated = apply_filters( 'awpcp-payments-is-duplicated-transaction', false, $txn_id );
        if ( $duplicated ) {
            $message                           = __( 'It appears this transaction has already been processed. If you do not see your ad in the system please contact the site adminstrator for assistance.', 'another-wordpress-classifieds-plugin' );
            $transaction->errors['validation'] = $message;
            $transaction->payment_status       = AWPCP_Payment_Transaction::PAYMENT_STATUS_INVALID;
            awpcp_payment_failed_email( $transaction, $message );
            return false;
        }

        if ( strcasecmp( $payment_status, 'Completed' ) === 0 ) {
            $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED;

        } elseif ( strcasecmp( $payment_status, 'Pending' ) === 0 ) {
            $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING;
        } elseif ( $is_failed_payment ) {
            $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED;

        } else {
            $message                           = __( "We couldn't determine the payment status for your transaction. Please contact customer service if you are viewing this message after having made a payment. If you have not tried to make a payment and you are viewing this message, it means this message is being shown in error and can be disregarded.", 'another-wordpress-classifieds-plugin' );
            $transaction->errors['validation'] = $message;
            $transaction->payment_status       = AWPCP_Payment_Transaction::PAYMENT_STATUS_UNKNOWN;

            return false;
        }

        // at this point the validation was successful, any previously stored
        // errors are irrelevant.
        unset( $transaction->errors['validation'] );

        $transaction->set( 'validated', true );
        $transaction->payment_gateway = $this->slug;
        $transaction->payer_email     = $payer_email;

        return true;
    }

    /**
     * Whether the PayPal payment status means the payment failed or was taken back.
     *
     * @since x.x
     *
     * @param string $payment_status The payment_status sent by PayPal.
     *
     * @return bool
     */
    private function is_failed_payment_status( $payment_status ) {
        $failed_statuses = array(
            'refunded',
            'reversed',
            'partially-refunded',
            'canceled_reversal',
            'denied',
            'expired',
            'failed',
            'voided',
        );

        return in_array( strtolower( (string) $payment_status ), $failed_statuses, true );
    }

    /**
     * Whether the amount and currency PayPal received match the transaction total.
     *
     * @since x.x
     *
     * @param AWPCP_Payment_Transaction $transaction The payment transaction.
     *
     * @return bool
     */
    private function payment_matches_transaction_total( $transaction ) {
        $mc_gross      = awpcp_get_var( array( 'param' => 'mc_gross', 'sanitize' => 'floatval' ), 'post' );
        $payment_gross = awpcp_get_var( array( 'param' => 'payment_gross', 'sanitize' => 'floatval' ), 'post' );
        $tax           = awpcp_get_var( array( 'param' => 'tax', 'sanitize' => 'floatval' ), 'post' );
        $currency      = awpcp_get_var( array( 'param' => 'mc_currency' ), 'post' );

        $totals           = $transaction->get_totals();
        $amount           = number_format( $totals['money'], 2 );
        $received_amounts = array(
            number_format( $mc_gross, 2 ),
            number_format( $payment_gross, 2 ),
            number_format( $mc_gross - $tax, 2 ),
        );

        if ( ! in_array( $amount, $received_amounts, true ) ) {
            return false;
        }

        return strcasecmp( (string) $currency, (string) get_awpcp_option( 'paypalcurrencycode' ) ) === 0;
    }

    public function funds_were_sent_to_correct_receiver() {
        $receiver = awpcp_get_var( array( 'param' => 'receiver_email' ), 'post' );
        $business = awpcp_get_var( array( 'param' => 'business' ), 'post' );

        $email_addresses = array( $receiver, $business );
        $email_addresses = array_filter( $email_addresses, 'awpcp_is_valid_email_address' );

        $paypal_email = get_awpcp_option( 'paypalemail' );

        foreach ( $email_addresses as $email_address ) {
            if ( strcasecmp( $paypal_email, $email_address ) === 0 ) {
                return true;
            }
        }

        $received_id  = awpcp_get_var( array( 'param' => 'received_id' ), 'post' );
        $merchant_ids = array( $received_id, $business );
        $merchant_ids = array_filter( $merchant_ids, 'strlen' );

        $paypal_merchant_id = get_awpcp_option( 'paypal_merchant_id' );

        foreach ( $merchant_ids as $merchant_id ) {
            if ( strcasecmp( $paypal_merchant_id, $merchant_id ) === 0 ) {
                return true;
            }
        }

        return false;
    }

    public function process_payment( $transaction ) {
        return $this->render_payment_button( $transaction );
    }

    private function render_payment_button( $transaction ) {
        global $awpcp_imagesurl;

        // no current support for multiple items.
        $item = $transaction->get_item( 0 );

        $is_recurring         = get_awpcp_option( 'paypalpaymentsrecurring' );
        $is_test_mode_enabled = intval( get_awpcp_option( 'paylivetestmode' ) ) === 1;

        $currency = get_awpcp_option( 'paypalcurrencycode' );
        $custom   = $transaction->id;

        $totals = $transaction->get_totals();
        $amount = $totals['money'];

        $payments   = awpcp_payments_api();
        $return_url = $payments->get_return_url( $transaction );
        $notify_url = $payments->get_notify_url( $transaction );
        $cancel_url = $payments->get_cancel_url( $transaction );

        $paypal_url = $is_test_mode_enabled ? self::SANDBOX_URL : self::PAYPAL_URL;

        ob_start();
            include AWPCP_DIR . '/frontend/templates/payments-paypal-payment-button.tpl.php';
            $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function process_payment_completed( $transaction ) {
        $this->do_process_payment( $transaction, false );
    }

    public function process_payment_notification( $transaction ) {
        $this->do_process_payment( $transaction, true );
    }

    /**
     * Process the payment verification.
     *
     * @since 4.4.4
     *
     * @param AWPCP_Payment_Transaction $transaction The payment transaction.
     * @param bool                      $is_ipn      Whether this is an IPN notification.
     */
    private function do_process_payment( $transaction, $is_ipn ) {
        if ( $transaction->get( 'verified', false ) ) {
            $transaction->set( 'pending_verification', false );

            if ( $is_ipn && $this->request->post( 'verify_sign' ) ) {
                $this->process_follow_up_notification( $transaction );
            }

            return;
        }

        if ( ! $this->request->post( 'verify_sign' ) ) {
            $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_VERIFIED;
            return;
        }

        $response                    = $this->verify_transaction( $transaction );
        $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_UNKNOWN;

        if ( 'VERIFIED' === $response ) {
            $this->validate_transaction( $transaction );
            return;
        }

        if ( 'INVALID' === $response ) {
            if ( $is_ipn ) {
                // IPN returning INVALID is a real failure from PayPal.
                $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_INVALID;
                $transaction->set( 'pending_verification', false );
            } else {
                // Wait for an IPN instead of treating an unverified return as payment pending.
                $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_VERIFIED;
                $transaction->set( 'pending_verification', true );
            }
        } elseif ( 'ERROR' === $response ) {
            // ERROR means the verification request itself failed (network, server issue, etc.).
            // Clear pending verification to avoid showing stale pending UI.
            $transaction->set( 'pending_verification', false );

            // Keep status as UNKNOWN - user can retry by refreshing.
        }
    }

    /**
     * Applies an IPN received for a transaction that was already verified.
     *
     * PayPal sends more notifications for the same payment when its status
     * changes (an eCheck clears or is denied, a payment is reversed). Only
     * genuine notifications for the original payment are applied, and only
     * when they move the payment forward: Pending to Completed, or Pending
     * and Completed to Failed. Refund notifications are not applied, so
     * refunds keep being handled by the site administrator.
     *
     * @since x.x
     *
     * @param AWPCP_Payment_Transaction $transaction The payment transaction.
     */
    private function process_follow_up_notification( $transaction ) {
        $original_txn_id  = $transaction->get( 'txn-id' );
        $previous_status  = $transaction->payment_status;
        $payment_status   = awpcp_get_var( array( 'param' => 'payment_status' ), 'post' );
        $applied_statuses = array( 'completed', 'denied', 'expired', 'failed', 'reversed', 'voided' );

        if ( ! in_array( strtolower( (string) $payment_status ), $applied_statuses, true ) ) {
            return;
        }

        if ( ! $this->notification_belongs_to_payment( $transaction, $original_txn_id ) ) {
            return;
        }

        $previous_errors      = $transaction->errors;
        $previous_payer_email = $transaction->payer_email;

        $response = $this->verify_transaction( $transaction );

        // Keep the original payment's state; the notification can't change it.
        $transaction->set( 'verified', true );
        $transaction->set( 'txn-id', $original_txn_id );

        if ( 'VERIFIED' === $response ) {
            $this->validate_transaction( $transaction );
        }

        if ( ! $this->is_allowed_follow_up_status_change( $previous_status, $transaction->payment_status ) ) {
            $transaction->payment_status = $previous_status;
            $transaction->payer_email    = $previous_payer_email;
            $transaction->errors         = $previous_errors;
        }
    }

    /**
     * Whether the posted IPN is about the payment that was already verified for the transaction.
     *
     * @since x.x
     *
     * @param AWPCP_Payment_Transaction $transaction     The payment transaction.
     * @param string                    $original_txn_id The PayPal transaction ID of the verified payment.
     *
     * @return bool
     */
    private function notification_belongs_to_payment( $transaction, $original_txn_id ) {
        $custom        = awpcp_get_var( array( 'param' => 'custom' ), 'post' );
        $txn_id        = awpcp_get_var( array( 'param' => 'txn_id' ), 'post' );
        $parent_txn_id = awpcp_get_var( array( 'param' => 'parent_txn_id' ), 'post' );

        if ( empty( $original_txn_id ) || strcasecmp( (string) $custom, (string) $transaction->id ) !== 0 ) {
            return false;
        }

        return in_array( (string) $original_txn_id, array( (string) $txn_id, (string) $parent_txn_id ), true );
    }

    /**
     * Whether a follow-up IPN may change the payment status from one value to the other.
     *
     * @since x.x
     *
     * @param string $previous_status The payment status before the notification.
     * @param string $new_status      The payment status after the notification.
     *
     * @return bool
     */
    private function is_allowed_follow_up_status_change( $previous_status, $new_status ) {
        $allowed_changes = array(
            AWPCP_Payment_Transaction::PAYMENT_STATUS_PENDING   => array(
                AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED,
                AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED,
            ),
            AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED => array(
                AWPCP_Payment_Transaction::PAYMENT_STATUS_FAILED,
            ),
        );

        if ( ! isset( $allowed_changes[ $previous_status ] ) ) {
            return false;
        }

        return in_array( $new_status, $allowed_changes[ $previous_status ], true );
    }

    public function process_payment_canceled( $transaction ) {
        $transaction->errors[]       = __( 'The payment transaction was canceled by the user.', 'another-wordpress-classifieds-plugin' );
        $transaction->payment_status = AWPCP_Payment_Transaction::PAYMENT_STATUS_CANCELED;
    }
}
