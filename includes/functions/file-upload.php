<?php

/**
 * Return mime types associated with image files.
 *
 * @since 3.0.2
 */
function awpcp_get_image_mime_types() {
    return array(
        'image/png',
        'image/jpg', 'image/jpeg', 'image/pjpeg',
        'image/gif',
    );
}

/**
 * @param $file A $_FILES item
 */
function awpcp_upload_image_file($directory, $filename, $tmpname, $min_size, $max_size, $min_width, $min_height, $uploaded=true) {
    $filename = sanitize_file_name($filename);
    $newname = wp_unique_filename($directory, $filename);
    $newpath = trailingslashit($directory) . $newname;

    if ( !file_exists( $tmpname ) ) {
        /* translators: %s the file name */
        return sprintf( __( 'The specified image file does not exists: %s.', 'another-wordpress-classifieds-plugin' ), $tmpname );
    }

    $ext = strtolower( awpcp_get_file_extension( $filename ) );
    $imginfo = getimagesize($tmpname);
    $size = filesize($tmpname);

    $allowed_extensions = array('gif', 'jpg', 'jpeg', 'png');

    if (empty($filename)) {
        return __( 'No file was selected.', 'another-wordpress-classifieds-plugin');
    }

    if ($uploaded && !is_uploaded_file($tmpname)) {
        return __( 'Unknown error encountered while uploading the image.', 'another-wordpress-classifieds-plugin');
    }

    if ( empty( $size ) ) {
        /* translators: %s the file name */
        $message = __( 'There was an error trying to find out the file size of the image %s.', 'another-wordpress-classifieds-plugin' );
        return sprintf( $message, $filename );
    }

    if (!(in_array($ext, $allowed_extensions))) {
        /* translators: %s the file name */
        return sprintf( __( 'The file %s has an invalid extension and was rejected.', 'another-wordpress-classifieds-plugin'), $filename );

    } elseif ($size < $min_size) {
        /* translators: %1$s the file name, %2$d the size limit */
        $message = __( 'The size of %1$s was too small. The file was not uploaded. File size must be greater than %2$d bytes.', 'another-wordpress-classifieds-plugin');
        return sprintf($message, $filename, $min_size);

    } elseif ($size > $max_size) {
        /* translators: %1$s the file name, %2$s the size limit */
        $message = __( 'The file %1$s was larger than the maximum allowed file size of %2$s bytes. The file was not uploaded.', 'another-wordpress-classifieds-plugin');
        return sprintf($message, $filename, $max_size);

    } elseif (!isset($imginfo[0]) && !isset($imginfo[1])) {
        /* translators: %s the file name */
        return sprintf( __( 'The file %s does not appear to be a valid image file.', 'another-wordpress-classifieds-plugin' ), $filename );

    } elseif ( $imginfo[0] < $min_width ) {
        /* translators: %1$s the file name, %2$s the size limit */
        $message = __( 'The image %1$s did not meet the minimum width of %2$s pixels. The file was not uploaded.', 'another-wordpress-classifieds-plugin');
        return sprintf($message, $filename, $min_width);

    } elseif ($imginfo[1] < $min_height) {
        /* translators: %1$s the file name, %2$s the size limit */
        $message = __( 'The image %1$s did not meet the minimum height of %2$s pixels. The file was not uploaded.', 'another-wordpress-classifieds-plugin');
        return sprintf( $message, $filename, $min_height );
    }

    if ($uploaded && !@move_uploaded_file($tmpname, $newpath)) {
        /* translators: %s the file name */
        $message = __( 'The file %s could not be moved to the destination directory.', 'another-wordpress-classifieds-plugin');
        return sprintf($message, $filename);

    } elseif (!$uploaded && !@copy($tmpname, $newpath)) {
        /* translators: %s the file name */
        $message = __( 'The file %s could not be moved to the destination directory.', 'another-wordpress-classifieds-plugin');
        return sprintf($message, $filename);
    }

    if (!awpcp_create_image_versions($newname, $directory)) {
        /* translators: %s the file name */
        $message = __( 'Could not create resized versions of image %s.', 'another-wordpress-classifieds-plugin');
        @unlink($newpath);
        return sprintf($message, $filename);
    }

    @chmod($newpath, 0644);

    return array('original' => $filename, 'filename' => $newname);
}

