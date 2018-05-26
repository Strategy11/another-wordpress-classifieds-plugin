<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Exporter for Payment personal data.
 */
class AWPCP_PaymentPersonalDataExporter implements AWPCP_PersonalDataExporterInterface {

    /**
     * @var
     */
    private $data_formatter;

    /**
     * @since 3.8.6
     */
    public function __construct( $data_formatter ) {
        $this->data_formatter = $data_formatter;
    }

    /**
     * @since 3.8.6
     */
    public function get_page_size() {
        return 10;
    }

    /**
     * @since 3.8.6
     */
    public function get_objects( $user, $email_address, $page ) {
        if ( ! is_object( $user ) ) {
            return array();
        }

        return AWPCP_Payment_Transaction::query( array( 'user_id' => $user->ID ) );
    }

    /**
     * @since 3.8.6
     */
    public function export_objects( $users ) {
        $items = array(
            'ID'          => __( 'Payment Transaction', 'another-wordpress-classifieds-plugin' ),
            'payer_email' => __( 'Payer Email', 'another-wordpress-classifieds-plugin' ),
        );

        $export_items = array();

        foreach ( $payment_transactions as $payment_transaction ) {
            $data = $this->data_formatter->format_data( $items, $this->get_payment_transaction_properties( $payment_transaction ) );

            $export_items[] = array(
                'group_id' =>  'awpcp-payments',
                'group_label' => __( 'Classifieds Payment Information', 'another-wordpress-classifieds-plugin' ),
                'item_id'     => "awpcp-payment-transaction-{$payment_transaction->id}",
                'data'        => $data,
            );
        }

        return $export_items;
    }

    /**
     * @since 3.8.6
     */
    private function get_payment_transaction_properties( $payment_transaction ) {
        return array(
            'ID'          => $payment_transaction->id,
            'payer_email' => $payment_transaction->payer_email,
        );
    }
}

