<?php

function awpcp_file_types() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = awpcp_file_types_builder()->create_instance();
    }

    return $instance;
}

function awpcp_file_types_builder() {
    return new AWPCP_FileTypesBuilder;
}

class AWPCP_FileTypesBuilder {

    public function create_instance() {
        $instance = new AWPCP_FileTypes( awpcp()->settings );

        $instance->add_file_types( 'image', array(
            'png' => array(
                'name' => 'PNG',
                'mime_types' => array( 'image/png' ),
            ),
            'jpg' => array(
                'name' => 'JPG',
                'mime_types' => array( 'image/jpg', 'image/jpeg', 'image/pjpeg' ),
            ),
            'gif' => array(
                'name' => 'GIF',
                'mime_types' => array( 'image/gif' ),
            ),
        ) );

        do_action( 'awpcp-register-file-types', $instance );

        return $instance;
    }
}

class AWPCP_FileTypes {

    private $file_types = array();

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function add_file_types( $group, $file_types ) {
        $this->file_types[ $group ] = $file_types;
    }

    public function get_video_mime_types() {
        return $this->get_mime_types( $this->file_types['video'] );
    }

    private function get_mime_types( $file_types ) {
        $mime_types = array();

        foreach ( $file_types as $extension => $file_type ) {
            foreach ( $file_type['mime_types'] as $mime_type ) {
                $mime_types[] = $mime_type;
            }
        }

        return $mime_types;
    }

    public function get_allowed_video_mime_types() {
        return $this->get_allowed_mime_types( $this->file_types['video'], $this->get_allowed_video_extensions() );
    }

    private function get_allowed_mime_types( $file_types, $allowed_extensions ) {
        $allowed_file_types = array();

        foreach ( $file_types as $extension => $file_type ) {
            if ( in_array( $extension, $allowed_extensions ) ) {
                $allowed_file_types[] = $file_type;
            }
        }

        return $this->get_mime_types( $allowed_file_types );
    }

    public function get_video_extensions() {
        return array_keys( $this->file_types['video'] );
    }

    public function get_allowed_video_extensions() {
        return $this->settings->get_option( 'attachments-allowed-video-extensions', array() );
    }

    public function get_other_files_mime_types() {
        return $this->get_mime_types( $this->file_types['others'] );
    }

    public function get_other_allowed_files_mime_types() {
        return $this->get_allowed_mime_types( $this->file_types['others'], $this->get_other_allowed_files_extensions() );
    }

    public function get_other_files_extensions() {
        return array_keys( $this->file_types['others'] );
    }

    public function get_other_allowed_files_extensions() {
        return $this->settings->get_option( 'attachments-allowed-other-files-extensions', array() );
    }

    public function get_extension_names() {
        $extension_names = array();

        foreach ( $this->file_types as $extension => $file_type ) {
            $extension_names[ $extension ] = $file_type['name'];
        }

        return $extension_names;
    }
}