function awpcp_setup_uploads_dir() {
    // TODO: Remove directory permissions setting when this code is finally removed.
    $permissions = awpcp_directory_permissions();

    $upload_dir_name = get_awpcp_option('uploadfoldername', 'uploads');
    $upload_dir = WP_CONTENT_DIR . '/' . $upload_dir_name . '/';

    // Required to set permission on main upload directory
    require_once( AWPCP_DIR . '/includes/class-fileop.php' );

    $fileop = new fileop();
    $owner = fileowner(WP_CONTENT_DIR);

    if (!is_dir($upload_dir) && is_writable(WP_CONTENT_DIR)) {
        umask(0);
        wp_mkdir_p( $upload_dir );
        chown($upload_dir, $owner);
    }

    // TODO: It is a waste of resources to check this on every request.
    if ( ! is_writable( $upload_dir ) ) {
        $fileop->set_permission( $upload_dir, $permissions );
    }

    $images_dir = $upload_dir . 'awpcp/';
    $thumbs_dir = $upload_dir . 'awpcp/thumbs/';

    if (!is_dir($images_dir) && is_writable($upload_dir)) {
        umask(0);
        wp_mkdir_p( $images_dir );
        @chown($images_dir, $owner);
    }

    if (!is_dir($thumbs_dir) && is_writable($upload_dir)) {
        umask(0);
        wp_mkdir_p( $thumbs_dir );
        @chown($thumbs_dir, $owner);
    }

    // TODO: It is a waste of resources to check this on every request.
    if ( ! is_writable( $images_dir ) ) {
        $fileop->set_permission( $images_dir, $permissions );
    }

    // TODO: It is a waste of resources to check this on every request.
    if ( ! is_writable( $thumbs_dir ) ) {
        $fileop->set_permission( $thumbs_dir, $permissions );
    }

    return array($images_dir, $thumbs_dir);
}

function awpcp_get_image_constraints() {
    $min_width = get_awpcp_option('imgminwidth');
    $min_height = get_awpcp_option('imgminheight');
    $min_size = get_awpcp_option('minimagesize');
    $max_size = get_awpcp_option('maximagesize');
    return array($min_width, $min_height, $min_size, $max_size);
}

/**
 * Create thumbnails and resize original image to match image size
 * restrictions.
 * XXX: Moved to ImageFileProcessor class.
 */
function awpcp_create_image_versions($filename, $directory) {
    $directory = trailingslashit($directory);
    $thumbnails = $directory . 'thumbs/';

    $filepath = $directory . $filename;

    awpcp_fix_image_rotation( $filepath );

    // create thumbnail
    $width = get_awpcp_option('imgthumbwidth');
    $height = get_awpcp_option('imgthumbheight');
    $crop = get_awpcp_option('crop-thumbnails');
    $thumbnail = awpcp_make_intermediate_size($filepath, $thumbnails, $width, $height, $crop);

    // create primary image thumbnail
    $width = get_awpcp_option('primary-image-thumbnail-width');
    $height = get_awpcp_option('primary-image-thumbnail-height');
    $crop = get_awpcp_option('crop-primary-image-thumbnails');
    $primary = awpcp_make_intermediate_size($filepath, $thumbnails, $width, $height, $crop, 'primary');

    // resize original image to match restrictions
    $width = get_awpcp_option('imgmaxwidth');
    $height = get_awpcp_option('imgmaxheight');
    $resized = awpcp_make_intermediate_size($filepath, $directory, $width, $height, false, 'large');

    return $resized && $thumbnail && $primary;
}

