<?php

function awpcp_image_constraints() {
    return new AWPCP_ImageConstraints( awpcp()->settings );
}

class AWPCP_ImageConstraints {

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    public function get_allowed_mime_types() {
        return $this->settings->get_runtime_option( 'image-mime-types' );
    }

    public function get_max_file_size() {
        return $this->settings->get_option( 'maximagesize' );
    }

    public function get_min_file_size() {
        return $this->settings->get_option( 'minimagesize' );
    }

    public function get_min_image_width() {
        return $this->settings->get_option( 'imgminwidth' );
    }

    public function get_min_image_height() {
        return $this->settings->get_option( 'imgminheight' );
    }
}
