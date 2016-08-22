<?php

function awpcp_image_file_processor() {
    return new AWPCP_ImageFileProcessor( awpcp()->settings );
}

class AWPCP_ImageFileProcessor {

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function process_file( $listing, $file ) {
        $this->try_to_fix_image_rotation( $file );
    }

    private function try_to_fix_image_rotation( $file ) {
        if ( ! function_exists( 'exif_read_data' ) ) {
            return;
        }

        $exif_data = @exif_read_data( $filepath );

        $orientation = isset( $exif_data['Orientation'] ) ? $exif_data['Orientation'] : 0;
        $mime_type = isset( $exif_data['MimeType'] ) ? $exif_data['MimeType'] : '';

        $rotation_angle = 0;
        if ( 6 == $orientation ) {
            $rotation_angle = 90;
        } else if ( 3 == $orientation ) {
            $rotation_angle = 180;
        } else if ( 8 == $orientation ) {
            $rotation_angle = 270;
        }

        if ( $rotation_angle > 0 ) {
            awpcp_rotate_image( $filepath, $mime_type, $rotation_angle );
        }
    }
}
