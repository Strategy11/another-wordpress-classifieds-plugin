<?php
/**
 * Export Settings admin page.
 *
 * @package AWPCP\Admin\Pages
 */

/**
 * Constructor function for AWPCP_Export_Settings_Admin_Page class.
 */
function awpcp_export_settings_admin_page() {
    return new AWPCP_Export_Settings_Admin_Page(
        awpcp_settings_json_reader()
    );
}

/**
 * Admin page that allows users to export settings into a JSON file.
 */
class AWPCP_Export_Settings_Admin_Page {

    /**
     * An instance of a Settings Reader.
     *
     * @var object
     */
    private $settings_reader;

    /**
     * Constructor.
     *
     * @param object $settings_reader An instance of a Settings Reader.
     */
    public function __construct( $settings_reader ) {
        $this->settings_reader = $settings_reader;
    }

    /**
     * Code executed during admin_init when this page is visited.
     */
    public function on_admin_init() {
        $filename = 'awpcp-settings-' . awpcp_datetime( 'Ymd-His' ) . '.json';

        header( 'Content-Description: File Transfer' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );

        echo $this->settings_reader->read_all(); // WPCS: XSS OK.

        exit();
    }
}
