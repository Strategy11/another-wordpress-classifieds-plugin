<?php
/**
 * @package AWPCP\Frontend
 */

// phpcs:disable

require_once(AWPCP_DIR . '/frontend/page-place-ad.php');

function awpcp_edit_listing_page() {
    return new AWPCP_EditAdPage(
        'awpcp-edit-ad',
        null,
        awpcp_attachments_collection(),
        awpcp_listing_upload_limits(),
        awpcp_listing_authorization(),
        awpcp_listing_renderer(),
        awpcp_listings_api(),
        awpcp_listings_collection(),
        awpcp_payments_api(),
        awpcp_template_renderer(),
        awpcp_wordpress(),
        awpcp_request()
    );
}

/**
 * @since  2.1.4
 * @SuppressWarnings(PHPMD)
 */
class AWPCP_EditAdPage extends AWPCP_Place_Ad_Page {

    protected $ad = null;

    public $active = false;
    public $messages = array();

    public function get_ad() {
        if (is_null($this->ad)) {
            try {
                $this->ad = $this->listings->get( $this->get_listing_id() );
            } catch ( AWPCP_Exception $e ) {
                $this->ad = null;
            }
        }

        return $this->ad;
    }

    private function get_listing_id() {
        return $this->request->param( 'ad_id', $this->request->param( 'id', $this->request->get_query_var( 'id' ) ) );
    }

    public function get_edit_hash($ad) {
        return wp_create_nonce("edit-ad-{$ad->ID}");
    }

    protected function request_includes_authorized_hash( $ad ) {
        return wp_verify_nonce(awpcp_request_param('edit-hash'), "edit-ad-{$ad->ID}");
    }

    protected function _dispatch($default=null) {
        if ( $this->should_redirect_user_to_ad_management_panel() ) {
            $url = admin_url('admin.php?page=awpcp-panel');
            $message = __('Please go to the Ad Management panel to edit your Ads.', 'another-wordpress-classifieds-plugin');
            $message = sprintf('%s <a href="%s">%s</a>.', $message, $url, __('Click here', 'another-wordpress-classifieds-plugin'));
            return $this->render('content', awpcp_print_message($message));
        } else {
            return $this->handle_request( $default );
        }
    }

    private function should_redirect_user_to_ad_management_panel() {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        if ( is_admin() ) {
            return false;
        }

        if ( ! get_awpcp_option( 'enable-user-panel' ) ) {
            return false;
        }

        return true;
    }

    protected function handle_request( $default_action = null ) {
        $ad = $this->get_ad();

        if ( ! is_null( $ad ) ) {
            if ( $this->is_user_allowed_to_edit( $ad ) ) {
                return $this->render_page( $this->get_current_action( 'details' ) );
            } else {
                $message = __( 'You are not allowed to edit the specified Ad.', 'another-wordpress-classifieds-plugin' );
                return $this->render( 'content', awpcp_print_error( $message ) );
            }
        } else {
            return $this->render_page( $this->get_current_action( $default_action ) );
        }
    }

    protected function render_page( $action ) {
        switch ($action) {
            case 'details':
            case 'save-details':
                return $this->details_step();
                break;
            case 'upload-images':
                return $this->upload_images_step();
                break;
            case 'delete-ad':
                return $this->delete_ad_step();
                break;
            case 'send-access-key':
                return $this->send_access_key_step();
                break;
            case 'verify-access-token':
                return $this->verify_access_token_step();
            default:
                return $this->handle_custom_listing_actions( $action );
                break;
        }
    }

    public function details_step() {
        $ad = $this->get_ad();

        if ( is_null( $ad ) ) {
            return $this->handle_missing_listing_exception();
        }

        if (strcmp($this->get_current_action(), 'save-details') === 0) {
            return $this->save_details_step();
        } else {
            return $this->details_step_form($ad, array());
        }
    }

