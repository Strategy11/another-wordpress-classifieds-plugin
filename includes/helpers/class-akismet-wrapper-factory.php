<?php

class AWPCP_AkismetWrapperFactory {

    public function get_akismet_wrapper() {
        if ( $this->is_akismet_available() ) {
            return new AWPCP_AkismetWrapper();
        } else {
            return new AWPCP_AkismetWrapperBase();
        }
    }

    protected function is_akismet_available() {
        if ( ! class_exists( 'Akismet' ) ) {
            return false;
        }

        $api_key = Akismet::get_api_key();

        if ( empty( $api_key ) ) {
            return false;
        }

        if ( strcmp( Akismet::verify_key( $api_key ), 'valid' ) != 0 ) {
            return false;
        }

        return true;
    }
}
