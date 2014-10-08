<?php

function awpcp_image_file_handler() {
    return new AWPCP_ImageFileHandler(
        awpcp_image_file_validator(),
        awpcp_image_file_processor(),
        awpcp()->settings
    );
}

class AWPCP_ImageFileHandler {

    private $validator;
    private $processor;
    private $settings;

    public function __construct( $validator, $processor, $settings ) {
        $this->validator = $validator;
        $this->processor = $processor;
        $this->settings = $settings;
    }

    public function can_handle( $file ) {
        return $file->is_image();
    }

    public function handle_file( $listing_id, $file ) {
        $this->validator->validate_file( $listing_id, $file );
        $this->move_file_to( $file, 'images' );
        $this->processor->process_file( $listing_id, $file );

        return $file;
    }

    private function move_file_to( $file, $relative_path ) {
        $uploads_dir = $this->settings->get_runtime_option( 'awpcp-uploads-dir' );
        $destination_dir = implode( DIRECTORY_SEPARATOR, array( $uploads_dir, $relative_path ) );

        if ( ! file_exists( $destination_dir ) && ! mkdir( $destination_dir, awpcp_directory_permissions(), true ) ) {
            throw new AWPCP_Exception( __( "Destination directory doesn't exists and couldn't be created.", 'AWPCP' ) );
        }

        $unique_filename = wp_unique_filename( $destination_dir, $file->get_real_name() );
        $destination_path = implode( DIRECTORY_SEPARATOR, array( $destination_dir, $unique_filename ) );

        if ( rename( $file->get_path(), $destination_path ) ) {
            $file->set_path( $destination_path );
            chmod( $destination_path, 0644 );
        } else {
            unlink( $file->get_path() );

            $message = _x( 'The file %s could not be copied to the destination directory.', 'upload files', 'AWPCP' );
            $message = sprintf( $message, $file->get_real_name() );

            throw new AWPCP_Exception( $message );
        }

        return $file;
    }
}
