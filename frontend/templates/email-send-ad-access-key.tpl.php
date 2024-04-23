<?php
echo esc_html(
	sprintf( __( 'Hello %s,', 'another-wordpress-classifieds-plugin' ), $contact_name )
);
?>

<?php $message = __( 'Below you will find the access key for your Ad "%s" associated with the email address %s.', 'another-wordpress-classifieds-plugin'); ?>
<?php echo esc_html( sprintf( $message, $listing_title, $contact_email ) ); ?>

<?php esc_html_e( 'Access Key', 'another-wordpress-classifieds-plugin' ); ?>: <?php echo esc_html( $access_key ); ?>
<?php esc_html_e( 'Edit Link:', 'another-wordpress-classifieds-plugin' ); ?> <?php echo esc_url_raw( $edit_link ); ?>

<?php echo esc_html_x( 'The edit link will expire after 24 hours. If you use the link after it has expired, a new one will be delivered to your email address automatically.', 'edit link email', 'another-wordpress-classifieds-plugin' ); ?>

<?php echo esc_html( awpcp_get_blog_name() ); ?>
<?php echo esc_url_raw( home_url() ); ?>
