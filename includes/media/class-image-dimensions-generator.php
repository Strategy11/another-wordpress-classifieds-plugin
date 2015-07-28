<?php

function awpcp_image_dimensions_generator() {
    return new AWPCP_Image_Dimensions_Generator( awpcp_media_api() );
}

class AWPCP_Image_Dimensions_Generator {

    private $media;

    public function __construct( $media ) {
        $this->media = $media;
    }

    public function set_image_dimensions( $image ) {
        $image_dimensions = $this->calculate_image_dimensions( $image );

        $this->media->set_metadata( $image, 'image-dimensions', $image_dimensions );
        $this->media->save( $image );
    }

    private function calculate_image_dimensions( $image ) {
        $image_dimensions = array();

        $targets = array(
            'original' => $image->get_original_file_path(),
            'large' => $image->get_large_image_path(),
            'primary' => $image->get_primary_thumbnail_path(),
            'thumbnail' => $image->get_thumbnail_path(),
        );

        foreach ( $targets as $image_type => $image_path ) {
            $editor = wp_get_image_editor( $image_path );

            if ( is_wp_error( $editor ) ) {
                continue;
            }

            $image_dimensions[ $image_type ] = $editor->get_size();
        }

        return $image_dimensions;
    }

    public function get_image_dimensions( $image ) {
        return $this->media->get_metadata( $image, 'image-dimensions' );
    }
}
