<?php

function awpcp_media_uploader_component() {
    return new AWPCP_MediaUploaderComponent( awpcp()->js );
}

class AWPCP_MediaUploaderComponent {

    private $javascript;

    public function __construct( $javascript ) {
        $this->javascript = $javascript;
    }

    public function render( $configuration ) {
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
