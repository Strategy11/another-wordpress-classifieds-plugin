<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



class AWPCP_WP_Members_Login_Form_Implementation {

    public function render( $redirect, $message = null ) {
        $form  = $message ? awpcp_print_message( $message ) : '';
        $form .= '[wpmem_form login redirect_to="' . $this->prepare_redirect_attribute( $redirect ) . '" /]';

        return do_shortcode( $form );
    }

    /**
     * Makes the redirect URL safe to use as a shortcode attribute value.
     *
     * The URL usually comes from the current request, so it must not be able
     * to close the attribute or the shortcode tag, or to open a new shortcode.
     *
     * @since x.x
     *
     * @param string $redirect The URL to redirect to after login.
     *
     * @return string
     */
    private function prepare_redirect_attribute( $redirect ) {
        return str_replace( array( '[', ']' ), array( '%5B', '%5D' ), esc_url_raw( (string) $redirect ) );
    }
}
