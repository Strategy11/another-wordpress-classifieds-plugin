<?php
/**
 * @package AWPCP\Admin\Listings
 */

// phpcs:disable

require_once( AWPCP_DIR . '/includes/helpers/admin-page.php' );

require_once( AWPCP_DIR . '/admin/admin-panel-listings-place-ad-page.php' );
require_once( AWPCP_DIR . '/admin/admin-panel-listings-edit-ad-page.php' );
require_once( AWPCP_DIR . '/admin/admin-panel-listings-renew-ad-page.php' );
require_once( AWPCP_DIR . '/admin/admin-panel-listings-table.php' );

/**
 * @SuppressWarnings(PHPMD)
 */
function awpcp_manage_listings_admin_page() {
    return new AWPCP_Admin_Listings(
        'awpcp-admin-listings',
        awpcp_admin_page_title( __( 'Manage Listings', 'another-wordpress-classifieds-plugin' ) ),
        awpcp_attachments_collection(),
        awpcp_listings_api(),
        awpcp_listing_renderer(),
        awpcp_listings_collection(),
        awpcp_settings_api(),
        awpcp_template_renderer()
    );
}

/**
 * @since 2.1.4
 * @SuppressWarnings(PHPMD)
 */
class AWPCP_Admin_Listings extends AWPCP_AdminPageWithTable {

    private $attachments;
    private $listings_logic;
    private $listing_renderer;
    private $listings;
    private $settings;
    private $template_renderer;
    protected $listing_upload_limits;

    public function __construct( $page, $title, $attachments, $listings_logic, $listing_renderer, $listings, $settings, $template_renderer ) {
        parent::__construct( $page, $title, __('Listings', 'another-wordpress-classifieds-plugin') );

        $this->table = null;

        $this->attachments = $attachments;
        $this->listings_logic = $listings_logic;
        $this->listing_renderer = $listing_renderer;
        $this->listings = $listings;
        $this->settings = $settings;
        $this->template_renderer = $template_renderer;
        $this->listing_upload_limits = awpcp_listing_upload_limits();

        // TODO: Does awpcp-listings-delete-ad work?
    }

    public function enqueue_scripts() {
        // necessary in the Place Ad operation
        wp_enqueue_style('awpcp-frontend-style');
        wp_enqueue_script('awpcp-admin-listings');

        awpcp()->js->localize( 'admin-listings', 'delete-message', __( 'Are you sure you want to delete the selected Ads?', 'another-wordpress-classifieds-plugin' ) );
        awpcp()->js->localize( 'admin-listings', 'cancel', __( 'Cancel', 'another-wordpress-classifieds-plugin' ) );
    }

    public function show_sidebar() {
        return awpcp_current_user_is_admin();
    }

    protected function params_blacklist() {
        // we don't need all this in our URLs, do we?
        return array(
            'a', 'action2', 'action', // action and bulk actions
            'selected', // selected rows for bulk actions
            '_wpnonce',
            '_wp_http_referer'
        );
    }

    public function get_current_action($default=null) {
        $blacklist = $this->params_blacklist();

        // return current bulk-action, if one was selected
        if (!$this->action)
            $this->action = $this->get_table()->current_action();

        if (!$this->action) {
            $action = awpcp_request_param('a', 'index');
            $action = awpcp_request_param('action', $action);
            $this->action = $action;
        }

        if (!isset($this->params) || empty($this->params)) {
            wp_parse_str($_SERVER['QUERY_STRING'], $_params);
            $this->params = array_diff_key($_params, array_combine($blacklist, $blacklist));
        }

        return $this->action;
    }

    public function get_table() {
        if ( is_null( $this->table ) ) {
            $this->table = awpcp_listings_table( $this, array( 'screen' => 'classifieds_page_awpcp-admin-listings' ) );
        }
        return $this->table;
    }

    public function actions($ad, $filter=false) {
        $is_moderator = awpcp_current_user_is_moderator();

        $actions = array();

        $images = $this->attachments->count_attachments_of_type( 'image', array( 'post_parent' => $ad->ID ) );

        if ( $images ) {
            $label = __( 'Manage Images', 'another-wordpress-classifieds-plugin' );
            $url = $this->url(array('action' => 'manage-images', 'id' => $ad->ID));
            $actions['manage-images'] = array($label, array('', $url, " ($images)"));
        } else if ( $this->listing_upload_limits->are_uploads_allowed_for_listing( $ad ) ) {
            $actions['add-image'] = array(__('Add Images', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'add-image', 'id' => $ad->ID)));
        }

        $actions = apply_filters( 'awpcp-admin-listings-table-actions', $actions, $ad, $this );

        if (is_array($filter)) {
            $actions = array_intersect_key($actions, array_combine($filter, $filter));
        }

