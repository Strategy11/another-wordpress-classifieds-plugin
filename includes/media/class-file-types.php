<?php

function awpcp_file_types() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_FileTypes( awpcp()->settings );
    }

    return $instance;
}

class AWPCP_FileTypes {

    private $file_types = null;

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function get_file_types() {
        if ( is_null( $this->file_types ) ) {
            $this->file_types = apply_filters( 'awpcp-file-types', $this->get_default_file_types() );
        }

        return $this->file_types;
    }

    private function get_default_file_types() {
        return array(
            'image' => array(
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
            )
        );
    }

    public function get_file_types_in_group( $group ) {
        return awpcp_array_data( $group, array(), $this->get_file_types() );
    }

    public function get_video_mime_types() {
        return $this->get_mime_types( $this->get_file_types_in_group( 'video' ) );
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
        return $this->get_allowed_mime_types( $this->get_file_types_in_group( 'video' ), $this->get_allowed_video_extensions() );
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
        return array_keys( $this->get_file_types_in_group( 'video' ) );
    }

    public function get_allowed_video_extensions() {
        return $this->settings->get_option( 'attachments-allowed-video-extensions', array() );
    }

    public function get_other_files_mime_types() {
        return $this->get_mime_types( $this->get_file_types_in_group( 'others' ) );
    }

    public function get_other_allowed_files_mime_types() {
        return $this->get_allowed_mime_types( $this->get_file_types_in_group( 'others' ), $this->get_other_allowed_files_extensions() );
    }

    public function get_other_files_extensions() {
        return array_keys( $this->get_file_types_in_group( 'others' ) );
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
