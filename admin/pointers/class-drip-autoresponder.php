<?php

function awpcp_drip_autoresponder() {
    return new AWPCP_DripAutoresponder();
}

class AWPCP_DripAutoresponder {

    public function register_pointer( $pointers ) {
        $nonce = wp_create_nonce( 'drip-autoresponder' );

        $pointers['drip-autoresponder'] = array(
            'content' => $this->render_content(),
            'buttons' => array(
                array(
                    'label' => _x( "Yes, I'd like my course, please", 'drip-autoresponder', 'another-wordpress-classifieds-plugin' ),
                    'event' => 'awpcp-autoresponder-user-subscribed',
                    'data' => array( $nonce ),
                    'elementClass' => 'button-primary',
                    'elementCSS' => array(
                        'marginLeft' => '10px',
                    ),
                ),
                array(
                    'label' => _x( 'No, thanks', 'drip-autoresponder', 'another-wordpress-classifieds-plugin' ),
                    'event' => 'awpcp-autoresponder-dismissed',
                    'data' => array( $nonce ),
                    'elementClass' => 'button',
                    'elementCSS' => array(
                        'marginLeft' => '5px',
                    ),
                ),
                array(
                    'label' => '',
                    'event' => 'nothing',
                    'data' => array(),
                    'elementClass' => 'spinner awpcp-spinner is-hidden',
                    'elementCSS' => array(
                        'display' => 'none',
                        'marginTop' => '4px',
                    ),
                ),
            ),
            'position' => array(
                'edge' => 'top',
                'align' => 'center',
            ),
        );

        return $pointers;
    }

    private function render_content() {
        $template = '<h3><title></h3><p><content></p><p><label for="awpcp-autoresponder-email"><b><label>:</b></label><br><input style="min-width: 100%" id="awpcp-autoresponder-email" type="text" name="awpcp-user-email" value="<user-email>" /></p><div class="awpcp-message error is-hidden"></div>';

        $title   = esc_html__( 'Want to know the Secrets of Building an Awesome Classifieds Website?', 'another-wordpress-classifieds-plugin' );
        $content = esc_html__( 'Find out how to create a compelling, thriving classifieds site from scratch in this ridiculously actionable (and free) 5-part email course.', 'another-wordpress-classifieds-plugin' );

        $template = str_replace( '<title>', $title, $template );
        $template = str_replace( '<content>', $content, $template );
        $template = str_replace( '<label>', esc_html__( 'Email Address', 'another-wordpress-classifieds-plugin' ), $template );
        $template = str_replace( '<user-email>', esc_html( wp_get_current_user()->user_email ), $template );

        return $template;
    }
}
