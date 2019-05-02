<?php
/**
 * @package AWPCP\Admin\Importer
 */

// phpcs:disable

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AWPCP_ExportListingsAdminPage {

	private $request;

	public function __construct( $request ) {
		$this->request = $request;
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'awpcp-admin-export-style' );
		wp_enqueue_script( 'awpcp-admin-export' );
	}

    public function dispatch() {
    	$template = AWPCP_DIR . '/templates/admin/export-listings-admin-page.tpl.php';
	    return awpcp_render_template( $template, array());
    }

    public function ajax() {
	    if ( ! current_user_can( 'administrator' ) ) {
		    exit();
	    }

	    $error = '';

	    try {
		    if ( ! isset( $_REQUEST['state'] ) ) {
			    $export = new AWPCP_CSVExporter( array_merge( $this->request->post('settings'), array() ), awpcp_settings_api());
		    } else {
			    $state  = json_decode( base64_decode( $_REQUEST['state'] ), true );
			    if ( ! $state || ! is_array( $state ) || empty( $state['workingdir'] ) ) {
				    $error = _x( 'Could not decode export state information.', 'admin csv-export', 'another-wordpress-classifieds-plugin' );
			    }

			    $export = AWPCP_CSVExporter::from_state( $state );

			    if ( isset( $_REQUEST['cleanup'] ) && $_REQUEST['cleanup'] == 1 ) {
				    $export->cleanup();
			    } else {
				    $export->advance();
			    }
		    }
	    } catch (Exception $e) {
		    $error = $e->getMessage();
	    }

	    $state = ! $error ? $export->get_state() : null;

	    $response = array();
	    $response['error'] = $error;
	    $response['state'] = $state ? base64_encode( json_encode( $state ) ) : null;
	    $response['count'] = $state ? count( $state['listings'] ) : 0;
	    $response['exported'] = $state ? $state['exported'] : 0;
	    $response['filesize'] = $state ? size_format( $state['filesize'] ) : 0;
	    $response['isDone'] = $state ? $state['done'] : false;
	    $response['fileurl'] = $state ? ( $state['done'] ? $export->get_file_url() : '' ) : '';
	    $response['filename'] = $state ? ( $state['done'] ? basename( $export->get_file_url() ) : '' ) : '';

	    echo wp_send_json( $response );
    }
}
