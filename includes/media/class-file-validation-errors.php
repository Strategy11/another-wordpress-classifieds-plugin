<?php

function awpcp_file_validation_errors() {
    return new AWPCP_FileValidationErrors();
}

class AWPCP_FileValidationErrors {

    public function get_file_is_too_large_error_message() {
        return __( 'The file <filename> was larger than the maximum allowed file size of <bytes-count> bytes. The file cannot be uploaded.', 'AWPCP' );
    }
}
