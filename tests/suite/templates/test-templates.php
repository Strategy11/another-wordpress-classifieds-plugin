<?php
/**
 * @package AWPCP\Tests\Templates
 */

// phpcs:disable

class AWPCP_TemplateTester {

    private $methods = array();
    private $variables = array();

    public function register_method( $method, $return_value ) {
        $this->methods[ $method ] = $return_value;
    }

    public function register_variable( $variable, $value ) {
        $this->variables[ $variable ] = $value;
    }

    public function include_template( $template_file ) {
        extract( $this->variables );

        ob_start();
        include( $template_file );
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * This function DOES NOT ignore compile-time parse errors (E_PARSE).
     */
    public function include_template_but_ignore_notices_and_warnings( $template_file ) {
        try {
            return $this->include_template( $template_file );
        } catch ( PHPUnit_Framework_Error_Notice $e ) {
            echo " Notice: " . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . " ";
        } catch( PHPUnit_Framework_Error_Warning $e ) {
            echo " Warning: " . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . " ";
        } catch ( PHPUnit_Framework_Error $e ) {
            echo " Error: " . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . " ";
        }
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function __call( $method, $args ) {
        if ( isset( $this->methods[ $method ] ) ) {
            return $this->methods[ $method ];
        } else {
            trigger_error( "Call to undefined method {$method}().", E_USER_ERROR );
        }
    }
}

/**
 * Before the AWPCP 3.3 release, many templates were modified to fix
 * a security vulnerability and prevent other of the same nature. The
 * modifications caused some templates to break, mostly because of syntax
 * errors. This class will attempt to include every template and make sure
 * it can be interpreted by PHP without errors.
 *
 * The logic in every template will not be tested here, sadly!.
 *
 * @SuppressWarnings(PHPMD)
 */
class AWPCP_TestTemplates extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();
        $this->tester = new AWPCP_TemplateTester();
    }

    public function test_admin_page_template() {
        $this->tester->register_variable( 'page_slug', 'some-page' );
        $this->tester->register_variable( 'page_title', 'Page Title' );
        $this->tester->register_variable( 'should_show_title', true );
        $this->tester->register_variable( 'show_sidebar', true );
        $this->tester->register_variable( 'content', 'Page Content' );

        $this->tester->include_template( 'another-wordpress-classifieds-plugin/admin/templates/admin-page.tpl.php' );
    }

    public function test_admin_credit_plans_entry_form_template() {
        $_POST['action'] = 'action';

        $plan = Phake::mock( 'AWPCP_CreditPlan' );
        $plan->credits = rand();
        $plan->price = rand();

        $this->tester->register_variable( 'entry', $plan );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/admin/templates/admin-panel-credit-plans-entry-form.tpl.php' );
    }

    public function test_admin_credit_plans_template() {
        $this->tester->params = array();
        $this->tester->register_method( 'url', 'http://somewhere.nice' );
        $this->tester->register_variable( 'page', Phake::mock( 'AWPCP_CreditPlansAdminPage' ) );
        $this->tester->register_variable( 'option', 'option-name' );
        $this->tester->register_variable( 'table', Phake::mock( 'WP_List_Table' ) );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/admin/templates/admin-panel-credit-plans.tpl.php' );
    }

    public function test_import_listings_admin_page_upload_files_form_template() {
        $this->tester->register_variable( 'form_data', array(
            'images_source' => '',
            'local_path' => '',
        ) );
        $this->tester->register_variable( 'form_errors', array() );

        $this->tester->include_template( AWPCP_DIR . '/templates/admin/import-listings-admin-page-upload-files-form.tpl.php' );
    }

