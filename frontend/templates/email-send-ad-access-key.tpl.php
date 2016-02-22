<?php echo sprintf( __( 'Hello %s,', 'another-wordpress-classifieds-plugin' ), $contact_name ); ?> 
 
<?php $message = __('Below you will find the access key for your Ad "%s" associated to the email address %s.', 'another-wordpress-classifieds-plugin'); ?>
<?php echo sprintf( $message, $listing_title, $contact_email ); ?> 

<?php _e( 'Access Key', 'another-wordpress-classifieds-plugin' ); ?>: <?php echo $access_key; ?> 
 
<?php echo awpcp_get_blog_name(); ?> 
<?php echo home_url(); ?> 
