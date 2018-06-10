<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Edit Listing Page.
 */
class AWPCP_EditListingPage extends AWPCP_Page {

    /**
     * @var bool
     */
    public $show_menu_items = false;

    /**
     * @var WP_Post
     */
    private $ad;

    /**
     * @since 4.0.0
     */
    public function __construct( $sections_generator, $listings, $request ) {
        parent::__construct( null, null, awpcp()->container['TemplateRenderer'] );

        $this->sections_generator = $sections_generator;
        $this->listings           = $listings;
        $this->request            = $request;
    }

    /**
     * @since 4.0.0
     */
    public function dispatch() {
        try {
            return $this->do_current_step();
        } catch ( AWPCP_Exception $e ) {
            return $this->render( 'content', $e->getMessage() );
        }
    }

    /**
     * @since 4.0.0
     */
    private function do_current_step() {
        $step = $this->get_current_step();

        switch ( $step ) {
            case 'listings-information':
                return $this->do_listing_information_step();
            case 'finish':
                return $this->do_finish_step();
            default:
                return $this->do_listing_information_step();
        }
    }

    /**
     * @since 4.0.0
     */
    private function get_current_step() {
        return $this->request->post( 'step', $this->request->param( 'step' ) );
    }

    /**
     * @since 4.0.0
     */
    private function do_listing_information_step() {
        wp_enqueue_script( 'awpcp-submit-listing-page' );

        $listing = $this->get_ad();

        $sections = $this->sections_generator->get_sections( $listing );

        return $this->render( 'content', '<form class="awpcp-submit-listing-page-form"></form><script type="text/javascript">var AWPCPSubmitListingPageSections = ' . wp_json_encode( $sections ) . ';</script>' );
    }

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function do_finish_step() {
        $ad       = $this->get_ad();
        $messages = [];

        if ( is_null( $ad ) ) {
            $message = __( 'The specified Ad doesn\'t exists.', 'another-wordpress-classifieds-plugin' );
            return $this->render( 'content', awpcp_print_error( $message ) );
        }

        awpcp_listings_api()->consolidate_existing_ad( $ad );

        if ( is_admin() ) {
            /* translators: %s is the URL to the listing's individual page. */
            $message = __( 'The Ad has been edited successfully. <a href="%s">Go back to view listings</a>.', 'another-wordpress-classifieds-plugin' );

            if ( awpcp_currency_symbols() ) {
                $url = awpcp_get_admin_listings_url();
            } else {
                $url = awpcp_get_user_panel_url();
            }

            $messages[] = sprintf( $message, esc_url( $url ) );
        }

        $template = AWPCP_DIR . '/frontend/templates/page-place-ad-finish-step.tpl.php';
        $params   = array(
            'messages' => array_merge( $messages, awpcp_listings_api()->get_ad_alerts( $ad ) ),
            'edit'     => true,
            'ad'       => $ad,
        );

        return $this->render( $template, $params );
    }

    /**
     * @since 4.0.0
     */
    public function get_ad() {
        if ( is_null( $this->ad ) ) {
            try {
                $this->ad = $this->listings->get( $this->get_listing_id() );
            } catch ( AWPCP_Exception $e ) {
                $this->ad = null;
            }
        }

        return $this->ad;
    }

    /**
     * TODO: Can't we use Request's method?
     *
     * @since 4.0.0
     */
    private function get_listing_id() {
        return $this->request->param( 'ad_id', $this->request->param( 'id', $this->request->get_query_var( 'id' ) ) );
    }
}
