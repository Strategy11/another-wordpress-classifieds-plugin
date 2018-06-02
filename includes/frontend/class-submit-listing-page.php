<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Submit Listing Page.
 */
class AWPCP_SubmitListingPage extends AWPCP_Page {

    /**
     * @since 4.0.0
     */
    public function __construct() {
        parent::__construct( null, null, awpcp()->container['TemplateRenderer'] );
    }

    /**
     * @since 4.0.0
     */
    public function dispatch() {
        wp_enqueue_script( 'awpcp-submit-listing-page' );

        $this->sections = [
            new AWPCP_OrderSubmitListingSection(
                awpcp()->container['Payments'],
                awpcp()->container['RolesAndCapabilities'],
                awpcp()->container['TemplateRenderer']
            ),
            new AWPCP_ListingFieldsSubmitListingSection(
                awpcp()->container['ListingDetailsFormFieldsRenderer'],
                awpcp()->container['FormFieldsData'],
                awpcp()->container['TemplateRenderer']
            ),
        ];

        $sections = [];

        foreach ( $this->sections as $section ) {
            $sections[] = [
                'id'       => $section->get_id(),
                'position' => $section->get_position(),
                'template' => $section->render(),
            ];

            $section->enqueue_scripts();
        }

        return '<form class="awpcp-submit-listing-page-form"></form><script type="text/javascript">var AWPCPSubmitListingPageSections = ' . wp_json_encode( $sections ) . ';</script>';
    }
}
