<?php
/**
 * @package AWPCP\Templates\Admin\Settings
 */

if ( isset( $errors ) && $errors ) {
    foreach ( $errors as $err ) {
        echo awpcp_print_error( $err ); // XSS Ok.
    }
}

?><div class="awpcp-facebook-inline-documentation">

<form  method="post">
    <p>
        <?php wp_nonce_field( 'awpcp-facebook-settings' ); ?>
        <?php esc_html_e( 'If you are having additional problems with Facebook API, click "Diagnostics" to check your settings.', 'another-wordpress-classifieds-plugin' ); ?>
        <input type="submit" class="button-secondary" name="diagnostics" value="<?php esc_html_e( 'Diagnostics', 'another-wordpress-classifieds-plugin' ); ?>" />
    </p>
</form>

<hr />

</div>