    private function handle_missing_listing_exception() {
        $listing_id = $this->get_listing_id();

        if ( $listing_id ) {
            $message = __( 'The specified Ad doesn\'t exists.', 'another-wordpress-classifieds-plugin' );
            return $this->render( 'content', awpcp_print_error( $message ) );
        } else {
            return $this->enter_email_and_key_step();
        }
    }

    public function details_step_form($ad, $form=array(), $errors=array()) {
        $form = $this->get_posted_details( $form );
        $form = array_merge( $form, $this->get_characters_allowed( $ad->ID ) );

        $form['regions-allowed'] = $this->get_regions_allowed( $ad->ID );

        // if there are errors then the user already sent edited information,
        // and we don't need to provide defaults from Ad object
        if (empty($errors)) {
            foreach ( $this->get_ad_info( $ad->ID ) as $field => $value ) {
                $form[$field] = empty($form[$field]) ? $value : $form[$field];
            }
        }

        // overwrite user email and name using Profile information
        if ( $ad->post_author ) {
            $info = $this->get_user_info( $ad->post_author );

            $fields = array( 'ad_contact_name', 'ad_contact_email', 'ad_contact_phone' );
            foreach ($fields as $field) {
                if ( empty( $form[ $field ] ) && isset( $info[ $field ] ) && ! empty( $info[ $field ] ) ) {
                    $form[ $field ] = $info[ $field ];
                }
            }
        }

        $hidden = array('edit-hash' => $this->get_edit_hash($ad));
        $required = $this->get_required_fields();

        if ( is_admin() && $this->should_show_upload_files_step( $ad ) ) {
            $manage_attachments = __( 'Manage Attachments', 'another-wordpress-classifieds-plugin' );
            $url = add_query_arg( array( 'action' => 'manage-images', 'id' => $ad->ID ), $this->url() );
            $link = sprintf( '<strong><a href="%s" title="%s">%s</a></strong>', esc_url( $url ), esc_attr( $manage_attachments ), esc_html( $manage_attachments ) );
            $message = __( "Go to the %s section to manage the Images and Attachments for this Ad.", 'another-wordpress-classifieds-plugin');

            $this->messages[] = sprintf( $message, $link );
        }

        $payment_term = $this->listing_renderer->get_payment_term( $ad );
        $payment_terms = array( $payment_term->type => array( $payment_term ) );

        return $this->details_form(
            compact( 'payment_terms' ),
            $form,
            true,
            $hidden,
            $required,
            $errors
        );
    }

