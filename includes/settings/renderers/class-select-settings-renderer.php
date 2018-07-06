<?php
/**
 * @package AWPCP\Settings\Renderers
 */

/**
 * @since 4.0.0
 */
class AWPCP_SelectSettingsRenderer {

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
    public function render_setting( $setting ) {
        $current = esc_html( stripslashes( $this->settings->get_option( $setting['id'] ) ) );

        $html = '<select id="' . $setting['id'] . '" name="awpcp-options['. $setting['id'] .']">';

        foreach ( $setting['options'] as $value => $label ) {
            if ( $value === $current ) {
                $html .= '<option value="' . $value . '" selected="selected">' . $label . '</option>';
            } else {
                $html .= '<option value="' . $value . '">' . $label . '</option>';
            }
        }

        $html .= '</select><br/>';
        $html .= '<span class="description">' . $setting['description'] . '</span>';

        echo $html;
    }
}
