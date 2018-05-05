<?php
/**
 * @package AWPCP\FormFields
 */

/**
 * Datepicker field
 */
class AWPCP_ListingDatePickerFormField extends AWPCP_FormField {

    /**
     * @var string
     */
    private $label;

    /**
     * @var object
     */
    private $template_renderer;

    /**
     * @param string $slug                  An identifier for the form field.
     * @param string $label                 A label for the form field.
     * @param object $template_renderer     An instance of Template Renderer.
     * @since 4.0.0
     */
    public function __construct( $slug, $label, $template_renderer ) {
        parent::__construct( $slug );

        $this->label             = $label;
        $this->template_renderer = $template_renderer;
    }

    /**
     * @since 4.0.0
     */
    public function get_label() {
        return $this->label;
    }

    /**
     * @since 4.0.0
     */
    public function get_name() {
        return $this->get_slug();
    }

    /**
     * @param mixed  $value     The value for this form field.
     * @param array  $errors    An array of form field errors index by field slug.
     * @param object $listing   An instance of WP_Post.
     * @param array  $context   Information about the context where the form field
     *                          is being rendered.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render( $value, $errors, $listing, $context ) {
        $params = array(
            'required'        => false,
            'value'           => awpcp_datetime( 'Y/m/d', $value ),
            'formatted_value' => awpcp_datetime( 'awpcp-date', $value ),
            'errors'          => $errors,

            'label'           => $this->get_label(),

            'html'            => array(
                'id'   => $this->get_slug(),
                'name' => $this->get_name(),
            ),
        );

        return $this->template_renderer->render_template( 'frontend/form-fields/listing-datepicker-form-field.tpl.php', $params );
    }
}
