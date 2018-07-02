<?php
/**
 * @package AWPCP\Frontend
 */

/**
 * Create the CAPTCHA provider selected on the plugin settings.
 */
class AWPCP_CAPTCHAProviderFactory {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Request
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $settings, $request ) {
        $this->settings = $settings;
        $this->request  = $request;
    }

    /**
     * @since 4.0.0
     */
    public function get_captcha_provider() {
        $provider_type = $this->settings->get_option( 'captcha-provider' );

        if ( 'recaptcha' === $provider_type ) {
            return $this->get_recaptcha_provider();
        }

        return $this->get_default_captcha_provider();
    }

    /**
     * @since 4.0.0
     */
    public function get_recaptcha_provider() {
        $site_key   = $this->settings->get_option( 'recaptcha-public-key' );
        $secret_key = $this->settings->get_option( 'recaptcha-private-key' );

        return new AWPCP_reCAPTCHAProvider( $site_key, $secret_key, $this->request );
    }

    /**
     * @since 4.0.0
     */
    public function get_default_captcha_provider() {
        $max = $this->settings->get_option( 'math-captcha-max-number' );

        return new AWPCP_DefaultCAPTCHAProvider( $max );
    }
}
