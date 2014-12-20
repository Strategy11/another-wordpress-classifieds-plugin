<?php

function awpcp_attachments_file_types() {
    return new AWPCP_AttachmentsFileTypes( awpcp()->settings );
}

/**
 * TODO: move this class into core, and allow modules to register file types.
 */
class AWPCP_AttachmentsFileTypes {

    private $file_types;

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
        $this->define_file_types();
    }

    private function define_file_types() {
        $this->image_file_types = array(
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
        );

        $this->video_file_types = array(
            'mov' => array(
                'name' => 'MOV',
                'mime_types' => array( 'video/quicktime' ),
            ),
            'mp4' => array(
                'name' => 'MP4',
                'mime_types' => array( 'video/mp4' ),
            ),
            'ogv' => array(
                'name' => 'OGV',
                'mime_types' => array(
                    'video/ogg',
                    'application/ogg'
                ),
            ),
            'webm' => array(
                'name' => 'WEBM',
                'mime_types' => array(
                    'video/webm',
                    'application/octet-stream',
                ),
            ),
            'avi' => array(
                'name' => 'AVI',
                'mime_types' => array(
                    'video/avi',
                    'video/x-msvideo',
                ),
            ),
            'asf' => array(
                'name' => 'ASF',
                'mime_types' => array(
                    'video/asf',
                    'video/x-ms-asf',
                ),
            ),
        );

        $this->other_file_types = array(
            'pdf' => array(
                'name' => 'PDF',
                'mime_types' => array( 'application/pdf', 'application/x-pdf', 'application/vnd.pdf' ),
            ),
            'rtf' => array(
                'name' => 'RTF',
                'mime_types' => array( 'application/rtf', 'application/x-rtf', 'text/richtext' ),
            ),
            'txt' => array(
                'name' => 'TXT',
                'mime_types' => array( 'text/plain' ),
            ),
        );

        $this->file_types = array_merge( $this->image_file_types, $this->video_file_types, $this->other_file_types );
    }

    public function get_video_mime_types() {
        return $this->get_mime_types( $this->video_file_types );
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
        return $this->get_allowed_mime_types( $this->video_file_types, $this->get_allowed_video_extensions() );
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
        return array_keys( $this->video_file_types );
    }

    public function get_allowed_video_extensions() {
        return $this->settings->get_option( 'attachments-allowed-video-extensions', array() );
    }

    public function get_other_files_mime_types() {
        return $this->get_mime_types( $this->other_file_types );
    }

    public function get_other_allowed_files_mime_types() {
        return $this->get_allowed_mime_types( $this->other_file_types, $this->get_other_allowed_files_extensions() );
    }

    public function get_other_files_extensions() {
        return array_keys( $this->other_file_types );
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
