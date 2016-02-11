<?php

function awpcp_media_manager_component() {
    return new AWPCP_MediaManagerComponent(
        awpcp_attachment_properties(),
        awpcp()->js,
        awpcp()->settings
    );
}

class AWPCP_MediaManagerComponent {

    private $attachment_properties;
    private $javascript;
    private $settings;

    public function __construct( $attachment_properties, $javascript, $settings ) {
        $this->attachment_properties = $attachment_properties;
        $this->javascript = $javascript;
        $this->settings = $settings;
    }

    public function render( $files = array(), $options = array() ) {
        $options['files'] = $this->prepare_files( $files );

        $this->javascript->set( 'media-manager-data', $options );

        return $this->render_component();
    }

    private function prepare_files( $files ) {
        $files_info = array();

        foreach ( $files as $file ) {
            $files_info[] = array(
                'id' => $file->ID,
                'name' => $file->post_title,
                'listingId' => $file->post_parent,
                'enabled' => $this->attachment_properties->is_enabled( $file ),
                'status' => $this->attachment_properties->get_allowed_status( $file ),
                'mimeType' => $file->post_mime_type,
                'isImage' => $this->attachment_properties->is_image( $file ),
                'isPrimary' => $this->attachment_properties->is_featured( $file ),
                'thumbnailUrl' => $this->attachment_properties->get_image_url( $file, 'thumbnail' ),
                'iconUrl' => $this->attachment_properties->get_icon_url( $file ),
                'url' => $this->attachment_properties->get_image_url( $file, 'large' ),
            );
        }

        return $files_info;
    }

    private function render_component() {
        $thumbnails_width = $this->settings->get_option( 'imgthumbwidth' );

        ob_start();
        include( AWPCP_DIR . '/templates/components/media-manager.tpl.php' );
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
