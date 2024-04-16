<?php
/**
 * @package AWPCP\Settings\Renderers
 */

/**
 * @since 4.0.0
 */
class AWPCP_LicenseSettingsRenderer {

    /**
     * @var LicensesManager
     */
    private $licenses_manager;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @since 4.0.0
     */
    public function __construct( $licenses_manager, $settings ) {
        $this->licenses_manager = $licenses_manager;
        $this->settings         = $settings;
    }

    /**
     * @since 4.0.0
     */
    public function render_setting( $setting ) {
        $module_name = $setting['params']['module_name'];
        $module_slug = $setting['params']['module_slug'];

        $license = $this->settings->get_option( $setting['id'] );

        echo '<input id="' . esc_attr( $setting['id'] ) . '" class="regular-text" type="text" name="awpcp-options[' . esc_attr( $setting['id'] ) . ']" value="' . esc_attr( $license ) . '">';

        if ( ! empty( $license ) ) {
            if ( $this->licenses_manager->is_license_valid( $module_name, $module_slug ) ) {
                echo '<input class="button-secondary" type="submit" name="awpcp-deactivate-' . esc_attr( $module_slug ) . '-license" value="' . esc_attr__( 'Deactivate', 'another-wordpress-classifieds-plugin' ) . '"/>';
                echo '<br>' . str_replace( '<license-status>', '<span class="awpcp-license-status awpcp-license-valid">' . esc_html__( 'active', 'another-wordpress-classifieds-plugin' ) . '</span>.', __( 'Status: <license-status>', 'another-wordpress-classifieds-plugin' ) );
            } elseif ( $this->licenses_manager->is_license_inactive( $module_name, $module_slug ) ) {
                echo '<input class="button-secondary" type="submit" name="awpcp-activate-' . esc_attr( $module_slug ) . '-license" value="' . esc_attr__( 'Activate', 'another-wordpress-classifieds-plugin' ) . '"/>';
                echo '<br>' . str_replace( '<license-status>', '<span class="awpcp-license-status awpcp-license-inactive">' . esc_html__( 'inactive', 'another-wordpress-classifieds-plugin' ) . '</span>.', __( 'Status: <license-status>', 'another-wordpress-classifieds-plugin' ) );
            } else {
                echo '<input class="button-secondary" type="submit" name="awpcp-activate-' . esc_attr( $module_slug ) . '-license" value="' . esc_attr__( 'Activate', 'another-wordpress-classifieds-plugin' ) . '"/>';

                $contact_url     = 'https://awpcp.com/contact';
                $contact_message = __( 'Click the button above to check the status of your license. Please <contact-link>contact customer support</a> if you think the reported status is wrong.', 'another-wordpress-classifieds-plugin' );

                echo '<br>' . str_replace( '<contact-link>', '<a href="' . esc_url( $contact_url ) . '" target="_blank">', $contact_message );

                if ( $this->licenses_manager->is_license_expired( $module_name, $module_slug ) ) {
                    echo '<br>' . str_replace( '<license-status>', '<span class="awpcp-license-status awpcp-license-expired">' . esc_html__( 'expired', 'another-wordpress-classifieds-plugin' ) . '</span>.', __( 'Status: <license-status>', 'another-wordpress-classifieds-plugin' ) );
                } elseif ( $this->licenses_manager->is_license_disabled( $module_name, $module_slug ) ) {
                    echo '<br>' . str_replace( '<license-status>', '<span class="awpcp-license-status awpcp-license-invalid">' . esc_html__( 'disabled', 'another-wordpress-classifieds-plugin' ) . '</span>.', __( 'Status: <license-status>', 'another-wordpress-classifieds-plugin' ) );
                } else {
                    echo '<br>' . str_replace( '<license-status>', '<span class="awpcp-license-status awpcp-license-invalid">' . esc_html__( 'unknown', 'another-wordpress-classifieds-plugin' ) . '</span>.', __( 'Status: <license-status>', 'another-wordpress-classifieds-plugin' ) );
                }
            }
            wp_nonce_field( 'awpcp-update-license-status-nonce', 'awpcp-update-license-status-nonce' );
        }
    }
}
