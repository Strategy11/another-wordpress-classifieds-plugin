<?php

function awpcp_media_manager_component() {
    return new AWPCP_MediaManagerComponent( awpcp()->js );
}

class AWPCP_MediaManagerComponent {

    private $javascript;

    public function __construct( $javascript ) {
        $this->javascript = $javascript;
    }

    public function render( $listing, $files = array() ) {
        $this->javascript->set( 'media-manager-data', array(
            'files' => $this->prepare_files( $files ),
            'nonce' => wp_create_nonce( 'manage-listing-media-' . $listing->ad_id )
        ) );

        return $this->render_component();
    }

    private function prepare_files( $files ) {
        $files_info = array();

        foreach ( $files as $file ) {
            $files_info[] = array(
                'id' => $file->id,
                'name' => $file->name,
                'listingId' => $file->ad_id,
                'enabled' => $file->enabled,
                'status' => $file->status,
                'isImage' => $file->is_image(),
                'isVideo' => $file->is_video(),
                'isPrimary' => $file->is_primary(),
                'thumbnailUrl' => $file->get_url( 'thumbnail' ),
                'iconUrl' => $file->get_icon_url(),
                'url' => $file->get_url(),
            );
        }

        return $files_info;
    }

    private function render_component() {
        ob_start();
        include( AWPCP_DIR . '/templates/components/media-manager.tpl.php' );
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
