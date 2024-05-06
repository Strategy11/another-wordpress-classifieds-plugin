<?php
printf(
    esc_html__( 'The ad "%s" was modified. A copy of the details sent to the customer can be found below. You can follow this link %s to go to the Manage Ad Listing section to approve/reject/spam and see the full version of the Ad.', 'another-wordpress-classifieds-plugin' ),
    esc_html( $listing_title ),
    esc_url_raw( $manage_listing_url )
);
?>

<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $content;
