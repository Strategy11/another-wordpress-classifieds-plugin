<?php if ( isset( $title ) && ! empty( $title ) ): ?>
<h2><?php echo $title; ?></h2>
<?php else: ?>
<h2><?php esc_html_e( 'Your Ad has been renewed', 'another-wordpress-classifieds-plugin' ); ?></h2>
<?php endif; ?>

<?php echo $response ?>
