<?php
/**
 * @package AWPCP\Templates\Frontend\SubmitListingPage
 */

if ( get_awpcp_option( 'show-create-listing-form-steps' ) ) {
    awpcp_listing_form_steps_componponent()->show( $current_step, compact( 'transaction' ) );
}

?><form class="awpcp-submit-listing-page-form"></form><script type="text/javascript">var AWPCPSubmitListingPageData = <?php echo wp_json_encode( $page_data ); ?>;</script>
