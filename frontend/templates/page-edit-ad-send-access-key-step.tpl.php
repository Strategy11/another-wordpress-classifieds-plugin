<?php
    foreach ($messages as $message) {
        echo awpcp_print_message($message);
    }

    foreach ($errors as $index => $error) {
        if (is_numeric($index)) {
            echo awpcp_print_error($error);
        }
    }
?>

<div>
    <form method="post" action="<?php echo esc_attr( $send_access_key_url ); ?>">
        <?php foreach( $hidden as $name => $value ): ?>
        <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
        <?php endforeach; ?>

        <h2><?php echo esc_html( __( 'Resend Ad access key', 'another-wordpress-classifieds-plugin' ) ); ?></h2>

        <p class="awpcp-form-spacer">
            <label for="ad-email"><?php echo esc_html( __( 'Enter your email address', 'another-wordpress-classifieds-plugin' ) ); ?></label>
            <input class="awpcp-textfield inputbox" id="ad-email" type="text" size="50" name="ad_email" value="<?php echo awpcp_esc_attr( $form['ad_email'] ); ?>" />
            <?php awpcp_show_form_error( 'ad_email', $errors ); ?>
        </p>

        <input type="submit" class="button" value="<?php echo esc_html( _x(  "Continue", 'send ad access key form', 'another-wordpress-classifieds-plugin' ) ); ?>" />
    </form>
</div>
