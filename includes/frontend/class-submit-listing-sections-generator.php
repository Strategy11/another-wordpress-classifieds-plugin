<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Takes an array of Submit Listing Section instances and generates a data
 * representation that can be converted into JSON.
 */
class AWPCP_SubmitLisitngSectionsGenerator {

    /**
     * @var array
     */
    private $sections = array();

    /**
     * @since 4.0.0
     */
    public function get_sections( $listing = null, $sections_ids = [] ) {
        $this->sections = [
            'order'          => new AWPCP_OrderSubmitListingSection(
                awpcp()->container['Payments'],
                awpcp()->container['ListingsLogic'],
                awpcp()->container['ListingRenderer'],
                awpcp()->container['RolesAndCapabilities'],
                awpcp()->container['TemplateRenderer']
            ),
            'listing-dates'  => new AWPCP_ListingDatesSubmitListingSection(
                awpcp()->container['ListingDateFormFieldsRenderer'],
                awpcp()->container['FormFieldsData'],
                awpcp()->container['ListingAuthorization'],
                awpcp()->container['TemplateRenderer']
            ),
            'listing-fields' => new AWPCP_ListingFieldsSubmitListingSection(
                awpcp()->container['ListingDetailsFormFieldsRenderer'],
                awpcp()->container['FormFieldsData'],
                awpcp()->container['TemplateRenderer']
            ),
            'upload-media'   => new AWPCP_UploadMediaSubmitListingSection(
                awpcp()->container['AttachmentsCollection'],
                awpcp()->container['ListingUploadLimits'],
                awpcp()->container['RolesAndCapabilities'],
                awpcp()->container['TemplateRenderer'],
                awpcp()->container['Settings']
            ),
            'save'           => new AWPCP_SaveSubmitListingSection(
                awpcp()->container['TemplateRenderer']
            ),
        ];

        $this->sections = apply_filters( 'awpcp_submit_listing_sections', $this->sections );

        $sections = [];

        foreach ( $this->sections as $section_id => $section ) {
            if ( ! empty( $sections_ids ) && ! in_array( $section_id, $sections_ids, true ) ) {
                continue;
            }

            $sections[ $section_id ] = [
                'id'       => $section_id,
                'position' => $section->get_position(),
                'state'    => $section->get_state( $listing ),
                'template' => $section->render( $listing ),
            ];

            $section->enqueue_scripts();
        }

        uasort( $sections, function( $section_a, $section_b ) {
            return $section_a['position'] - $section_b['position'];
        } );

        return $sections;
    }
}
