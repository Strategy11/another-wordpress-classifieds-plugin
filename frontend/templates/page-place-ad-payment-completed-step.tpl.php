<h2><?php echo $payments->render_payment_completed_page_title($transaction) ?></h2>

<?php
    if ( isset( $transaction ) ) {
        echo awpcp_render_listing_form_steps_with_transaction( 'payment', $transaction );
    }
?>

<?php foreach ($messages as $message): ?>
    <?php echo awpcp_print_message($message) ?>
<?php endforeach ?>

<?php echo $payments->render_payment_completed_page($transaction, $url, $hidden) ?>