    public function test_import_listings_admin_page_configuration_form_template() {
        $this->tester->register_variable( 'form_data', array(
                'define_default_dates' => null,
                'default_start_date' => null,
                'default_end_date' => null,
                'date_format' => null,
                'time_separator' => null,
                'date_separator' => null,
                'images_separator' => null,
                'create_missing_categories' => null,
                'assign_listings_to_user' => null,
                'define_default_user' => null,
                'default_user' => null,
        ) );
        $this->tester->register_variable( 'form_errors', array() );

        $this->tester->include_template( AWPCP_DIR . '/templates/admin/import-listings-admin-page-configuration-form.tpl.php' );
    }

    public function test_import_listings_admin_page_import_form_template() {
        $this->tester->register_variable( 'action_name', null );
        $this->tester->register_variable( 'test_mode_enabled', null );
        $this->tester->include_template( AWPCP_DIR . '/templates/admin/import-listings-admin-page-import-form.tpl.php' );
    }

    // Uncomment me!
    // public function test_admin_debug_template() {
    //     $this->tester->register_variable( 'download', false );
    //     $this->tester->register_variable( 'page_id', 'page-id' );
    //     $this->tester->register_variable( 'page_title', 'Page Title' );
    //     $this->tester->register_variable( 'pages', array() );
    //     $this->tester->register_variable( 'options', array() );
    //     $this->tester->register_variable( 'rules', array() );
    //     $this->tester->include_template( 'another-wordpress-classifieds-plugin/admin/templates/admin-panel-debug.tpl.php' );
    // }

    public function test_admin_delete_fee_template() {
        $this->tester->register_method( 'url', 'htt://somewhere.nice' );
        $this->tester->register_variable( 'fee', Phake::mock( 'AWPCP_Fee' ) );
        $this->tester->register_variable( 'fees', array() );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/admin/templates/admin-panel-fees-delete.tpl.php' );
    }

    public function test_place_ad_order_step_template() {
        $payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );
        $payment_terms_list = Phake::mock( 'AWPCP_Payment_Terms_List' );

        $this->tester->register_variable( 'messages', array() );
        $this->tester->register_variable( 'transaction_errors', array() );
        $this->tester->register_variable( 'form_errors', array() );
        $this->tester->register_variable( 'skip_payment_term_selection', false );
        $this->tester->register_variable( 'payments', $payments );
        $this->tester->register_variable( 'payment_options', array() );
        $this->tester->register_variable( 'payment_terms', array() );
        $this->tester->register_variable( 'payment_terms_list', $payment_terms_list );
        $this->tester->register_variable( 'form', array() );
        $this->tester->register_variable( 'transaction', $transaction );

