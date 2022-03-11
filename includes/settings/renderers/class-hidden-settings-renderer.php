<?php
/**
 * @package AWPCP\Settings\Renderers
 */

/**
 * @since x.x
 */
class AWPCP_HiddenSettingsRenderer {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @since x.x
     */
    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    /**
     * @since x.x
     */
    public function render_setting( $setting ) {
        $current = esc_html( stripslashes( $this->settings->get_option( $setting['id'] ) ) );

        echo '<input type="hidden" name="awpcp-options[' . esc_attr( $setting['id'] ) . ']" value="' . esc_attr( $current ) . '" />';
    }
}
