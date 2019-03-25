<?php
/**
 * @package AWPCP\Admin\Listings
 */

?>
<div class="postbox">
    <strong><?php esc_html_e( 'Links:', 'another-wordpress-classifieds-plugin' ); ?></strong>
    <a href="<?php echo esc_url( $edit_listing_url ); ?>"><?php echo esc_html( $edit_listing_link_text ); ?></a> |
    <a href="<?php echo esc_url( $listings_url ); ?>"><?php esc_html_e( 'Return to Listings', 'another-wordpress-classifieds-plugin' ); ?></a>
</div>

<div class="postbox"><?php echo $content; // XSS: Ok. ?></div>
