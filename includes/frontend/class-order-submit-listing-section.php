<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Order section for the Submit Listing page.
 */
class AWPCP_OrderSubmitListingSection {

    use AWPCP_SubmitListingSectionTrait;

    /**
     * @var string
     */
    private $template = 'frontend/order-submit-listing-section.tpl.php';

    /**
     * @var ListingsAPI
     */
    private $listings_logic;

    /**
     * @var ListingRenderer
     */
    private $listing_renderer;

    /**
     * @var object
     */
    private $payments;

    /**
     * @var object
     */
    private $roles;

    /**
     * @var CAPTCHA
     */
    private $captcha;

    /**
     * @var object
     */
    private $template_renderer;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @since 4.0.0
     */
    public function __construct( $payments, $listings_logic, $listing_renderer, $roles, $captcha, $template_renderer, $settings ) {
        $this->payments          = $payments;
        $this->listings_logic    = $listings_logic;
        $this->listing_renderer  = $listing_renderer;
        $this->roles             = $roles;
        $this->captcha           = $captcha;
        $this->template_renderer = $template_renderer;
        $this->settings          = $settings;
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
     * See AWPCP_OrderSubmitListingSectionTest::test_get_state_returns_preview().
     *
     * @since 4.0.0
     */
    public function get_state( $listing ) {
        if ( is_null( $listing ) ) {
            return 'edit';
        }

        if ( ! $this->listings_logic->can_payment_information_be_modified_during_submit( $listing ) ) {
            return 'read';
        }

        $payment_term = $this->listing_renderer->get_payment_term( $listing );

        if ( $payment_term ) {
            return 'preview';
        }

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
     * TODO: Lock this section for regular users that just completed payment.
     *
     * @since 4.0.0
     */
    public function render( $listing ) {
        $messages = [];

        if ( awpcp_current_user_is_admin() ) {
            $messages[] = __( 'You are logged in as an administrator. Any payment steps will be skipped.', 'another-wordpress-classifieds-plugin' );
        }

        $stored_data = $this->get_stored_data( $listing );
        $nonce       = $this->maybe_generate_nonce( $listing );

        $payment_terms = $this->payments->get_payment_terms();
        $payment_terms = apply_filters( 'awpcp_submit_listing_payment_terms', $payment_terms, $listing );

        $params = array(
            'transaction'               => null,

            'payment_terms'             => $payment_terms,
            'form'                      => $stored_data,
            'nonce'                     => $nonce,

            'form_errors'               => [],

            'show_user_field'           => $this->roles->current_user_is_moderator(),
            'show_account_balance'      => ! $this->roles->current_user_is_administrator(),
            'show_captcha'              => $this->should_show_captcha( $listing ),
            'disable_parent_categories' => $this->settings->get_option( 'noadsinparentcat' ),
            'account_balance'           => '',
            'payment_terms_list'        => $this->render_payment_terms_list( $stored_data, $payment_terms ),
            'credit_plans_table'        => $this->payments->render_credit_plans_table( null ),
            'captcha'                   => $this->captcha,
        );

        if ( $params['show_account_balance'] ) {
            $params['show_account_balance'] = true;
            $params['account_balance']      = $this->payments->render_account_balance();
        }

        return $this->template_renderer->render_template( $this->template, $params );
    }

    /**
     * @since 4.0.0
     */
    private function get_stored_data( $listing ) {
        if ( is_null( $listing ) ) {
            return [
                'listing_id'                => null,
                'category'                  => null,
                'user'                      => null,
                'payment_term_id'           => null,
                'payment_term_type'         => null,
                'payment_term_payment_type' => null,
            ];
        }

        $payment_term = $this->listing_renderer->get_payment_term( $listing );

        return [
            'listing_id'                => $listing->ID,
            'category'                  => $this->listing_renderer->get_categories_ids( $listing ),
            'user'                      => $listing->post_author,
            'payment_term_id'           => $payment_term->id,
            'payment_term_type'         => $payment_term->type,
            'payment_term_payment_type' => 'money', // TODO: Get actual payment type from listing metadata?
        ];
    }

    /**
     * @since 4.0.0
     */
    private function maybe_generate_nonce( $listing ) {
        if ( $this->can_payment_information_be_modified_during_submit( $listing ) ) {
            return wp_create_nonce( 'awpcp-create-empty-listing' );
        }

        return '';
    }

    /**
     * @since 4.0.0
     */
    private function should_show_captcha( $listing ) {
        if ( ! $this->captcha->is_captcha_required() ) {
            return false;
        }

        return $this->can_payment_information_be_modified_during_submit( $listing );
    }

    /**
     * TODO: Update Payment Term List to work using stored data.
     *
     * @since 4.0.0
     */
    private function render_payment_terms_list( $data, $payment_terms ) {
        $payment_terms_list = awpcp_payment_terms_list();

        return $payment_terms_list->render(
            [
                'payment_term' => (object) [
                    'id'   => $data['payment_term_id'],
                    'type' => $data['payment_term_type'],
                ],
                'payment_type' => $data['payment_term_payment_type'],
            ],
            [
                'payment_terms' => $payment_terms,
            ]
        );
    }
}
