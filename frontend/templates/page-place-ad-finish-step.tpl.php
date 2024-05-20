<?php
/**
 * @package AWPCP\Templates
 */

if ( isset( $transaction ) && get_awpcp_option( 'show-create-listing-form-steps' ) ) {
    awpcp_listing_form_steps_componponent()->show( 'finish', compact( 'transaction' ) );
}

?><?php foreach ( (array) $messages as $message ) : ?>
    <?php echo awpcp_print_message( $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php endforeach; ?>

<?php
// TODO: ContentRenderer or the rendered content itself should be available as a parameter for this view.
awpcp()->container['ListingsContentRenderer']->show_content_without_notices(
    apply_filters( 'the_content', $ad->post_content ),
    $ad
);
