<?php

function awpcp_image_file_handler() {
    return new AWPCP_ImageFileHandler(
        awpcp_listing_image_file_validator(),
        awpcp_image_file_processor(),
        awpcp()->settings
    );
}

class AWPCP_ImageFileHandler extends AWPCP_ListingFileHandler {

    public function __construct( $validator, $processor, $settings ) {
        $this->validator = $validator;
        $this->processor = $processor;
        $this->settings = $settings;
    }

    public function can_handle( $file ) {
        return in_array( $file->get_mime_type(), $this->settings->get_runtime_option( 'image-mime-types' ) );
    }

    protected function move_file( $file ) {
        $this->move_file_to( $file, 'images' );
    }
}
