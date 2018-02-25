<?php
/**
 * @package AWPCP/Settings
 */

/**
 * Allows user to select WordPress pages as the value for plugin settings.
 */
class AWPCP_WordPressPageSettingsType {

    public function render( $content, $args, $settings ) {
        $dropdown_params = array(
            'name' => $settings->setting_name . '[' . $args['setting']->name . ']',
            'selected' => $settings->get_option( $args['setting']->name, 0 ),
            'show_option_none' => _x( '— Select —', 'page settings', 'another-wordpress-classifieds-plugin' ),
            'option_none_value' => 0,
            'echo' => false,
        );

        $create_page_button = sprintf(
            '<a class="button" href="%s">%s</a>',
            admin_url( 'post-new.php?post_type=page' ),
            __( 'Create Page', 'another-wordpress-classifieds-plugin' )
        );

        $description = sprintf( '<span class="description">%s</span>', $args['setting']->helptext );

        return wp_dropdown_pages( $dropdown_params ) . $create_page_button . '<br/>' . $description;
    }
}
