<?php
/**
 * @package AWPCP\Admin\Pages
 */

echo awpcp_html_admin_second_level_heading( $heading_params ); // WPCS: XSS Ok. ?>

<div class="awpcp-import-settings-form">
    <p class="install-help"><?php echo esc_html__( 'If you have a file with plugin settings in a JSON format, you may import those settings uploading the file here.', 'another-wordpress-classifieds-plugin' ); ?></p>
    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( $action_url ); ?>">
        <?php wp_nonce_field( $nonce_action ); ?>
        <label class="screen-reader-text" for="settings_file"><?php echo esc_html__( 'Settings JSON file', 'another-wordpress-classifieds-plugin' ); ?></label>
        <input type="file" id="settings_file" name="settings_file">
        <input type="submit" name="import-settings-submit" id="import-settings-submit" class="button button-primary" value="<?php echo esc_html__( 'Import Now', 'another-wordpress-classifieds-plugin' ); ?>">
        <a class="button" href="<?php echo esc_url( $settings_url ); ?>"><?php echo esc_html__( 'Return to Settings', 'another-wordpress-classifieds-plugin' ); ?></a>
    </form>
</div>
