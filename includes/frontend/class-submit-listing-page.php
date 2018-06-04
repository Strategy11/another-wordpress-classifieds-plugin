<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Submit Listing Page.
 */
class AWPCP_SubmitListingPage extends AWPCP_Page {

    /**
     * @var SubmitListingSectionsGenerator
     */
    private $sections_generator;

    /**
     * @since 4.0.0
     */
    public function __construct( $sections_generator ) {
        parent::__construct( null, null, awpcp()->container['TemplateRenderer'] );

        $this->sections_generator = $sections_generator;
    }

    /**
     * @since 4.0.0
     */
    public function dispatch() {
        wp_enqueue_script( 'awpcp-submit-listing-page' );

        $sections = $this->sections_generator->get_sections();

        return '<form class="awpcp-submit-listing-page-form"></form><script type="text/javascript">var AWPCPSubmitListingPageSections = ' . wp_json_encode( $sections ) . ';</script>';
    }
}
