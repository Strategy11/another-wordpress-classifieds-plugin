<?php

function awpcp_image_file_validator() {
    return new AWPCP_ImageFileValidator(
        awpcp_listing_upload_limits(),
        awpcp_image_constraints()
    );
}

class AWPCP_ImageFileValidator {

    private $upload_limits;
    private $image_constraints;

    public function __construct( $upload_limits, $image_constraints ) {
        $this->upload_limits = $upload_limits;
        $this->image_constraints = $image_constraints;
    }

    public function validate_file( $listing_id, $file ) {
        if ( ! $this->upload_limits->can_add_file_to_listing( $listing_id, $file ) ) {
            throw new AWPCP_Exception( _x( "You can't add more images to this Ad. There are not remaining images slots.", 'upload files', 'AWPCP' ) );
        }

        if ( ! file_exists( $file->get_path() ) ) {
            $message = __( 'The file <filename> was not found in the temporary uploads directory.', 'AWPCP' );
            $this->throw_file_validation_exception( $file, $message );
        }

        if ( ! in_array( $file->get_mime_type(), $this->image_constraints->get_allowed_mime_types() ) ) {
            $message = __( 'The type of the uploaded file <filename> is not allowed.', 'AWPCP' );
            $this->throw_file_validation_exception( $file, $message );
        }

        $this->validate_file_size( $file );
        $this->validate_image_dimensions( $file );
    }

    private function throw_file_validation_exception( $file, $message ) {
        $message = str_replace( '<filename>', $file->get_real_name(), $message );
        throw new AWPCP_Exception( $message );
    }

    private function validate_file_size( $file ) {
        $filesize = filesize( $file->get_path() );

        if ( empty( $filesize ) || $filesize <= 0 ) {
            $message = _x( 'There was an error trying to find out the file size of the image %s.', 'upload files', 'AWPCP' );
            $message = sprintf( $message, '<strong>' . $file->get_real_name() . '</strong>' );
            throw new AWPCP_Exception( $message );
        }

        if ( $filesize > $this->image_constraints->get_max_file_size() ) {
            $message = _x( 'The file %s was larger than the maximum allowed file size of %s bytes. The file was not uploaded.', 'upload files', 'AWPCP' );
            $message = sprintf( $message, '<strong>' . $file->get_real_name() . '</strong>', $this->image_constraints->get_max_file_size() );
            throw new AWPCP_Exception( $message );
        }

        if ( $filesize < $this->image_constraints->get_min_file_size() ) {
            $message = __( 'The file <filename> is smaller than the minimum allowed file size of <bytes-count> bytes. The file was not uploaded.', 'AWPCP' );
            $message = str_replace( '<bytes-count>', $this->image_constraints->get_min_file_size(), $message );
            $this->throw_file_validation_exception( $file, $message );
        }
    }

    private function validate_image_dimensions( $file ) {
        $img_info = getimagesize( $file->get_path() );

        if ( ! isset( $img_info[ 0 ] ) && ! isset( $img_info[ 1 ] ) ) {
            $message = _x( 'There was an error trying to find out the dimension of <filename>. The file was not uploaded.', 'upload files', 'AWPCP' );
            $message = str_replace( '<filename>', '<strong>' . $file->get_real_name() . '</strong>' );
            throw new AWPCP_Exception( $message );
        }

        if ( $img_info[ 0 ] < $this->image_constraints->get_min_image_width() ) {
            $message = _x( 'The image %s did not meet the minimum width of %s pixels. The file was not uploaded.', 'upload files', 'AWPCP');
            $message = sprintf( $message, '<strong>' . $file->get_real_name() . '</strong>', $this->image_constraints->get_min_image_width() );
            throw new AWPCP_Exception( $message );
        }

        if ( $img_info[ 1 ] < $this->image_constraints->get_min_image_height() ) {
            $message = _x( 'The image %s did not meet the minimum height of %s pixels. The file was not uploaded.', 'upload files', 'AWPCP');
            $message = sprintf( $message, '<strong>' . $file->get_real_name() . '</strong>', $this->image_constraints->get_min_image_height() );
            throw new AWPCP_Exception( $message );
        }
    }
}
