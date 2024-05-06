<h2><?php echo esc_html( _x( 'Login/Registration', 'place ad login step', 'another-wordpress-classifieds-plugin' ) ); ?></h2>

<?php
    if ( get_awpcp_option( 'show-create-listing-form-steps' ) ) {
        awpcp_listing_form_steps_componponent()->show( 'login' );
    }
?>

<?php echo awpcp_login_form( $message, $page_url ); ?>
