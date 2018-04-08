<?php
/**
 * @package AWPCP\FormFields
 */

/**
 * Class used to retrieve data posted through listing's form fields.
 */
class AWPCP_FormFieldsData {

    /**
     * @var object
     */
    private $listing_renderer;

    /**
     * @var object
     */
    private $request;

    /**
     * @param object $listing_renderer  An instance of Listing Renderer.
     * @param object $request           An instance of Request.
     * @since 4.0.0
     */
    public function __construct( $listing_renderer, $request ) {
        $this->listing_renderer = $listing_renderer;
        $this->request          = $request;
    }

    /**
     * A replacement for PagePlaceAd::get_ad_info().
     *
     * @param object $post  An instance of WP_Post.
     * @since 4.0.0
     */
    public function get_stored_data( $post ) {
        $data = array(
            'ad_id'            => $post->ID,
            'user_id'          => $post->post_author,
            'ad_key'           => $this->listing_renderer->get_access_key( $post ),
            'ad_title'         => $this->listing_renderer->get_listing_title( $post ),
            'ad_details'       => $post->post_content,
            'ad_startdate'     => $this->listing_renderer->get_plain_start_date( $post ),
            'ad_enddate'       => $this->listing_renderer->get_plain_end_date( $post ),
            'ad_contact_name'  => $this->listing_renderer->get_contact_name( $post ),
            'ad_contact_phone' => $this->listing_renderer->get_contact_phone( $post ),
            'ad_contact_email' => $this->listing_renderer->get_contact_email( $post ),
            'websiteurl'       => $this->listing_renderer->get_website_url( $post ),
            'ad_item_price'    => $this->listing_renderer->get_price( $post ),
            'ad_category_id'   => $this->listing_renderer->get_category_id( $post ),
            'categories'       => $this->listing_renderer->get_categories_ids( $post ),
            'regions'          => $this->listing_renderer->get_regions( $post ),
        );

        $data['ad_category'] = $data['categories'];
        $data['start_date']  = $data['ad_startdate'];
        $data['end_date']    = $data['ad_enddate'];

        // Listing prices have been historically stored in cents, so we have to
        // devide them by 100.
        $data['ad_item_price'] = $data['ad_item_price'] / 100;

        return apply_filters( 'awpcp_form_fields_stored_data', $data, 'details' );
    }

    /**
     * @since 4.0.0
     */
    public function get_posted_data() {
        $defaults = $this->get_default_values();
        $data     = array();

        foreach ( $defaults as $name => $default ) {
            $value = $this->request->param( $name, $default );

            if ( 'ad_details' !== $name ) {
                $value = awpcp_strip_all_tags_deep( $value );
            }

            $data[ $name ] = $value;
        }

        $data['ad_title']   = str_replace( array( "\r", "\n" ), '', $data['ad_title'] );
        $data['ad_details'] = str_replace( "\r", '', $data['ad_details'] );
        $data['websiteurl'] = awpcp_maybe_add_http_to_url( $data['websiteurl'] );

        // Parse the value provided by the user and convert it to a float value.
        $data['ad_item_price'] = awpcp_parse_money( $data['ad_item_price'] );
        $data['ad_item_price'] = 100 * $data['ad_item_price'];

        $data['is_featured_ad'] = absint( $data['is_featured_ad'] );

        // phpcs:disable WordPress.NamingConventions.ValidHookName.UseUnderscores
        // TODO: We no longer pass an array that filters can use to extract data from.
        return apply_filters( 'awpcp-get-posted-data', $this->translate_data( $data ), 'details', null );
        // phpcs:enable
    }

    /**
     * Return default values for standard fields.
     */
    private function get_default_values() {
        return array(
            'start_date'       => '',
            'end_date'         => '',

            'ad_id'            => '',
            'adterm_id'        => '',
            'ad_title'         => '',
            'ad_contact_name'  => '',
            'ad_contact_phone' => '',
            'ad_contact_email' => '',
            'websiteurl'       => '',
            'ad_item_price'    => '',
            'ad_details'       => '',
            'ad_payment_term'  => '',
            'is_featured_ad'   => '',

            'regions'          => array(),
        );
    }

    /**
     * Convert pre-4.0 fields into the new format that uses post attribuets and
     * metadata.
     *
     * @param array $data   Array of data using old index names.
     * @since 4.0.0
     */
    private function translate_data( $data ) {
        return array(
            'ID'          => $data['ad_id'],
            'post_fields' => array(
                'post_title'   => $data['ad_title'],
                'post_content' => $data['ad_details'],
            ),
            'regions'     => $data['regions'],
            'metadata'    => array(
                '_awpcp_start_date'      => $data['start_date'],
                '_awpcp_end_date'        => $data['end_date'],
                '_awpcp_contact_name'    => $data['ad_contact_name'],
                '_awpcp_contact_phone'   => $data['ad_contact_phone'],
                '_awpcp_contact_email'   => $data['ad_contact_email'],
                '_awpcp_website_url'     => $data['websiteurl'],
                '_awpcp_price'           => $data['ad_item_price'],
                '_awpcp_payment_term'    => $data['ad_payment_term'],
                '_awpcp_payment_term_id' => $data['adterm_id'],
                // TODO: Make sure to move this to the Featured Listings module.
                '_awpcp_is_featured'     => $data['is_featured_ad'],
            ),
        );
    }
}
