<?php // emails are sent in plain text, all blank lines in templates are required ?>
<?php echo $introduction; ?>: 

<?php _e( 'Total ads found sharing your email address', 'another-wordpress-classifieds-plugin' ); ?>: <?php echo count( $ads ); ?> 

<?php foreach ( $ads as $ad ): ?>
<?php echo $listing_renderer->get_listing_title( $ad ); ?> 
<?php _e( 'Access Key', 'another-wordpress-classifieds-plugin' ); ?>: <?php echo $listing_renderer->get_access_key( $ad ); ?> 
 
<?php endforeach; ?>

<?php echo awpcp_get_blog_name(); ?> 
<?php echo home_url(); ?> 
