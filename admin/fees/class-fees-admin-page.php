<?php

require_once(AWPCP_DIR . '/includes/helpers/admin-page.php');
require_once(AWPCP_DIR . '/admin/admin-panel-fees-table.php');

function awpcp_fees_admin_page() {
    return new AWPCP_AdminFees( awpcp_listings_collection() );
}

/**
 * @since 2.1.4
 */
class AWPCP_AdminFees extends AWPCP_AdminPageWithTable {

    private $listings;

    public function __construct() {
        parent::__construct(
            'awpcp-admin-fees',
            awpcp_admin_page_title( __( 'Manage Listing Fees', 'AWPCP' ) ),
            __('Fees', 'AWPCP')
        );

        $this->listings = $listings;
    }

    public function enqueue_scripts() {
        wp_enqueue_script('awpcp-admin-fees');
    }

    public function get_table() {
        if (!is_null($this->table))
            return $this->table;

        $this->table = new AWPCP_FeesTable($this, array('screen' => 'classifieds_page_awpcp-admin-fees'));

        return $this->table;
    }

    public function page_url($params=array()) {
        $base = add_query_arg('page', $this->page, admin_url('admin.php'));
        return $this->url($params, $base);
    }

    public function actions($fee, $filter=false) {
        $actions = array();
        $actions['edit'] = array(__('Edit', 'AWPCP'), $this->url(array('action' => 'edit', 'id' => $fee->id)));
        $actions['trash'] = array(__('Delete', 'AWPCP'), $this->url(array('action' => 'delete', 'id' => $fee->id)));

        if (is_array($filter))
            $actions = array_intersect_key($actions, array_combine($filter, $filter));

        return $actions;
    }

    public function dispatch() {
        $this->get_table();

        $action = $this->get_current_action();

        switch ($action) {
            case 'delete':
                return $this->delete();
                break;
            case 'transfer':
                return $this->transfer();
            case 'index':
                return $this->index();
                break;
            default:
                awpcp_flash("Unknown action: $action", 'error');
                return $this->index();
                break;
        }
    }

    public function transfer() {
        $fee = AWPCP_Fee::find_by_id(awpcp_request_param('id', 0));
        if (is_null($fee)) {
            awpcp_flash(__("The specified Fee doesn't exists.", 'AWPCP'), 'error');
            return $this->index();
        }

        $recipient = AWPCP_Fee::find_by_id(awpcp_request_param('payment_term', 0));
        if (is_null($recipient)) {
            awpcp_flash(__("The selected Fee doesn't exists.", 'AWPCP'), 'error');
            return $this->index();
        }

        if (isset($_POST['transfer'])) {
            $errors = array();
            if ($fee->transfer_ads_to($recipient->id, $errors)) {
                $message = __('All Ads associated to Fee %s have been associated with Fee %s.', 'AWPCP');
                $message = sprintf($message, '<strong>' . $fee->name . '</strong>', '<strong>' . $recipient->name . '</strong>');
                awpcp_flash($message);
            } else {
                foreach ($errors as $error) awpcp_flash($error, 'error');
            }
            return $this->index();

        } else if (isset($_POST['cancel'])) {
            return $this->index();

        } else {
            $params = array('fee' => $fee, 'fees' => AWPCP_Fee::query());
            $template = AWPCP_DIR . '/admin/templates/admin-panel-fees-delete.tpl.php';
            echo $this->render($template, $params);
        }
    }

    public function delete() {
        $id = awpcp_request_param('id', 0);
        $fee = AWPCP_Fee::find_by_id($id);

        if (is_null($fee)) {
            awpcp_flash(__("The specified Fee doesn't exists.", 'AWPCP'), 'error');
            return $this->index();
        }

        $errors = array();

        if (AWPCP_Fee::delete($fee->id, $errors)) {
            awpcp_flash(__('The Fee was successfully deleted.', 'AWPCP'));
        } else {
            $ads = $this->listings->find_listings( array(
                'meta_query' => array(
                    '_awpcp_payment_term_id' => $fee->id,
                    '_awpcp_payment_term_type' => 'fee',
                ),
            ) );

            if (empty($ads)) {
                foreach ($errors as $error) awpcp_flash($error, 'error');
            } else {
                $fees = AWPCP_Fee::query();

                if (count($fees) > 1) {
                    $message = __("The Fee couldn't be deleted because there are active Ads in the system that are associated with the Fee ID. You need to switch the Ads to a different Fee before you can delete the plan.", "AWPCP");
                    awpcp_flash($message, 'error');

                    $params = array(
                        'fee' => $fee,
                        'fees' => $fees
                    );

                    $template = AWPCP_DIR . '/admin/templates/admin-panel-fees-delete.tpl.php';

                    echo $this->render($template, $params);
                    return;
                } else {
                    $message = __("The Fee couldn't be deleted because there are active Ads in the system that are associated with the Fee ID. Please create a new Fee and try the delete operation again. AWPCP will help you to switch existing Ads to the new fee.", "AWPCP");
                    awpcp_flash($message, 'error');
                }
            }
        }

        return $this->index();
    }

    public function index() {
        $this->table->prepare_items();

        $params = array(
            'page' => $this,
            'table' => $this->table,
        );

        $template = AWPCP_DIR . '/admin/templates/admin-panel-fees.tpl.php';

        return awpcp_render_template( $template, $params );
    }
}
