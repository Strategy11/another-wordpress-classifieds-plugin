<?php

function awpcp_media_uploader_component() {
    return new AWPCP_MediaUploaderComponent( awpcp_file_validation_errors(), awpcp()->js );
}

class AWPCP_MediaUploaderComponent {

    private $validation_errors;
    private $javascript;

    public function __construct( $validation_errors, $javascript ) {
        $this->validation_errors = $validation_errors;
        $this->javascript = $javascript;
    }

    public function render( $configuration ) {
        $configuration = wp_parse_args( $configuration, array(
            'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
            'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
        ) );

        $this->javascript->set( 'media-uploader-data', $configuration );

        return $this->render_component();
    }

    private function render_component() {
        ob_start();
        include( AWPCP_DIR . '/templates/components/media-uploader.tpl.php' );
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