    /**
     * @param transaction   unused but required to match method
     *                          signature in parent class.
     */
    public function save_details_step($transaction=null, $errors=array()) {
        global $wpdb, $hasextrafieldsmodule;

        $ad = $this->get_ad();

        if (is_null($ad)) {
            $message = __('The specified Ad doesn\'t exists.', 'another-wordpress-classifieds-plugin');
            return $this->render('content', awpcp_print_error($message));
        }

        $data = $this->get_posted_details( $this->request->all_post_params() );
        $characters = $this->get_characters_allowed( $ad->ID );
        $errors = array();

        $payment_term = $this->listing_renderer->get_payment_term( $ad );

        if ( ! $this->validate_details( $data, true, $payment_term, $errors ) ) {
            return $this->details_step_form($ad, $data, $errors);
        }

        do_action('awpcp_before_edit_ad', $ad);

        // only admins can change the owner of an Ad
        if ( ! awpcp_current_user_is_moderator() || empty( $data['user_id'] ) ) {
            $data['user_id'] = $ad->post_author;
        }

        $current_time = current_time( 'mysql' );

        $listing_data = array(
            'post_fields' => array(
                'ID' => $ad->ID,
                'post_title' => $this->prepare_ad_title( $data['ad_title'], $characters['characters_allowed_in_title'] ),
                'post_content' => $this->prepare_ad_details( $data['ad_details'], $characters['characters_allowed'] ),
                'post_author' => $data['user_id'],
                'post_modified' => $current_time,
                'post_modified_gmt' => get_gmt_from_date( $current_time ),
            ),
            'metadata' => array(
                '_awpcp_contact_name' => $data['ad_contact_name'],
                '_awpcp_contact_phone' => $data['ad_contact_phone'],
                '_awpcp_contact_email' => $data['ad_contact_email'],
                '_awpcp_website_url' => $data['websiteurl'],
                '_awpcp_price' => $data['ad_item_price'] * 100,
            )
        );

        if ( awpcp_current_user_is_moderator() ) {
            $orginal_start_date = $this->listing_renderer->get_plain_start_date( $ad );
            $start_date = awpcp_set_datetime_date( $orginal_start_date, $data['start_date'] );
            $listing_data['metadata']['_awpcp_start_date'] = $start_date;

            $original_end_date = $this->listing_renderer->get_plain_end_date( $ad );
            $end_date = awpcp_set_datetime_date( $original_end_date, $data['end_date'] );
            $listing_data['metadata']['_awpcp_end_date'] = $end_date;
        } elseif ( $this->user_can_modify_start_date( $ad, true, false ) ) {
            $orginal_start_date = $this->listing_renderer->get_plain_start_date( $ad );
            $start_date = awpcp_set_datetime_date( $orginal_start_date, $data['start_date'] );
            $listing_data['metadata']['_awpcp_start_date'] = $start_date;

            $start_date_timestamp = awpcp_datetime( $listing_data['metadata']['_awpcp_start_date'] );
            $end_date = $payment_term->calculate_end_date( $start_date_timestamp );
            $listing_data['metadata']['_awpcp_end_date'] = $end_date;
        }

        if ( awpcp_current_user_is_moderator() && ! empty( $data['ad_category'] ) ) {
            $listing_data['terms'][AWPCP_CATEGORY_TAXONOMY] = array_filter(
                array_map( 'intval', $data['ad_category'] )
            );
        }

        if ( awpcp_current_user_is_moderator() || get_awpcp_option( 'allow-regions-modification' ) ) {
            $listing_data['regions'] = $data['regions'];
            $listing_data['regions-allowed'] = $this->get_regions_allowed( $ad->ID );
        }

        try {
            $this->listings_logic->update_listing( $ad, $listing_data );
        } catch ( AWPCP_Exception $e ) {
            $errors[] = $e->getMessage();
            return $this->details_step_form($ad, $data, $errors);
        }

        do_action('awpcp_edit_ad', $ad);

        if ( is_admin() || ! awpcp_are_images_allowed() ) {
            return $this->finish_step();
        } else {
            return $this->upload_images_step();
        }
    }

    public function upload_images_step() {
        $ad = $this->get_ad();

        if (is_null($ad)) {
            $message = __('The specified Ad doesn\'t exists. No images can be added at this time.', 'another-wordpress-classifieds-plugin');
            return $this->render('content', awpcp_print_error($message));
        }

        extract( $params = $this->get_images_config( $ad ) );

        // see if we can move to the next step
        if ( ! awpcp_are_images_allowed() ) {
            return $this->finish_step();
        } else if ( awpcp_post_param( 'submit-no-images', false ) ) {
            return $this->finish_step();
        } else if (($images_uploaded == 0 && $images_allowed == 0)) {
            return $this->finish_step();
        }

        // we are still here... let's show the upload images form

        return $this->show_upload_images_form( $ad, null, $params, array() );
    }

    /**
     * TODO: merge with the same method from Page Place Ad.
     */
    protected function show_upload_images_form( $ad, $transaction, $params, $errors ) {
        $allowed_files = awpcp_listing_upload_limits()->get_listing_upload_limits( $ad );

        $params = array_merge( $params, array(
            'hidden' => array(),
            'errors' => $errors,
            'media_manager_configuration' => array(
                'nonce' => wp_create_nonce( 'awpcp-manage-listing-media-' . $ad->ID ),
                'allowed_files' => $allowed_files,
                'show_admin_actions' => awpcp_current_user_is_moderator(),
            ),
            'media_uploader_configuration' => array(
                'listing_id' => $ad->ID,
                'context' => 'edit-listing',
                'nonce' => wp_create_nonce( 'awpcp-upload-media-for-listing-' . $ad->ID ),
                'allowed_files' => $allowed_files,
            ),
        ) );

        return $this->upload_images_form( $ad, $params );
    }