/**
 * XXX: Moved to ImageFileProcessor class.
 * @since 3.0.2
 */
function awpcp_fix_image_rotation( $filepath ) {
    if ( ! function_exists( 'exif_read_data' ) ) {
        return;
    }

    $exif_data = @exif_read_data( $filepath );

    $orientation = isset( $exif_data['Orientation'] ) ? $exif_data['Orientation'] : 0;
    $mime_type = isset( $exif_data['MimeType'] ) ? $exif_data['MimeType'] : '';

    $rotation_angle = 0;
    if ( 6 == $orientation ) {
        $rotation_angle = 90;
    } elseif ( 3 == $orientation ) {
        $rotation_angle = 180;
    } elseif ( 8 == $orientation ) {
        $rotation_angle = 270;
    }

    if ( $rotation_angle > 0 ) {
        awpcp_rotate_image( $filepath, $mime_type, $rotation_angle );
    }
}

/**
 * @since 3.0.2
 */
function awpcp_rotate_image( $file, $mime_type, $angle ) {
    if ( class_exists( 'Imagick' ) && method_exists( 'Imagick', 'setImageOrientation' ) ) {
        awpcp_rotate_image_with_imagick( $file, $angle );
    } else {
        awpcp_rotate_image_with_gd( $file, $mime_type, $angle );
    }
}

/**
 * @since 3.0.2
 */
function awpcp_rotate_image_with_imagick( $filepath, $angle ) {
    $imagick = new Imagick();
    $imagick->readImage( $filepath );
    $imagick->rotateImage( new ImagickPixel(), $angle );
    $imagick->setImageOrientation( 1 );
    $imagick->writeImage( $filepath );
    $imagick->clear();
    $imagick->destroy();
}

/**
 * @since 3.0.2
 */
function awpcp_rotate_image_with_gd( $filepath, $mime_type, $angle ) {
    // GD needs negative degrees
    $angle = -$angle;

    switch ( $mime_type ) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg( $filepath );
            $rotate = imagerotate( $source, $angle, 0 );
            imagejpeg( $rotate, $filepath );
            break;
        case 'image/png':
            $source = imagecreatefrompng( $filepath );
            $rotate = imagerotate( $source, $angle, 0 );
            imagepng( $rotate, $filepath );
            break;
        case 'image/gif':
            $source = imagecreatefromgif( $filepath );
            $rotate = imagerotate( $source, $angle, 0 );
            imagegif( $rotate, $filepath );
            break;
        default:
            break;
    }
}

/**
 * XXX: Moved to ImageFileProcessor class.
 */
function awpcp_make_intermediate_size($file, $directory, $width, $height, $crop=false, $suffix='') {
    $path_info = awpcp_utf8_pathinfo( $file );
    $filename = preg_replace("/\.{$path_info['extension']}/", '', $path_info['basename']);
    $suffix = empty($suffix) ? '.' : "-$suffix.";

    $newpath = trailingslashit($directory) . $filename . $suffix . $path_info['extension'];

    $image = image_make_intermediate_size($file, $width, $height, $crop);

    if (!is_writable($directory)) {
        @chmod( $directory, awpcp_directory_permissions() );
    }

    if (is_array($image) && !empty($image)) {
        $tmppath = trailingslashit($path_info['dirname']) . $image['file'];
        $result = rename($tmppath, $newpath);
    } else {
        $result = copy($file, $newpath);
    }
    @chmod($newpath, 0644);

    return $result;
}

/**
 * Returns the contents of a directory (ignoring . and .. special files).
 *
 * @param string $path a directory.
 * @return array list of files within the directory.
 * @since 3.6
 */
function awpcp_scandir( $path, $args = array() ) {
    if ( ! is_dir( $path ) ) {
        return array();
    }

    return array_diff( scandir( $path ), array( '.', '..' ) );
}
