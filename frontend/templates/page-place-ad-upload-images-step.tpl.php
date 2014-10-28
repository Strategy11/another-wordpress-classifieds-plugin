<h2><?php _e('Upload Images', 'AWPCP') ?></h2>

<?php
    if (get_awpcp_option('imagesapprove') == 1) {
        $messages[] = __('Image approval is in effect so any new images you upload will not be visible to viewers until an admin approves them.', 'AWPCP');
    }

    if ($images_uploaded > 0) {
        $messages[] = _x('Thumbnails of already uploaded images are shown below.', 'images upload step', 'AWPCP');
    }

    foreach ($messages as $message) {
        echo awpcp_print_message($message);
    }

	foreach($errors as $error) {
		echo awpcp_print_message($error, array('error'));
	}
?>

<ul class="upload-conditions clearfix">
	<li><?php _e('Image slots available', 'AWPCP') ?>: <strong><?php echo esc_html( $images_left ); ?></strong></li>
	<li><?php _e('Max image size', 'AWPCP') ?>: <strong><?php echo esc_html( $max_image_size / 1000 ); ?> KB</strong></li>
</ul>

<?php $media_uploader = awpcp_media_uploader_component(); ?>
<?php echo $media_uploader->render( $media_uploader_configuration ); ?>

<?php $messages = awpcp_messages_component(); ?>
<?php echo $messages->render( array( 'media-uploader', 'media-manager', 'thumbnails-generator' ) ); ?>

<?php $media_manager = awpcp_media_manager_component(); ?>
<?php echo $media_manager->render( $files, $media_manager_configuration ); ?>

<div class="awpcp-thumbnails-generator" data-nonce="<?php echo esc_attr( wp_create_nonce( 'awpcp-upload-generated-thumbnail-for-listing-' . $listing->ad_id ) ); ?>">
    <video preload="none" muted="muted" width="0" height="0"></video>
    <canvas></canvas>
</div>

<form class="awpcp-upload-images-form" method="post" enctype="multipart/form-data">
	<p class="form-submit">
		<input class="button" type="submit" value="<?php echo esc_attr( $next ); ?>" id="submit-no-images" name="submit-no-images">

		<input type="hidden" name="step" value="upload-images">
		<?php foreach ($hidden as $name => $value): ?>
		<input type="hidden" name="<?php echo esc_attr($name) ?>" value="<?php echo esc_attr($value) ?>">
		<?php endforeach ?>
	</p>
</form>
