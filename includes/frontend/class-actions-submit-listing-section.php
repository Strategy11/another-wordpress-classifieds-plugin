<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Actions section for the Submit Listing page.
 */
class AWPCP_ActionsSubmitListingSection {

    use AWPCP_SubmitListingSectionTrait;

    /**
     * @var string
     */
    private $template = 'frontend/actions-submit-listing-section.tpl.php';

    /**
     * @var ListingsLogic
     */
    private $listings_logic;

    /**
     * @var TemplateRenderer
     */
    private $template_renderer;

    /**
     * @since 4.0.0
     */
    public function __construct( $listings_logic, $template_renderer ) {
        $this->listings_logic    = $listings_logic;
        $this->template_renderer = $template_renderer;
    }

    /**
     * @since 4.0.0
     */
    public function get_id() {
        return 'actions';
    }

    /**
     * @since 4.0.0
     */
    public function get_position() {
        return 0;
    }

    /**
     * @since 4.0.0
     */
    public function get_state( $listing ) {
        if ( $this->can_payment_information_be_modified_during_submit( $listing ) ) {
            return 'edit';
        }

        return 'read';
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
        $params = [
            'listing_actions' => awpcp_listing_actions_component()->render( $listing ),
        ];

        return $this->template_renderer->render_template( $this->template, $params );
    }
}
