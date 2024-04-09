<h2><?php esc_html_e( 'Complete Payment', 'another-wordpress-classifieds-plugin' ); ?></h2>

<?php
    if ( isset( $transaction ) && get_awpcp_option( 'show-create-listing-form-steps' ) ) {
        if ( $transaction->is_doing_checkout() ) {
            echo awpcp_render_listing_form_steps( 'checkout', $transaction );
        } elseif ( $transaction->is_processing_payment() ) {
            echo awpcp_render_listing_form_steps( 'payment', $transaction );
        }
    }
?>

<?php foreach ($messages as $message): ?>
    <?php echo awpcp_print_message($message) ?>
<?php endforeach ?>

<?php echo $payments->render_checkout_page($transaction, $hidden) ?>