    public function upload_images_form( $ad, $params=array() ) {
        $params = array_merge( $params, array(
            'listing' => $ad,
            'files' => $this->attachments->find_attachments( array( 'post_parent' => $ad->ID ) ),
            'hidden' => array(
                'ad_id' => $ad->ID,
                'edit-hash' => $this->get_edit_hash( $ad ) ),
            'messages' => $this->messages,
            'next' => __( 'Finish', 'another-wordpress-classifieds-plugin' ),
        ) );

        $template = AWPCP_DIR . '/frontend/templates/page-place-ad-upload-images-step.tpl.php';

        return $this->render( $template, $params );
    }

    public function finish_step() {
        $ad = $this->get_ad();

        if (is_null($ad)) {
            $message = __('The specified Ad doesn\'t exists.', 'another-wordpress-classifieds-plugin');
            return $this->render('content', awpcp_print_error($message));
        }

        awpcp_listings_api()->consolidate_existing_ad( $ad );

        if (is_admin()) {
            $message = __('The Ad has been edited successfully. <a href="%s">Go back to view listings</a>.', 'another-wordpress-classifieds-plugin');

            if ( awpcp_currency_symbols() ) {
                $url = awpcp_get_admin_listings_url();
            } else {
                $url = awpcp_get_user_panel_url();
            }

            $this->messages[] = sprintf( $message, esc_url( $url) );
        }

        $template = AWPCP_DIR . '/frontend/templates/page-place-ad-finish-step.tpl.php';
        $params = array(
            'messages' => array_merge( $this->messages, awpcp_listings_api()->get_ad_alerts( $ad ) ),
            'edit' => true,
            'ad' => $ad
        );

        return $this->render($template, $params);
    }

    public function delete_ad_step() {
        $ad = $this->get_ad();

        if (is_null($ad)) {
            $message = __('The specified Ad doesn\'t exists.', 'another-wordpress-classifieds-plugin');
            return $this->render('content', awpcp_print_error($message));
        }

        if ( ! awpcp_post_param( 'confirm', false ) || ! $this->listings_logic->delete_listing( $ad ) ) {
            $this->messages[] = __('There was a problem trying to delete your Ad. The Ad was not deleted.', 'another-wordpress-classifieds-plugin');
            return $this->details_step();
        }

        if ( get_awpcp_option( 'requireuserregistration' ) ) {
            return $this->render_delete_listing_confirmation();
        } else {
            return $this->enter_email_and_key_step();
        }
    }

    private function render_delete_listing_confirmation() {
        $this->messages[] = __( 'Your Ad has been successfully deleted.', 'another-wordpress-classifieds-plugin' );
        $template = AWPCP_DIR . '/templates/frontend/edit-listing-page-delete-listing-confirmation.tpl.php';

        return $this->render( $template, array(
            'messages' => $this->messages,
            'main_page_url' => awpcp_get_main_page_url()
        ) );
    }

    private function handle_custom_listing_actions( $action ) {
        $listing = $this->get_ad();

        if ( is_null( $listing ) ) {
            return $this->handle_missing_listing_exception();
        }

        $output = apply_filters( "awpcp-custom-listing-action-$action", null, $listing );

        if ( is_null( $output ) ) {
            if ( $this->is_user_allowed_to_edit( $listing ) ) {
                return $this->details_step();
            } else {
                return $this->enter_email_and_key_step();
            }
        } else if ( is_array( $output ) && isset( $output['redirect'] ) ) {
            return $this->render_page( $output['redirect'] );
        } else {
            return $output;
        }
    }
}
