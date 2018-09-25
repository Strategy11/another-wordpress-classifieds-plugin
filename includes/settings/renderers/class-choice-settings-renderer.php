<?php
/**
 * @package AWPCP\Settings\Renderers
 */

/**
 * @since 4.0.0
 */
class AWPCP_ChoiceSettingsRenderer {

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
        $field_name = 'awpcp-options[' . $setting['id'] . '][]';
        $field_type = 'checkbox';

        if ( isset( $setting['multiple'] ) && empty( $setting['multiple'] ) ) {
            $field_type = 'radio';
        }

        $selected = array_filter( $this->settings->get_option( $setting['id'], array() ), 'strlen' );

        $html = array( sprintf( '<input type="hidden" name="%s" value="">', $field_name ) );

        foreach ( $setting['choices'] as $value => $label ) {
            $id = "{$setting['id']}-$value";

            // Options values ($selected) are stored as strings.
            $checked = in_array( (string) $value, $selected, true ) ? 'checked="checked"' : '';

            $html_field = '<input id="%s" type="%s" name="%s" value="%s" %s />';
            $html_field = sprintf( $html_field, $id, $field_type, $field_name, $value, $checked );
            $html_label = '<label for="' . $id . '">' . $label . '</label><br/>';

            $html[] = $html_field . '&nbsp;' . $html_label;
        }

        $html[] = '<span class="description">' . $setting['description'] . '</span>';

        echo join( '', $html ); // XSS Ok.
    }
}
