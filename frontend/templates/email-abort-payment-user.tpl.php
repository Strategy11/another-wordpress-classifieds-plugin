<?php // emails are sent in plain text, trailing whitespace are required for proper formatting ?>
<?php echo get_awpcp_option('paymentabortedbodymessage') ?>

<?php esc_html_e( 'Additional Details', 'another-wordpress-classifieds-plugin' ); ?>

<?php printf( "\t%s", $message ); ?>

<?php if ($transaction): ?>
<?php esc_html_e( 'Payment transaction ID', 'another-wordpress-classifieds-plugin' ); ?>: <?php echo esc_html( $transaction->id ); ?>
<?php endif ?>

<?php echo esc_html( awpcp_get_blog_name() ); ?>
<?php echo esc_url( home_url() ); ?>
