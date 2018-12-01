<?php
/**
 * @package AWPCP\Templates
 */

if ( isset( $transaction ) && get_awpcp_option( 'show-create-listing-form-steps' ) ) {
    echo awpcp_render_listing_form_steps( 'finish', $transaction ); // XSS Ok.
}

?><?php foreach ( (array) $messages as $message ) : ?>
    <?php echo awpcp_print_message( $message ); // XSS Ok. ?>
<?php endforeach; ?>

<?php
// TODO: Make sure the menu is not shown.
// TODO: ContentRenderer should be available as a parameter for this view.
echo awpcp()->container['ListingsContentRenderer']->render_content_without_notices( apply_filters( 'the_content', $ad->post_content ), $ad ); // XSS Ok.
