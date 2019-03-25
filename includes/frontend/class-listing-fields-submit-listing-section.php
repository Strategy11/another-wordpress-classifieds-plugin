<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Listing Fields submit listing section.
 */
class AWPCP_ListingFieldsSubmitListingSection {

    /**
     * @var string
     */
    private $template = 'frontend/listing-fields-submit-listing-section.tpl.php';

    /**
     * @var object
     */
    private $form_fields;

    /**
     * @var object
     */
    private $form_fields_data;

    /**
     * @var UsersCollection
     */
    private $users;

    /**
     * @var object
     */
    private $template_renderer;

    /**
     * @since 4.0.0
     */
    public function __construct( $form_fields, $form_fields_data, $users, $template_renderer ) {
        $this->form_fields       = $form_fields;
        $this->form_fields_data  = $form_fields_data;
        $this->users             = $users;
        $this->template_renderer = $template_renderer;
    }

    /**
     * @since 4.0.0
     */
    public function get_id() {
        return 'listing-fields';
    }

    /**
     * @since 4.0.0
     */
    public function get_position() {
        return 15;
    }

    /**
     * @since 4.0.0
     */
    public function get_state( $listing ) {
        return is_null( $listing ) ? 'disabled' : 'edit';
    }

    /**
     * @since 4.0.0
     */
    public function enqueue_scripts() {
    }

    /**
     * @since 4.0.0
     */
    public function render( $listing, $transaction ) {
        if ( is_null( $listing ) ) {
            $listing = (object) [
                'ID'           => 0,
                'post_title'   => '',
                'post_content' => '',
                'post_author'  => 0,
            ];
        }

        awpcp()->js->localize( 'submit-listing-form-fields', awpcp_listing_form_fields_validation_messages() );

        $data    = $this->get_form_fields_data( $listing, $transaction );
        $errors  = array();
        $context = array(
            'category' => null,
            'action'   => 'normal',
        );

        $params = array(
            'form_fields' => $this->form_fields->render_fields( $data, $errors, $listing, $context ),
            'nonces'      => $this->maybe_generate_nonces( $listing ),
        );

        return $this->template_renderer->render_template( $this->template, $params );
    }

    /**
     * @since 4.0.0
     */
    private function get_form_fields_data( $listing, $transaction ) {
        $data = $this->form_fields_data->get_stored_data( $listing );

        if ( empty( $transaction->user_id ) ) {
            return $data;
        }

        foreach ( $this->get_user_data( $transaction->user_id ) as $field => $value ) {
            if ( empty( $data[ $field ] ) ) {
                $data[ $field ] = $value;
            }
        }

        return $data;
    }

    /**
     * Gets user information from user meta and the profile fields for Classifieds
     * Contact Information.
     *
     * @since 4.0.0
     */
    private function get_user_data( $user_id ) {
        $user_properties = [
            'ID',
            'user_login',
            'user_email',
            'user_url',
            'display_name',
            'public_name',
            'first_name',
            'last_name',
            'nickname',
            'awpcp-profile',
        ];

        $user = $this->users->find_by_id( $user_id, $user_properties );
        $data = array();

        $field_translations = [
            'ad_contact_name'   => 'public_name',
            'ad_contact_email'  => 'user_email',
            'ad_contact_phone'  => 'phone',
            'websiteurl'        => 'user_url',
            'ad_country'        => 'country',
            'ad_state'          => 'state',
            'ad_city'           => 'city',
            'ad_county_village' => 'county',
        ];

        foreach ( $field_translations as $field => $key ) {
            if ( isset( $user->$key ) && ! empty( $user->$key ) ) {
                $data[ $field ] = $user->$key;
            }
        }

        $user_region = [
            'country' => awpcp_array_data( 'ad_country', '', $data ),
            'state'   => awpcp_array_data( 'ad_state', '', $data ),
            'city'    => awpcp_array_data( 'ad_city', '', $data ),
            'county'  => awpcp_array_data( 'ad_county_village', '', $data ),
        ];
        $user_region = array_filter( $user_region, 'strlen' );

        if ( ! empty( $user_region ) ) {
            $data['regions'][] = $user_region;
        }

        // @phpcs:disable WordPress.NamingConventions.ValidHookName.UseUnderscores
        return apply_filters( 'awpcp-listing-details-user-info', $data, $user_id );
        // @phpcs:enable WordPress.NamingConventions.ValidHookName.UseUnderscores
    }

    /**
     * @since 4.0.0
     */
    public function maybe_generate_nonces( $listing ) {
        $save_listing_information  = '';
        $clear_listing_information = '';

        if ( ! is_null( $listing ) ) {
            $save_listing_information  = wp_create_nonce( "awpcp-save-listing-information-{$listing->ID}" );
            $clear_listing_information = wp_create_nonce( "awpcp-clear-listing-information-{$listing->ID}" );
        }

        return compact( 'save_listing_information', 'clear_listing_information' );
    }
}
