<?php
/**
 * @package AWPCP\Frontend
 */

// phpcs:disable

class AWPCP_reCAPTCHAProvider implements AWPCP_CAPTCHAProviderInterface {

    /**
     * @var string
     */
    private $site_key;

    /**
     * @var string
     */
    private $secret_key;

    /**
     * @var Request
     */
    private $request;

    public function __construct( $site_key, $secret_key, $request ) {
        $this->site_key   = $site_key;
        $this->secret_key = $secret_key;
        $this->request    = $request;
    }

    public function render() {
        if ( empty( $this->site_key ) ) {
            return $this->missing_key_message();
        }

        wp_enqueue_script(
            'awpcp-recaptcha',
            'https://www.google.com/recaptcha/api.js?onload=AWPCPreCAPTCHAonLoadCallback&render=explicit',
            array( 'awpcp' ),
            'v2',
            true
        );

        return $this->get_recaptcha_html( $this->site_key );
    }

    private function missing_key_message() {
        $message = __( 'To use reCAPTCHA you must get an API key from %s.', 'another-wordpress-classifieds-plugin' );
        $link = sprintf( '<a href="%1$s">%1$s</a>', 'https://www.google.com/recaptcha/admin' );
        return sprintf( $message, $link );
    }

    private function get_recaptcha_html( $site_key ) {
        return '<div class="g-recaptcha awpcp-recaptcha" data-sitekey="' . esc_attr( $site_key ) . '"></div>';
    }

    public function validate() {
        if ( empty( $this->secret_key ) ) {
            throw new AWPCP_Exception( $this->missing_key_message() );
        }

        $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $this->secret_key,
                'response' => $this->request->post( 'g-recaptcha-response' ),
                $_SERVER['REMOTE_ADDR'],
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            $message = $this->get_verification_error_message( $response->get_error_message() );

            throw new AWPCP_Exception( $message );
        }

        $json = json_decode( $response['body'], true );

        if ( $json['error-codes'] ) {
            $message = $this->get_verification_error_message( $this->process_error_codes( $json['error-codes'] ) );

            throw new AWPCP_Exception( $message );
        }

        if ( ! $json['success'] ) {
            $message = __( "Your answers couldn't be verified by the reCAPTCHA server.", 'another-wordpress-classifieds-plugin' );

            throw new AWPCP_Exception( $message );
        }

        return true;
    }

    /**
     * @since 4.0.0
     */
    private function get_verification_error_message( $error ) {
        $message = __( 'There was an error trying to verify the reCAPTCHA answer. <reCAPTCHA-error>', 'another-wordpress-classifieds-plugin' );
        $message = str_replace( '<reCAPTCHA-error>', $error, $message );

        return $message;
    }

    private function process_error_codes( $error_codes ) {
        $errors = array();

        foreach ( $error_codes as $error_code ) {
            switch( $error_code ) {
                case 'missing-input-secret':
                    $errors[] = _x( 'The secret parameter is missing', 'recaptcha-error', 'another-wordpress-classifieds-plugin' );
                    break;
                case 'invalid-input-secret':
                    $errors[] = _x( 'The secret parameter is invalid or malformed.', 'recaptcha-error', 'another-wordpress-classifieds-plugin' );
                    break;
                case 'missing-input-response':
                    $errors[] = _x( 'The response parameter is missing.', 'recaptcha-error', 'another-wordpress-classifieds-plugin' );
                    break;
                case 'invalid-input-response':
                default:
                    $errors[] = _x( 'The response parameter is invalid or malformed.', 'recaptcha-error', 'another-wordpress-classifieds-plugin' );
                    break;
            }
        }

        return implode( ' ', $errors );
    }
}
