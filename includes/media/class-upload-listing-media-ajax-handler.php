<?php

function awpcp_upload_listing_media_ajax_handler() {
    return new AWPCP_UploadListingMediaAjaxHandler(
        awpcp_file_uploader(),
        awpcp_new_media_manager(),
        awpcp_request(),
        awpcp_ajax_response()
    );
}

class AWPCP_UploadListingMediaAjaxHandler extends AWPCP_AjaxHandler {

    private $uploader;
    private $media_manager;
    private $request;

    public function __construct( $uploader, $media_manager, $request, $response ) {
        parent::__construct( $response );

        $this->media_manager = $media_manager;
        $this->uploader = $uploader;
        $this->request = $request;
    }

    public function ajax() {
        $listing_id = $this->request->post( 'listing' );

        if ( ! $this->is_user_authorized_to_upload_media_to_listing( $listing_id ) ) {
            return $this->multiple_errors_response( __( 'You are not authorized to upload files.', 'AWPCP' ) );
        }

        try {
            return $this->process_uploaded_file( $listing_id );
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->get_errors() );
        }
    }

    private function is_user_authorized_to_upload_media_to_listing( $listing_id ) {
        if ( ! wp_verify_nonce( $this->request->post( 'nonce' ), 'awpcp-upload-media-for-listing-' . $listing_id ) ) {
            return false;
        }

        // TODO: complete me!

        return true;
    }

    private function process_uploaded_file( $listing_id ) {
        $uploaded_file = $this->uploader->get_uploaded_file();

        if ( $uploaded_file->is_complete ) {
            $file = $this->media_manager->add_file( $listing_id, $uploaded_file );

            return $this->success( array(
                'file' => array(
                    'id' => $file->id,
                    'name' => $file->name,
                    'listingId' => $file->ad_id,
                    'enabled' => $file->enabled,
                    'status' => $file->status,
                    'isImage' => $file->is_image(),
                    'isVideo' => $file->is_video(),
                    'isPrimary' => $file->is_primary(),
                    'thumbnailUrl' => $file->get_url( 'thumbnail' ),
                    'iconUrl' => $file->get_icon_url(),
                    'url' => $file->get_url(),
                ),
            ) );
        } else {
            return $this->success();
        }
    }

    private function forbidden( $message ) {
        header( 'HTTP/1.1 403 Forbidden' );
        return $this->error( array( 'error' => $message ) );
    }
}





