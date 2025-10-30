<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



function awpcp_image_resizer() {
    return new AWPCP_ImageResizer( awpcp_filesystem(), awpcp()->settings );
}

class AWPCP_ImageResizer {

    private $filesystem;
    private $settings;
    private $wp_filesystem;

    public function __construct( $filesystem, $settings ) {
        $this->filesystem    = $filesystem;
        $this->settings      = $settings;
        $this->wp_filesystem = awpcp_get_wp_filesystem();

        if ( ! $this->wp_filesystem ) {
            throw new AWPCP_Exception( esc_html__( 'Unable to initialize WordPress file system.', 'another-wordpress-classifieds-plugin' ) );
        }
    }

    public function create_thumbnail( $source, $filename ) {
        $thumbnails_dir = $this->get_thumbnails_dir();

        $width  = $this->settings->get_option( 'imgthumbwidth' );
        $height = $this->settings->get_option( 'imgthumbheight' );
        $crop   = $this->settings->get_option( 'crop-thumbnails' );

        return $this->make_intermediate_image_size( $source, $filename, $thumbnails_dir, $width, $height, $crop );
    }

    public function get_thumbnails_dir() {
        return $this->filesystem->get_thumbnails_dir();
    }

    private function make_intermediate_image_size( $source, $filename, $dest_dir, $width, $height, $crop = false, $suffix='' ) {
        $pathinfo = awpcp_utf8_pathinfo( $source );

        $safe_suffix      = empty( $suffix ) ? '.' : "-$suffix.";
        $extension        = $pathinfo['extension'];
        $parent_directory = $pathinfo['dirname'];

        $generated_image_name = $filename . $safe_suffix . $extension;
        $generated_image_path = implode( DIRECTORY_SEPARATOR, array( $dest_dir, $generated_image_name ) );

        $generated_image = image_make_intermediate_size( $source, $width, $height, $crop );

        if ( is_array( $generated_image ) ) {
            $temporary_image_path = implode( DIRECTORY_SEPARATOR, array( $parent_directory, $generated_image['file'] ) );
            $result               = $this->wp_filesystem->move( $temporary_image_path, $generated_image_path );
        }

        if ( ! isset( $result ) || $result === false ) {
            $result = $this->wp_filesystem->copy( $source, $generated_image_path );
        }

        $this->wp_filesystem->chmod( $generated_image_path, FS_CHMOD_FILE );

        return $result;
    }

    public function create_thumbnail_from_uploaded_file( $file ) {
        return $this->create_thumbnail( $file->get_path(), $file->get_file_name() );
    }

    public function create_thumbnail_for_media( $media, $source_image ) {
        $filename = sprintf( '%s-%d', awpcp_utf8_pathinfo( $media->name, PATHINFO_FILENAME ), $media->id );
        return $this->create_thumbnail( $source_image, $filename );
    }
}