        return $actions;
    }

    public function dispatch() {
        $this->id = awpcp_request_param('id', false);
        $action = $this->get_current_action();

        $moderator_actions = array(
            'enable', 'approvead', 'bulk-enable',
            'disable', 'rejectad', 'bulk-disable',
            'remove-featured', 'bulk-remove-featured',
            'make-featured', 'bulk-make-featured',
            'mark-verified',
            'mark-paid',
            'send-key',
            'mark-reviewed',
            'bulk-renew',
            'send-to-facebook', 'bulk-send-to-facebook',
            'unflag',
            'spam', 'bulk-spam',

            'approve-file', 'reject-file',
        );

        if ( ! awpcp_current_user_is_moderator() && in_array( $action, $moderator_actions ) ) {
            awpcp_flash(_x('You do not have sufficient permissions to perform that action.', 'admin listings', 'another-wordpress-classifieds-plugin' ), 'error');
            $action = 'index';
        }

        return $this->render_page( $action );
    }

    private function render_page( $action ) {
        switch ($action) {
            case 'mark-verified':
                return $this->mark_as_verified();
                break;

            case 'mark-paid':
                return $this->mark_as_paid();
                break;

            case 'mark-reviewed':
                return $this->listing_action( 'mark_reviewed' );
                break;

            case 'add-image':
            case 'manage-images':
            case 'set-primary-image':
            case 'deletepic':
            case 'rejectpic':
            case 'approvepic':
            case 'approve-file':
            case 'reject-file':
                return $this->listing_action( 'manage_images' );
                break;

            case -1:
            case 'index':
                return $this->index();
                break;

            default:
                return $this->handle_custom_listing_actions( $action );
                break;
        }
    }

    private function listing_action( $callback ) {
        $listing_id = awpcp_request()->get_current_listing_id();

        if ( empty( $listing_id ) ) {
            awpcp_flash( __( 'No Ad ID was specified.', 'another-wordpress-classifieds-plugin' ), 'error' );
            return $this->redirect( 'index' );
        }

        try {
            $listing = awpcp_listings_collection()->get( $listing_id );
        } catch ( AWPCP_Exception $e ) {
            awpcp_flash( __( "The specified Ad doesn't exists.", 'another-wordpress-classifieds-plugin' ), 'error' );
            return $this->redirect( 'index' );
        }

        return call_user_func( array( $this, $callback ), $listing );
    }

    public function place_ad() {
        $page = awpcp_place_listing_admin_page();
        return $page->dispatch();
    }

    public function edit_ad() {
        return awpcp_edit_listing_admin_page()->dispatch('details');
    }

    protected function bulk_action($handler, $success, $failure) {
        $selected = awpcp_request_param( 'selected', array( $this->id ) );

        foreach ( array_filter( $selected ) as $id ) {
            try {
                $ad = $this->listings->get( $id );
            } catch ( AWPCP_Exception $e ) {
                $ad = null;
            }

            if ( call_user_func( $handler, $ad ) ) {
                $processed[] = $ad;
            } else {
                $failed[] = $ad;
            }
        }

        $passed = isset( $processed ) ? count( $processed ) : 0;
        $failed = isset( $failed ) ? count( $failed ) : 0;

        if ( $passed === 0 && $failed === 0 ) {
            awpcp_flash( __( 'No Ads were selected.', 'another-wordpress-classifieds-plugin' ), 'error' );
        } else {
            $message_ok = sprintf( call_user_func( $success, $passed ), $passed );
            $message_error = sprintf( call_user_func( $failure, $failed ), $failed );

            if ( $passed > 0 && $failed > 0) {
                $message = _x( '%s and %s.', 'Listing bulk operations: <message-ok> and <message-error>.', 'another-wordpress-classifieds-plugin' );
                awpcp_flash( sprintf( $message, $message_ok, $message_error ), 'error' );
            } else if ( $passed > 0 ) {
                awpcp_flash( $message_ok . '.' );
            } else if ( $failed > 0 ) {
                awpcp_flash( ucfirst( $message_error . '.' ), 'error' );
            }
        }

        return $this->redirect('index');
    }

    public function mark_as_verified() {
        try {
            $ad = $this->listings->get( $this->id );
        } catch ( AWPCP_Exception $e ) {
            awpcp_flash( __( 'The specified listing does not exists.', 'another-wordpress-classifieds-plugin' ), array( 'error' ) );
            return $this->redirect( 'index' );
        }

        awpcp_listings_api()->verify_ad( $ad );
        awpcp_flash( __( 'The Ad was marked as verified.', 'another-wordpress-classifieds-plugin' ) );

        return $this->redirect( 'index' );
    }

    public function mark_as_paid() {
        try {
            $listing = $this->listings->get( $this->id );
        } catch ( AWPCP_Exception $e ) {
            awpcp_flash( __( 'The specified listing does not exists.', 'another-wordpress-classifieds-plugin' ), array( 'error' ) );
            return $this->redirect( 'index' );
        }

        $metadata = array( '_awpcp_payment_status' => AWPCP_Payment_Transaction::PAYMENT_STATUS_COMPLETED );

        if ( $this->listings_logic->update_listing( $listing, array( 'metadata' => $metadata ) ) ) {
            awpcp_flash(__('The Ad has been marked as paid.', 'another-wordpress-classifieds-plugin' ));
        }

        return $this->redirect('index');
    }

    public function manage_images( $listing ) {
        $allowed_files = awpcp_listing_upload_limits()->get_listing_upload_limits( $listing );

        $attachments = $this->attachments->find_attachments( array( 'post_parent' => $listing->ID ) );

        $params = array(
            'listing' => $listing,
            'files' => $attachments,
            'media_manager_configuration' => array(
                'nonce' => wp_create_nonce( 'awpcp-manage-listing-media-' . $listing->ID ),
                'allowed_files' => $allowed_files,
                'show_admin_actions' => awpcp_current_user_is_moderator(),
            ),
            'media_uploader_configuration' => array(
                'listing_id' => $listing->ID,
                'context' => 'manage-media',
                'nonce' => wp_create_nonce( 'awpcp-upload-media-for-listing-' . $listing->ID ),
                'allowed_files' => $allowed_files,
            ),
            'urls' => array(
                'view-listing' => $this->url( array( 'action' => 'view', 'id' => $listing->ID ) ),
                'listings' => $this->url( array( 'id' => null ) ),
            ),
        );

        $template = AWPCP_DIR . '/templates/admin/listings-media-center.tpl.php';

        return $this->template_renderer->render_template( $template, $params );
    }

    public function delete_ad() {
        $message = deletead($this->id, '', '');
        awpcp_flash($message);

        return $this->redirect('index');
    }

    public function delete_selected_ads() {
        if (!wp_verify_nonce(awpcp_request_param('_wpnonce'), 'bulk-awpcp-listings'))
            return $this->index();

        $user = wp_get_current_user();
        $selected = awpcp_request_param('selected');

        $deleted = 0;
        $failed = 0;
        $non_existent = 0;
        $unauthorized = 0;
        $total = count( $selected );

        foreach ($selected as $id) {
            try {
                $listing = awpcp_listings_collection()->get( $id );
            } catch ( AWPCP_Exception $e ) {
                $non_existent = $non_existent + 1;
                continue;
            }

            if ( ! awpcp_listing_authorization()->is_current_user_allowed_to_edit_listing( $listing ) ) {
                $unauthorized = $unauthorized + 1;
                continue;
            }

            $errors = array();
            deletead( $id, '', '', $force=true, $errors );

            if ( empty( $errors ) ) {
                $deleted = $deleted + 1;
            } else {
                $failed = $failed + 1;
            }
        }

        if ( $deleted > 0 && $failed > 0 ) {
            awpcp_flash( sprintf( __( '%d of %d Ads were deleted. %d generated errors.', 'another-wordpress-classifieds-plugin' ), $deleted,$total, $failed ) );
        } else if ( $deleted > 0 ) {
            awpcp_flash( sprintf( __( '%d of %d Ads were deleted.', 'another-wordpress-classifieds-plugin' ), $deleted, $total ) );
        }

        if ( $non_existent > 0 ) {
            awpcp_flash( sprintf( __( "%d of %d Ads don't exist.", 'another-wordpress-classifieds-plugin' ), $non_existent, $total ), 'error' );
        }

        if ( $unauthorized > 0 ) {
            awpcp_flash( sprintf( __( "%d of %d Ads weren't deleted because you are not authorized.", 'another-wordpress-classifieds-plugin' ), $non_existent, $total ), 'error' );
        }

        return $this->redirect('index');
    }

    public function index() {
        $table = $this->get_table();
        $table->prepare_items();

        $params = array(
            'page' => $this,
            'table' => $table,
        );

        $template = AWPCP_DIR . '/admin/templates/admin-panel-listings.tpl.php';

        return awpcp_render_template( $template, $params );
    }

    private function handle_custom_listing_actions( $action ) {
        try {
            $listing = awpcp_listings_collection()->get( $this->id );
        } catch ( AWPCP_Exception $e ) {
            awpcp_flash( __( "The specified listing doesn't exists.", 'another-wordpress-classifieds-plugin' ), 'error' );
            return $this->index();
        }

        $output = apply_filters( "awpcp-custom-admin-listings-table-action-$action", null, $listing );

        if ( is_null( $output ) ) {
            awpcp_flash("Unknown action: $action", 'error');
            return $this->index();
        } else if ( is_array( $output ) && isset( $output['redirect'] ) ) {
            return $this->render_page( $output['redirect'] );
        } else {
            return $output;
        }
    }
}
