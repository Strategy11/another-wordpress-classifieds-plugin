<?php
/**
 * @package AWPCP\Templates\Frontend\SubmitListingPage
 */

if ( get_awpcp_option( 'show-create-listing-form-steps' ) ) {
    echo awpcp_render_listing_form_steps( 'listing-details', $transaction ); // XSS Ok.
}

?><form class="awpcp-submit-listing-page-form"></form><script type="text/javascript">var AWPCPSubmitListingPageSections = <?php echo wp_json_encode( $sections ); ?>;</script>
