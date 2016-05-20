<?php

function awpcp_upload_listing_media_ajax_handler() {
    return new AWPCP_UploadListingMediaAjaxHandler(
        awpcp_listings_collection(),
        awpcp_file_uploader(),
        awpcp_new_media_manager(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_UploadListingMediaAjaxHandler extends AWPCP_AjaxHandler {

    private $listings;
    private $uploader;
    private $media_manager;
    private $request;

    public function __construct( $listings, $uploader, $media_manager, $request, $response ) {
        parent::__construct( $response );

        $this->listings = $listings;
        $this->media_manager = $media_manager;
        $this->uploader = $uploader;
        $this->request = $request;
    }

    public function ajax() {
        try {
            $this->try_to_process_uploaded_file();
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->get_errors() );
        }
    }

    private function try_to_process_uploaded_file() {
        $listing = $this->listings->get( $this->request->post( 'listing' ) );

        if ( ! $this->is_user_authorized_to_upload_media_to_listing( $listing ) ) {
            throw new AWPCP_Exception( __( 'You are not authorized to upload files.', 'another-wordpress-classifieds-plugin' ) );
        }

        return $this->process_uploaded_file( $listing );
    }

    private function is_user_authorized_to_upload_media_to_listing( $listing ) {
        if ( ! wp_verify_nonce( $this->request->post( 'nonce' ), 'awpcp-upload-media-for-listing-' . $listing->ad_id ) ) {
            return false;
        }

        // TODO: complete me!

        return true;
    }

    private function process_uploaded_file( $listing ) {
        $uploaded_file = $this->uploader->get_uploaded_file();

        if ( $uploaded_file->is_complete ) {
            $file = $this->media_manager->add_file( $listing, $uploaded_file );
            $notification_sent = $this->maybe_send_notification( $file->ad_id );
            do_action( 'awpcp-media-uploaded', $file, $listing );

            return $this->success( array(
                'file' => array(
                    'id' => $file->id,
                    'name' => $file->name,
                    'listingId' => $file->ad_id,
                    'enabled' => $file->enabled,
                    'status' => $file->status,
                    'mimeType' => $file->mime_type,
                    'isPrimary' => $file->is_primary(),
                    'thumbnailUrl' => $file->get_url( 'thumbnail' ),
                    'iconUrl' => $file->get_icon_url(),
                    'url' => $file->get_url(),
                    'notification_sent' => $notification_sent,
                ),
            ) );
        } else {
            return $this->success();
        }
    }

    private function maybe_send_notification( $listing_id ){
        $send_notification_to_administrators = get_awpcp_option( 'send-images-uploaded-notification-to-administrators' );
        if( $send_notification_to_administrators ){
            $content = 'Test Content';

            $admin_message = new AWPCP_Email;
            $admin_message->to = array( awpcp_admin_email_to() );
            $admin_message->subject = __( 'New Image uploaded to listing', 'another-wordpress-classifieds-plugin' );

            $params = array('page' => 'awpcp-listings',  'action' => 'view', 'id' => $listing_id);
            $url = add_query_arg( urlencode_deep( $params ), admin_url( 'admin.php' ) );

            $template = AWPCP_DIR . '/frontend/templates/email-place-ad-success-admin.tpl.php';
            $admin_message->prepare($template, compact('content', 'url'));

            $message_sent = $admin_message->send();

            return $message_sent;
        }else{
            return false;
        }

    }
}
