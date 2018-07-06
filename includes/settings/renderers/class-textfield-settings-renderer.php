<?php
/**
 * @package AWPCP\Settings\Renderers
 */

/**
 * @since 4.0.0
 */
class AWPCP_TextfieldSettingsRenderer {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @since 4.0.0
     */
    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    /**
     * @since 4.0.0
     */
    public function render_setting( $setting, $config ) {
        $value = esc_html( stripslashes( $this->settings->get_option( $setting['id'] ) ) );
        $type  = 'text';

        if ( 'password' === $setting['type'] ) {
            $type = 'password';
        }

        $html  = '<input id="'. $setting['id'] . '" class="regular-text" ';
        $html .= 'value="' . $value . '" type="' . $type . '" ';
        $html .= 'name="awpcp-options[' . $setting['id'] . ']" ';

        if ( ! empty( $config ) ) {
            $html .= 'awpcp-setting="' . esc_attr( json_encode( $config ) ) . '" />';
        } else {
            $html .= '/>';
        }

        $html .= strlen( $setting['description'] ) > 20 ? '<br/>' : '&nbsp;';
        $html .= '<span class="description">' . $setting['description'] . '</span>';

        echo $html;
    }
}
