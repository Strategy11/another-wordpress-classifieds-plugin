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
                awpcp()->container['RolesAndCapabilities'],
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
        ];

        $sections = [];

        foreach ( $this->sections as $section_id => $section ) {
            if ( ! empty( $sections_ids ) && ! in_array( $section_id, $sections_ids, true ) ) {
                continue;
            }

            $sections[] = [
                'id'       => $section_id,
                'position' => $section->get_position(),
                'state'    => $section->get_state( $listing ),
                'template' => $section->render( $listing ),
            ];

            $section->enqueue_scripts();
        }

        return $sections;
    }
}
