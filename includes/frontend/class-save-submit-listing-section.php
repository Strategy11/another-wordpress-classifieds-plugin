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
     * @var Settings
     */
    private $settings;

    /**
     * @var TemplateRenderer
     */
    private $template_renderer;

    /**
     * @since 4.0.0
     */
    public function __construct( $settings, $template_renderer ) {
        $this->settings          = $settings;
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render( $listing, $transaction, $mode = 'create' ) {
        $labels = $this->get_button_labels( $transaction, $mode );

        return $this->template_renderer->render_template( $this->template, $labels );
    }

    /**
     * @since 4.0.0
     */
    private function get_button_labels( $transaction, $mode ) {
        if ( 'edit' === $mode ) {
            return [
                'section_label' => _x( 'Save ad', 'save submit listing section', 'another-wordpress-classifieds-plugin' ),
                'button_label'  => _x( 'Save Ad', 'save submit listing section', 'another-wordpress-classifieds-plugin' ),
            ];
        }

        if ( $this->settings->get_option( 'pay-before-place-ad' ) || is_null( $transaction ) || $transaction->payment_is_not_required() ) {
            return [
                'section_label' => _x( 'Create ad', 'save submit listing section', 'another-wordpress-classifieds-plugin' ),
                'button_label'  => _x( 'Create Ad', 'save submit listing section', 'another-wordpress-classifieds-plugin' ),
            ];
        }

        return [
            'section_label' => _x( 'Create ad', 'save submit listing section', 'another-wordpress-classifieds-plugin' ),
            'button_label'  => _x( 'Continue', 'save submit listing section', 'another-wordpress-classifieds-plugin' ),
        ];
    }
}
