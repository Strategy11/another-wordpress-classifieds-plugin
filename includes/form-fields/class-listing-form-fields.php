<?php
/**
 * @package AWPCP\FormFields
 */

/**
 * Constructor function for Listing Form Fields.
 */
function awpcp_listing_form_fields() {
    return new AWPCP_ListingFormFields();
}

/**
 * Listing Form Fields defines the set of fields available for listings on the
 * Edit and Submit Listing forms.
 */
class AWPCP_ListingFormFields {

    /**
     * @param array $fields     An array of Form Fields definitions.
     */
    public function register_listing_form_fields( $fields ) {
        $fields['ad_title']         = 'awpcp_listing_title_form_field';
        $fields['websiteurl']       = 'awpcp_listing_website_form_field';
        $fields['ad_contact_name']  = 'awpcp_listing_contact_name_form_field';
        $fields['ad_contact_email'] = 'awpcp_listing_contact_email_form_field';
        $fields['ad_contact_phone'] = 'awpcp_listing_contact_phone_form_field';
        $fields['regions']          = 'awpcp_listing_regions_form_field';
        $fields['ad_item_price']    = 'awpcp_listing_price_form_field';
        $fields['ad_details']       = 'awpcp_listing_details_form_field';

        if ( is_admin() && isset( $GLOBALS['typenow'] ) && 'awpcp_listing' === $GLOBALS['typenow'] ) {
            unset( $fields['ad_title'] );
            unset( $fields['ad_details'] );
        }

        return $fields;
    }
}
