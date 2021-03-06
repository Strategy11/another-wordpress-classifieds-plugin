<?php // emails are sent in plain text, blank lines in templates are required ?>
<?php echo $introduction ?>


<?php _e("Listing Title", 'another-wordpress-classifieds-plugin') ?>: <?php echo $listing_title; ?>

<?php _e("Listing URL", 'another-wordpress-classifieds-plugin') ?>: <?php echo urldecode( url_showad( $ad->ID ) ); ?>

<?php _e("Listing ID", 'another-wordpress-classifieds-plugin') ?>: <?php echo $ad->ID; ?>

<?php _e("Listing Edit Email", 'another-wordpress-classifieds-plugin') ?>: <?php echo $contact_email; ?>

<?php if ( get_awpcp_option( 'include-ad-access-key' ) ): ?>
<?php _e("Listing Edit Key", 'another-wordpress-classifieds-plugin') ?>: <?php echo $access_key; ?>
<?php endif; ?>

<?php _e("Listing End Date", 'another-wordpress-classifieds-plugin') ?>: <?php echo $end_date; ?>



<?php
    $text = __( 'If you have questions about your listing, please contact %s.', 'another-wordpress-classifieds-plugin' );
    echo sprintf( $text, awpcp_admin_recipient_email_address() );
?>


<?php _e('Thank you for your business', 'another-wordpress-classifieds-plugin') ?>


<?php echo home_url() ?>
