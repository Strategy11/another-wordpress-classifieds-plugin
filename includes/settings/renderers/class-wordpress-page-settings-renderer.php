<?php
/**
 * @package AWPCP/Settings/Renderers
 */

/**
 * Allows user to select WordPress pages as the value for plugin settings.
 */
class AWPCP_WordPressPageSettingsRenderer {

    /**
     * @since 4.0.0
     */
    public function __construct( $settings ) {
        $this->settings = $settings;
    }

    /**
     * Handler for awpcp_register_settings action.
     */
    public function render_setting( $setting ) {
        $dropdown_params = array(
            'name'              => $this->settings->setting_name . '[' . $setting['id'] . ']',
            'selected'          => $this->settings->get_option( $setting['id'], 0 ),
            'show_option_none'  => _x( '— Select —', 'page settings', 'another-wordpress-classifieds-plugin' ),
            'option_none_value' => 0,
            'echo'              => false,
        );

        $create_page_button = sprintf(
            '<a class="button" href="%s">%s</a>',
            admin_url( 'post-new.php?post_type=page' ),
            __( 'Create Page', 'another-wordpress-classifieds-plugin' )
        );

        $description = sprintf( '<span class="description">%s</span>', $setting['description'] );

        echo wp_dropdown_pages( $dropdown_params ) . $create_page_button . '<br/>' . $description; // XSS Ok.
    }
}