        $this->tester->include_template( 'another-wordpress-classifieds-plugin/frontend/templates/page-place-ad-order-step.tpl.php' );
    }

    public function test_google_checkout_checkout_form_template() {
        $item = new stdClass();
        $item->name = 'Item Name';

        $this->tester->register_variable( 'google_checkout_url', 'http://somewhere.nice' );
        $this->tester->register_variable( 'item', $item );
        $this->tester->register_variable( 'amount', 10.5 );
        $this->tester->register_variable( 'currency', 'USD' );
        $this->tester->register_variable( 'return_url', 'http://home.sweet.home' );
        $this->tester->register_variable( 'text', 'whatever' );
        $this->tester->register_variable( 'key', 'a-key' );
        $this->tester->register_variable( 'button_url', 'http://path.to.button' );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/frontend/templates/checkout-form-google-checkout.tpl.php' );
    }

    public function test_abort_payment_admin_email_template() {
        $user = Phake::mock( 'WP_User' );
        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );

        $this->tester->register_variable( 'message', 'something important!' );
        $this->tester->register_variable( 'user', $user );
        $this->tester->register_variable( 'transaction', $transaction );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/frontend/templates/email-abort-payment-admin.tpl.php' );
    }

    public function test_abort_payment_user_email_template() {
        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );

        $this->tester->register_variable( 'message', 'something important!' );
        $this->tester->register_variable( 'transaction', $transaction );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/frontend/templates/email-abort-payment-user.tpl.php' );
    }

    public function test_payment_terms_table_template() {
        $item = Phake::mock( 'AWPCP_PaymentTerm' );

        $this->tester->register_method( 'get_columns', array( 'credits' => 'Credits' ) );
        $this->tester->register_method( 'get_items', array( $item ) );
        $this->tester->register_method( 'item_group', 'item-group' );
        $this->tester->register_method( 'item_group_name', 'Item Group' );
        $this->tester->register_method( 'item_attributes', '' );
        $this->tester->register_method( 'item_column', 'column-value' );

        $this->tester->include_template( 'another-wordpress-classifieds-plugin/frontend/templates/payments-payment-terms-table.tpl.php' );
    }

    public function test_payments_transactions_items_table_template() {
        $transaction = Phake::mock( 'AWPCP_Payment_Transaction' );

        Phake::when( $transaction )->get_items()->thenReturn( array() );

        $this->tester->register_variable( 'transaction', $transaction );
        $this->tester->register_variable( 'show_credits', true );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/frontend/templates/payments-transaction-items-table.tpl.php' );
    }

    /**
     * @medium
     */
    public function test_templates() {
        $this->tester->register_variable( 'uuid', 'uuid' );
        $this->tester->register_variable( 'errors', array() );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/frontend/templates/html-widget-multiple-region-selector.tpl.php' );

        $args = array( 'label' => 'Label', 'id' => 'field-id', 'required' => true, 'name' => 'field-name', 'class' => array( 'css', 'class' ) );
        $this->tester->register_variable( 'args', $args );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/frontend/templates/html-widget-users-autocomplete.tpl.php' );

        $this->tester->register_variable( 'params', array() );
        $this->tester->register_variable( 'items', array() );
        $this->tester->register_variable( 'options', array( 1, 2, 3 ) );
        $this->tester->register_variable( 'results', 3 );
        $this->tester->include_template( 'another-wordpress-classifieds-plugin/frontend/templates/listings-pagination.tpl.php' );

        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/login-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/main-menu.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-buy-credits-final-step.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-buy-credits-select-credit-plan-step.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-edit-ad-email-key-step.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-edit-ad-send-access-key-step.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-place-ad-details-step.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-place-ad-preview-step.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-place-ad-upload-fields.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-place-ad-upload-images-step.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-renew-ad-order-step.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-reply-to-ad.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page-search-ads.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/page.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/payments-2checkout-payment-button.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/payments-billing-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/payments-credit-plans-table.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/payments-payment-methods-table.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/payments-paypal-payment-button.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/widget-categories-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/widget-latest-ads-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'another-wordpress-classifieds-plugin/frontend/templates/widget-search-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-attachments/frontend/templates/page-place-ad-upload-files-step.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-attachments/frontend/templates/placeholder-attachments.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-comments-ratings/admin/templates/comment-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-comments-ratings/frontend/templates/comments-delete-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-comments-ratings/frontend/templates/comments-list-item.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-comments-ratings/frontend/templates/comments-post-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-coupons/admin/templates/admin-panel-coupons.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-coupons/admin/templates/coupons-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-google-checkout/templates/payments-google-checkout-payment-button.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-paypal-pro/templates/payments-express-checkout-checkout-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-region-control/templates/region-control-form-fields.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-region-control/templates/region-control-selector.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-restricted-categories/templates/user-disclaimer.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-subscriptions/admin/templates/dashboard-widget.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-subscriptions/admin/templates/subscriptions-create-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-subscriptions/admin/templates/subscriptions-entries.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-subscriptions/admin/templates/subscriptions-entry.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-subscriptions/admin/templates/subscriptions-form.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-subscriptions/admin/templates/subscriptions-plans-entries.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-subscriptions/admin/templates/subscriptions-plans-entry.tpl.php' );
        $this->tester->include_template_but_ignore_notices_and_warnings( 'premium-modules/awpcp-subscriptions/admin/templates/subscriptions-plans-form.tpl.php' );
    }
}
