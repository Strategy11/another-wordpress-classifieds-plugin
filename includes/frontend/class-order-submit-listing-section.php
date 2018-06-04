<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Order section for the Submit Listing page.
 */
class AWPCP_OrderSubmitListingSection {

    /**
     * @var string
     */
    private $template = 'frontend/order-submit-listing-section.tpl.php';

    /**
     * @var object
     */
    private $payments;

    /**
     * @var object
     */
    private $roles;

    /**
     * @var object
     */
    private $template_renderer;

    /**
     * @param object $payments              An instance of Payments API.
     * @param object $roles                 An instance of Roles And Capabilities.
     * @param object $template_renderer     An instance of Template Renderer.
     * @since 4.0.0
     */
    public function __construct( $payments, $roles, $template_renderer ) {
        $this->payments          = $payments;
        $this->roles             = $roles;
        $this->template_renderer = $template_renderer;
    }

    /**
     * @since 4.0.0
     */
    public function get_id() {
        return 'order';
    }

    /**
     * @since 4.0.0
     */
    public function get_position() {
        return 5;
    }

    /**
     * @since 4.0.0
     */
    public function get_state() {
        return 'edit';
    }

    /**
     * @since 4.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'select2' );
        wp_enqueue_script( 'select2' );
    }

    /**
     * @since 4.0.0
     */
    public function render() {
        $messages = [];

        if ( awpcp_current_user_is_admin() ) {
            $messages[] = __( 'You are logged in as an administrator. Any payment steps will be skipped.', 'another-wordpress-classifieds-plugin' );
        }

        $params = array(
            'transaction'          => null,

            'payment_terms'        => $this->payments->get_payment_terms(),
            'form'                 => $this->get_stored_data(),

            'form_errors'          => [],

            'show_user_field'      => $this->roles->current_user_is_moderator(),
            'show_account_balance' => ! $this->roles->current_user_is_administrator(),
            'account_balance'      => '',
            'payment_terms_list'   => $this->render_payment_terms_list(),
            'credit_plans_table'   => $this->payments->render_credit_plans_table( null ),
        );

        // TODO: Fix always-true condition.
        if ( true || $params['show_account_balance'] ) {
            $params['show_account_balance'] = true;
            $params['account_balance']      = $this->payments->render_account_balance();
        }

        return $this->template_renderer->render_template( $this->template, $params );
    }

    /**
     * TODO: Actually get stored data.
     *
     * @since 4.0.0
     */
    private function get_stored_data() {
        return [
            'category' => null,
            'user'     => null,
        ];
    }

    /**
     * TODO: Update Payment Term List to work using stored data.
     *
     * @since 4.0.0
     */
    private function render_payment_terms_list() {
        $payment_terms_list = awpcp_payment_terms_list();

        return $payment_terms_list->render( [
            'payment_term' => null,
            'payment_type' => null,
        ] );
    }
}
