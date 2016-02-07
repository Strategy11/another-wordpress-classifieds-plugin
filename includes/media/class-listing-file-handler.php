<?php

function awpcp_image_file_handler() {
    return new AWPCP_ListingFileHandler(
        awpcp_image_file_validator(),
        awpcp_image_file_processor(),
        awpcp_image_attachment_creator()
    );
}

class AWPCP_ListingFileHandler {

    private $validator;
    private $processor;
    private $creator;

    public function __construct( $validator, $processor, $creator ) {
        $this->validator = $validator;
        $this->processor = $processor;
        $this->creator = $creator;
    }

    public function handle_file( $listing, $file ) {
        $this->validator->validate_file( $listing, $file );
        $this->processor->process_file( $listing, $file );

        return $this->creator->create_attachment( $listing, $file );
    }
}
