<?php foreach ($messages as $message): ?>
<?php echo $message; ?>

<?php endforeach; ?>

<?php echo esc_html( awpcp_get_blog_name() ); ?>
<?php
echo esc_url( home_url() );
