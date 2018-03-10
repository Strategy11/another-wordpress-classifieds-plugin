<?php
    if ( isset( $transaction ) && get_awpcp_option( 'show-create-listing-form-steps' ) ) {
        echo awpcp_render_listing_form_steps( 'finish', $transaction );
    }
?>

<?php if (!is_admin()): ?>
    <?php if ($edit): ?>
    <?php echo awpcp_print_message(__("Your changes have been saved.", 'another-wordpress-classifieds-plugin')); ?>
    <?php else: ?>
    <?php echo awpcp_print_message( __( 'Your listing has been submitted.', 'another-wordpress-classifieds-plugin' ) ); ?>
    <?php endif; ?>
<?php endif; ?>

<?php foreach ((array) $messages as $message): ?>
    <?php echo awpcp_print_message($message); ?>
<?php endforeach; ?>

<?php // TODO: Make sure the menu is not shown. ?>
<?php // TODO: ContentRenderer should be available as a parameter for this view. ?>
<?php echo awpcp()->container['ListingsContentRenderer']->render_content_without_notices( apply_filters( 'the_content', $ad->post_content ), $ad ); ?>
