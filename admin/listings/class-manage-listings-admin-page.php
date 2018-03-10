<?php

require_once( AWPCP_DIR . '/includes/helpers/admin-page.php' );

require_once( AWPCP_DIR . '/admin/admin-panel-listings-place-ad-page.php' );
require_once( AWPCP_DIR . '/admin/admin-panel-listings-edit-ad-page.php' );
require_once( AWPCP_DIR . '/admin/admin-panel-listings-renew-ad-page.php' );
require_once( AWPCP_DIR . '/admin/admin-panel-listings-table.php' );

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

        $actions['view'] = array(__('View', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'view', 'id' => $ad->ID)));
        $actions['edit'] = array(__('Edit', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'edit', 'id' => $ad->ID)));
        $actions['trash'] = array(__('Delete', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'delete', 'id' => $ad->ID)));

        if ( $is_moderator ) {
            if ( $this->listing_renderer->is_disabled( $ad ) ) {
                $actions['enable'] = array(__('Enable', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'enable', 'id' => $ad->ID)));
            } else {
                $actions['disable'] = array(__('Disable', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'disable', 'id' => $ad->ID)));
            }

            if ($ad->flagged)
                $actions['unflag'] = array(__('Unflag', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'unflag', 'id' => $ad->ID)));

            if (get_awpcp_option('useakismet'))
                $actions['spam'] = array('SPAM', $this->url(array('action' => 'spam', 'id' => $ad->ID)));

			$has_featured_ads = function_exists( 'awpcp_is_featured_ad' );
			if ( $has_featured_ads && awpcp_is_featured_ad( $ad->ID ) ) {
                $actions['remove-featured'] = array(__('Remove Featured', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'remove-featured', 'id' => $ad->ID)));
			} else if ( $has_featured_ads ) {
                $actions['make-featured'] = array(__('Make Featured', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'make-featured', 'id' => $ad->ID)));
			}

            $actions['send-key'] = array(__('Send Access Key', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'send-key', 'id' => $ad->ID)));
        }

        if ( $this->listing_renderer->is_about_to_expire( $ad ) || $this->listing_renderer->has_expired( $ad ) ) {
            $hash = awpcp_get_renew_ad_hash( $ad->ID );
            $params = array( 'action' => 'renew', 'id' => $ad->ID, 'awpcprah' => $hash );
            $actions['renwew-ad'] = array( __( 'Renew Ad', 'another-wordpress-classifieds-plugin' ), $this->url( $params ) );
        }

        $images = $this->attachments->count_attachments_of_type( 'image', array( 'post_parent' => $ad->ID ) );

        if ( $images ) {
            $label = __( 'Manage Images', 'another-wordpress-classifieds-plugin' );
            $url = $this->url(array('action' => 'manage-images', 'id' => $ad->ID));
            $actions['manage-images'] = array($label, array('', $url, " ($images)"));
        } else if ( $this->listing_upload_limits->are_uploads_allowed_for_listing( $ad ) ) {
            $actions['add-image'] = array(__('Add Images', 'another-wordpress-classifieds-plugin' ), $this->url(array('action' => 'add-image', 'id' => $ad->ID)));
        }

        if ( $is_moderator && ! $this->listing_renderer->is_disabled( $ad ) ) {
            $fb = AWPCP_Facebook::instance();
            if ( ! awpcp_wordpress()->get_post_meta( $ad->ID, '_awpcp_sent_to_facebook_page', true ) && $fb->get( 'page_id' ) ) {
                $actions['send-to-facebook'] = array(
                    __( 'Send to Facebook', 'another-wordpress-classifieds-plugin' ),
                    $this->url( array(
                        'action' => 'send-to-facebook',
                        'id' => $ad->ID
                    ) )
                );
            } else if ( ! awpcp_wordpress()->get_post_meta( $ad->ID, '_awpcp_sent_to_facebook_group', true ) && $fb->get( 'group_id' ) ) {
                $actions['send-to-facebook'] = array(
                    __( 'Send to Facebook Group', 'another-wordpress-classifieds-plugin' ),
                    $this->url( array(
                        'action' => 'send-to-facebook',
                        'id' => $ad->ID
                    ) )
                );
            }
        }

        $actions = apply_filters( 'awpcp-admin-listings-table-actions', $actions, $ad, $this );

        if ( $is_moderator && isset( $_REQUEST['filterby'] ) && $_REQUEST['filterby'] == 'new' ) {
            $actions['mark-reviewed'] = array(
                __( 'Mark Reviewed', 'another-wordpress-classifieds-plugin' ),
                $this->url( array( 'action' => 'mark-reviewed', 'id' => $ad->ID ) ),
            );
        }

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
            case 'view':
                return $this->listing_action( 'view_ad' );
                break;

            case 'place-ad':
                return $this->place_ad();
                break;

            case 'edit':
            case 'dopost1':
                return $this->edit_ad();
                break;

            case 'bulk-disable':
            case 'rejectad':
            case 'disable':
                return $this->disable_ad();
                break;

            case 'bulk-enable':
            case 'approvead':
            case 'enable':
                return $this->enable_ad();
                break;

            case 'unflag':
                return $this->unflag_ad();
                break;

            case 'mark-verified':
                return $this->mark_as_verified();
                break;

            case 'mark-paid':
                return $this->mark_as_paid();
                break;

            case 'bulk-renew':
            case 'renew-ad':
            case 'renew':
                return $this->renew_ad();
                break;

            case 'bulk-spam':
            case 'spam':
                return $this->mark_as_spam();
                break;

            case 'bulk-make-featured':
            case 'make-featured':
                return $this->make_featured_ad();
                break;

            case 'bulk-remove-featured':
            case 'remove-featured':
                return $this->make_non_featured_ad();
                break;

            case 'mark-reviewed':
                return $this->listing_action( 'mark_reviewed' );
                break;

            case 'send-key':
                return $this->send_access_key();
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

            case 'bulk-delete':
                return $this->delete_selected_ads();

            case 'bulk-send-to-facebook':
            case 'send-to-facebook':
                return $this->send_to_facebook();
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

    public function view_ad( $ad ) {
        $category_id = $this->listing_renderer->get_category_id( $ad );
        $category_url = $this->url( array( 'showadsfromcat_id' => $category_id ) );

        $links = $this->links(
            $this->actions(
                $ad,
                array( 'edit', 'enable', 'disable', 'spam', 'make-featured', 'remove-featured' )
            )
        );

        // TODO: Make sure the menu is not shown.
        // TODO: ContentRenderer should be available as a parameter for this view.
        $content = awpcp()->container['ListingsContentRenderer']->render( apply_filters( 'the_content', $ad->post_content ), $ad );

        $params = array(
            'page' => $this,
            'ad' => $ad,
            'listing_title' => $this->listing_renderer->get_listing_title( $ad ),
            'category_name' => $this->listing_renderer->get_category_name( $ad ),
            'category_url' => $category_url,
            'links' => $links,
            'content' => $content,
        );

        $template = AWPCP_DIR . '/templates/admin/view-listing-admin-page.tpl.php';

        return awpcp_render_template( $template, $params );
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

    public function disable_ad_action($ad) {
        if ( is_null( $ad ) ) {
            return false;
        }

        return $this->listings_logic->disable_listing( $ad );
    }

    public function disable_ad_success($n) {
        return _n( '%d Ad was disabled', '%d Ads were disabled', $n, 'another-wordpress-classifieds-plugin' );
    }

    public function disable_ad_failure($n) {
        return __( 'there was an error trying to disable %d Ads', 'another-wordpress-classifieds-plugin' );
    }

    public function disable_ad() {
        return $this->bulk_action(
            array( $this, 'disable_ad_action' ),
            array( $this, 'disable_ad_success' ),
            array( $this, 'disable_ad_failure' )
        );
    }

    /**
     * @since feature/1112  Modified to work with custom post types.
     */
    public function enable_ad_action($ad) {
        if ( is_null( $ad ) ) {
            return false;
        }

        if ( ! $this->listings_logic->enable_listing( $ad ) ) {
            return false;
        }

        $current_user = wp_get_current_user();
        $send_listing_enabled_notification = $this->settings->get_option( 'send-ad-enabled-email' );

        if ( $current_user->ID == $ad->post_author && $send_listing_enabled_notification ) {
            awpcp_ad_enabled_email( $ad );
        }

        return true;
    }

    public function enable_ad_success($n) {
        return _n( '%d was enabled', '%d Ads were enabled', $n, 'another-wordpress-classifieds-plugin' );
    }

    public function enable_ad_failure($n) {
        return __( 'there was an error trying to enable %d Ads', 'another-wordpress-classifieds-plugin' );
    }

    public function enable_ad() {
        return $this->bulk_action(
            array( $this, 'enable_ad_action' ),
            array( $this, 'enable_ad_success' ),
            array( $this, 'enable_ad_failure' )
        );
    }

    public function unflag_ad() {
        try {
            $ad = $this->listings->get( $this->id );
        } catch ( AWPCP_Exception $e ) {
            awpcp_flash( __( 'The specified listing does not exists.', 'another-wordpress-classifieds-plugin' ), array( 'error' ) );
            return $this->redirect( 'index' );
        }

        if ( $result = awpcp_listings_api()->unflag_listing( $ad ) ) {
            awpcp_flash(__('The Ad has been unflagged.', 'another-wordpress-classifieds-plugin' ));
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

    public function renew_ad() {
        $page = awpcp_renew_listings_admin_page();
        $page->dispatch();
        return $this->redirect( 'index' );
    }

    public function mark_as_spam_action($ad) {
        return !is_null( $ad ) && $ad->mark_as_spam();
    }

    public function mark_as_spam_success($n) {
        return _n('%d Ad were marked as SPAM and removed', '%d Ads were marked as SPAM and removed', $n, 'another-wordpress-classifieds-plugin' );
    }

    public function mark_as_spam_failure($n) {
        return __('there was an error trying to mark %d Ad as SPAM', 'another-wordpress-classifieds-plugin' );
    }

    public function mark_as_spam() {
        return $this->bulk_action(
            array( $this, 'mark_as_spam_action' ),
            array( $this, 'mark_as_spam_success' ),
            array( $this, 'mark_as_spam_failure' )
        );
    }

    public function make_featured_ad_action($ad) {
		return ! is_null( $ad ) && update_post_meta( $ad->ID, '_awpcp_is_featured', true );
    }

    public function make_featured_ad_success($n) {
        return _n( '%d Ad was set as fatured', '%d Ads were set as featured', $n, 'another-wordpress-classifieds-plugin' );
    }

    public function make_featured_ad_failure($n) {
        return __( 'there was an error trying to set %d Ads as featured', 'another-wordpress-classifieds-plugin' );
    }

    public function make_featured_ad() {
        return $this->bulk_action(
            array( $this, 'make_featured_ad_action' ),
            array( $this, 'make_featured_ad_success' ),
            array( $this, 'make_featured_ad_failure' )
        );
    }

    public function make_non_featured_ad_action($ad) {
		return ! is_null( $ad ) && delete_post_meta( $ad->ID, '_awpcp_is_featured' );
    }

    public function make_non_featured_ad_success($n) {
        return _n( '%d Ad was set as non-fatured', '%d Ads were set as non-featured', $n, 'another-wordpress-classifieds-plugin' );
    }

    public function make_non_featured_ad_failure($n) {
        return __( 'there was an error trying to set %d Ads as non-featured', 'another-wordpress-classifieds-plugin' );
    }

    public function make_non_featured_ad() {
        return $this->bulk_action(
            array( $this, 'make_non_featured_ad_action' ),
            array( $this, 'make_non_featured_ad_success' ),
            array( $this, 'make_non_featured_ad_failure' )
        );
    }

    public function mark_reviewed( $listing ) {
		if ( awpcp_wordpress()->update_post_meta( $listing->ID, '_awpcp_reviewed', true ) ) {
            awpcp_flash( sprintf( __( 'The listing was marked as reviewed.', 'another-wordpress-classifieds-plugin' ), esc_html( $recipient ) ) );
        } else {
            awpcp_flash( sprintf( __( "The listing couldn't marked as reviewed.", 'another-wordpress-classifieds-plugin' ), esc_html( $recipient ) ) );
        }
        return $this->redirect( 'index' );
    }

    public function send_access_key() {
        global $nameofsite;

        try {
            $ad = $this->listings->get( $this->id );
        } catch ( AWPCP_Exception $e ) {
            return;
        }

        $listing_title = $this->listing_renderer->get_listing_title( $ad );
        $contact_name = $this->listing_renderer->get_contact_name( $ad );
        $contact_email = $this->listing_renderer->get_contact_email( $ad );

        $recipient = awpcp_format_recipient_address( $contact_email, $contact_name );
        $template = AWPCP_DIR . '/frontend/templates/email-send-ad-access-key.tpl.php';

        $message = new AWPCP_Email;
        $message->to[] = $recipient;
        $message->subject = sprintf( __( 'Access Key for "%s"', 'another-wordpress-classifieds-plugin' ), $listing_title );

        $message->prepare($template,  array(
            'listing_title' => $listing_title,
            'contact_name' => $contact_name,
            'contact_email' => $contact_email,
            'access_key' => $this->listing_renderer->get_access_key( $ad ),
            'edit_link' => awpcp_get_edit_listing_url_with_access_key( $ad ),
        ));

        if ($message->send()) {
            awpcp_flash(sprintf(__('The access key was sent to %s.', 'another-wordpress-classifieds-plugin' ), esc_html($recipient)));
        } else {
            awpcp_flash(sprintf(__('There was an error trying to send the email to %s.', 'another-wordpress-classifieds-plugin' ), esc_html($recipient)));
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

    public function send_to_facebook() {
        $page = awpcp_send_listing_to_facebook_admin_page();
        $page->dispatch();
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
