<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Renders the start date and end date form fields.
 */
class AWPCP_ListingDatesSubmitListingSection {

    /**
     * @var string
     */
    private $template = 'frontend/listing-dates-submit-listing-section.tpl.php';

    /**
     * @var FormFieldsRenderer
     */
    private $form_fields;

    /**
     * @var FormFieldsData
     */
    private $form_fields_data;

    /**
     * @var ListingAuthorization
     */
    private $authorization;

    /**
     * @var TemplateRenderer
     */
    private $template_renderer;

    /**
     * @since 4.0.0
     */
    public function __construct( $form_fields, $form_fields_data, $authorization, $template_renderer ) {
        $this->form_fields       = $form_fields;
        $this->form_fields_data  = $form_fields_data;
        $this->authorization     = $authorization;
        $this->template_renderer = $template_renderer;
    }

    /**
     * @since 4.0.0
     */
    public function get_id() {
        return 'listing-dates';
    }

    /**
     * @since 4.0.0
     */
    public function get_position() {
        return 10;
    }

    /**
     * @since 4.0.0
     */
    public function get_state( $listing ) {
        if ( $this->is_current_user_allowed_to_edit_listing_dates( $listing ) ) {
            return 'edit';
        }

        return 'disabled';
    }

    /**
     * @since 4.0.0
     */
    public function is_current_user_allowed_to_edit_listing_dates( $listing ) {
        if ( is_null( $listing ) ) {
            return false;
        }

        if ( $this->authorization->is_current_user_allowed_to_edit_listing_end_date( $listing ) ) {
            return true;
        }

        return $this->authorization->is_current_user_allowed_to_edit_listing_start_date( $listing );
    }

    /**
     * @since 4.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'awpcp-jquery-ui' );
    }

    /**
     * TODO: Update listing's payment term and category before requesting a template update (including start and end date)
     *
     * @since 4.0.0
     */
    public function render( $listing ) {
        if ( is_null( $listing ) ) {
            $listing = (object) [
                'ID'           => 0,
                'post_title'   => '',
                'post_content' => '',
                'post_author'  => 0,
            ];
        }

        $data    = $this->form_fields_data->get_stored_data( $listing );
        $errors  = array();
        $context = array(
            'category' => null,
            'action'   => 'normal',
        );

        $params = [
            'form_fields' => $this->form_fields->render_fields( $data, $errors, $listing, $context ),
        ];

        return $this->template_renderer->render_template( $this->template, $params );
    }
}
