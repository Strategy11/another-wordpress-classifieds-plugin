<?php

/**
 * @since 3.0.2
 */
class AWPCP_AccountBalancePage extends AWPCP_BuyCreditsPage {

    protected $template = 'admin/templates/admin-page.tpl.php';

    public $menu;

    public $page;

    public function __construct( $steps, $request ) {
        parent::__construct( $steps, $request );

        $this->page  = 'awpcp-user-account';
        $this->menu  = __( 'Account Balance', 'another-wordpress-classifieds-plugin' );
        $this->title = $this->menu;
    }

    public function enqueue_scripts() {
        wp_enqueue_style( 'awpcp-frontend-style' );
    }

    public function show_sidebar() {
        return awpcp_current_user_is_admin();
    }

    public function dispatch() {
        return parent::dispatch();
    }

    protected function render_user_not_allowed_error() {
        $this->errors[] = __( "Administrator users are not allowed to access this page. They can't add or remove credits to their accounts.", 'another-wordpress-classifieds-plugin' );
        $this->render_page_error();
    }
}

function awpcp_account_balance_page() {
    $request = new AWPCP_Request();
    $steps = awpcp_account_balance_page_steps( awpcp_payments_api() );

    return new AWPCP_AccountBalancePage( $steps, $request );
}

function awpcp_account_balance_page_steps( $payments ) {
    return array_merge(
        array(
            'summary' => new AWPCP_AccountBalancePageSummaryStep( $payments ),
        ),
        awpcp_buy_credit_page_steps( $payments )
    );
}
