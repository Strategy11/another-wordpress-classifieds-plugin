<?php foreach ( (array) $messages as $message ): ?>
    <?php echo awpcp_print_message( $message ); ?>
<?php endforeach; ?>

<form class="awpcp-preview-ad-form" action="<?php echo esc_attr( $this->url() ); ?>" method="post">
    <?php foreach($hidden as $name => $value): ?>
    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
    <?php endforeach ?>
    <input type="hidden" name="step" value="preview-ad" />

    <span><?php echo esc_html( __( 'This is a preview of your Ad. Use the buttons below to go back and edit your Ad, manage the uploaded images or finish the posting process.', 'another-wordpress-classifieds-plugin' ) ); ?></span>
    <br>
    <input class="button" type="submit" name="edit-details" value="<?php echo esc_attr( __( "Edit Details", 'another-wordpress-classifieds-plugin' ) ); ?>" />
    <?php if ( $ui['manage-images'] ): ?>
    <input class="button" type="submit" name="manage-images" value="<?php echo esc_attr( __( "Manage Images", 'another-wordpress-classifieds-plugin' ) ); ?>" />
    <?php endif; ?>
    <input class="button button-primary" type="submit" name="finish" value="<?php echo esc_attr( __( "Finish", 'another-wordpress-classifieds-plugin' ) ); ?>" />
</form>

<?php echo showad( $ad->ID, true, true, false, false ); ?>
