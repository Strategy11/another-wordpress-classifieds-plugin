<?php
/**
 * @package AWPCP\Settings\Renderers
 */

/**
 * @since 4.0.0
 */
class AWPCP_RadioSettingsRenderer {

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

        $html = '';

        foreach ( $setting['options'] as $value => $label ) {
            $id    = "{$setting['id']}-$value";
            $label = ' <label for="' . $id . '">' . $label . '</label>';

            $html .= '<input id="' . $id . '"type="radio" value="' . $value . '" ';
            $html .= 'name="awpcp-options['. $setting['id'] .']" ';

            if ( $value === $current ) {
                $html .= 'checked="checked" />' . $label;
            } else {
                $html .= '>' . $label;
            }

            $html .= '<br/>';
        }

        $html.= '<span class="description">' . $setting['description'] . '</span>';

        echo $html;
    }
}
