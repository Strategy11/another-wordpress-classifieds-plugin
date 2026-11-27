<?php
/**
 * @package AWPCP\Admin\Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @since 4.1.0
 */
class AWPCP_ExportListingsAdminPage {

    public function enqueue_scripts() {
        wp_enqueue_style( 'awpcp-admin-export-style' );
        wp_enqueue_script( 'awpcp-admin-export' );
    }

    public function dispatch() {
        $template = AWPCP_DIR . '/templates/admin/export-listings-admin-page.tpl.php';
        return awpcp_render_template( $template, array() );
    }

    public function ajax() {
        check_ajax_referer( 'awpcp-export-csv' );
        if ( ! awpcp_current_user_is_admin() ) {
            wp_send_json(
                array(
                    'error' => __( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ),
                )
            );
        }

        $error = '';

        try {
            if ( ! isset( $_REQUEST['state'] ) ) {
                $settings = (array) awpcp_get_var( array( 'param' => 'settings', 'default' => array() ), 'post' );
                $export   = new AWPCP_CSVExporter( array_merge( $settings, array() ), awpcp_settings_api() );
            } else {
                $state = awpcp_get_var( array( 'param' => 'state' ), 'post' );
                // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
                $state = json_decode( base64_decode( $state ), true );
                if ( ! $state || ! is_array( $state ) || empty( $state['workingdir'] ) ) {
                    $error = _x( 'Could not decode export state information.', 'admin csv-export', 'another-wordpress-classifieds-plugin' );
                }

                $export  = AWPCP_CSVExporter::from_state( $state );
                $cleanup = awpcp_get_var( array( 'param' => 'cleanup' ) );

                if ( $cleanup === '1' ) {
                    $export->cleanup();
                } else {
                    $export->advance();
                }
            }
        } catch ( Exception $e ) {
            $error = $e->getMessage();
        }

        $state = ! $error ? $export->get_state() : null;

        $response          = array();
        $response['error'] = $error;
        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
        $response['state']    = $state ? base64_encode( wp_json_encode( $state ) ) : null;
        $response['count']    = $state ? count( $state['listings'] ) : 0;
        $response['exported'] = $state ? $state['exported'] : 0;
        $response['filesize'] = $state ? size_format( $state['filesize'] ) : 0;
        $response['isDone']   = $state ? $state['done'] : false;
        $is_done              = $state && $state['done'];
        $response['fileurl']  = $is_done ? $export->get_file_url() : '';
        $response['filename'] = $is_done ? basename( $export->get_file_path() ) : '';

        wp_send_json( $response );
    }

    /**
     * Streams a finished export to an administrator and removes it from the server.
     *
     * @since x.x
     */
    public function download() {
        check_ajax_referer( 'awpcp-csv-export-download' );

        if ( ! awpcp_current_user_is_admin() ) {
            wp_die( esc_html__( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ), '', array( 'response' => 403 ) );
        }

        $export_id = awpcp_get_var( array( 'param' => 'export' ) );
        $file_path = AWPCP_CSVExporter::get_export_file( $export_id );

        if ( ! $file_path ) {
            wp_die( esc_html__( 'The requested export file does not exist.', 'another-wordpress-classifieds-plugin' ), '', array( 'response' => 404 ) );
        }

        $this->send_file( $file_path );

        AWPCP_CSVExporter::delete_export( $export_id );

        $this->end_request();
    }

    /**
     * Stops the request once the file has been sent.
     *
     * @since x.x
     */
    protected function end_request() {
        exit;
    }

    /**
     * @since x.x
     *
     * @param string $file_path Path to the file to send.
     */
    protected function send_file( $file_path ) {
        while ( ob_get_level() ) {
            ob_end_clean();
        }

        $filename     = basename( $file_path );
        $content_type = 'export.zip' === $filename ? 'application/zip' : 'text/csv; charset=' . get_option( 'blog_charset' );

        nocache_headers();
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: ' . $content_type );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Content-Length: ' . filesize( $file_path ) );

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streams large exports without loading them into memory.
        readfile( $file_path );
    }
}
