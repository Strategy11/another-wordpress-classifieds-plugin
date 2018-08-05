<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Submit listing section that renders the Create Listing/Save Changes submit buttons.
 */
class AWPCP_SaveSubmitListingSection {

    /**
     * @var string
     */
    private $template = 'frontend/save-submit-listing-section.tpl.php';

    /**
     * @var TemplateRenderer
     */
    private $template_renderer;

    /**
     * @since 4.0.0
     */
    public function __construct( $template_renderer ) {
        $this->template_renderer = $template_renderer;
    }

    /**
     * @since 4.0.0
     */
    public function get_id() {
        return 'save';
    }

    /**
     * @since 4.0.0
     */
    public function get_position() {
        return 99;
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
        $section_label = _x( 'Update ad', 'save submit listing section', 'another-wordpress-classifieds-plugin' );
        $button_label  = _x( 'Update Ad', 'save submit listing section', 'another-wordpress-classifieds-plugin' );

        if ( is_null( $listing ) ) {
            $section_label = _x( 'Create ad', 'save submit listing section', 'another-wordpress-classifieds-plugin' );
            $button_label  = _x( 'Create Ad', 'save submit listing section', 'another-wordpress-classifieds-plugin' );
        }

        $params = compact( 'section_label', 'button_label' );

        return $this->template_renderer->render_template( $this->template, $params );
    }
}
