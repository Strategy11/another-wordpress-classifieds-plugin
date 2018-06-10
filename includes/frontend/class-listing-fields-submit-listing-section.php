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
     * @var object
     */
    private $template_renderer;

    /**
     * @since 4.0.0
     */
    public function __construct( $form_fields, $form_fields_data, $template_renderer ) {
        $this->form_fields       = $form_fields;
        $this->form_fields_data  = $form_fields_data;
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

        $params = array(
            'form_fields' => $this->form_fields->render_fields( $data, $errors, $listing, $context ),
        );

        return $this->template_renderer->render_template( $this->template, $params );
    }
}
