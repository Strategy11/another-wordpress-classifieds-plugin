<?php

function awpcp_drip_autoresponder_ajax_handler() {
    return new AWPCP_DripAutoresponderAjaxHandler( awpcp()->settings, awpcp_request(), awpcp_ajax_response() );
}

class AWPCP_DripAutoresponderAjaxHandler extends AWPCP_AjaxHandler {

    const DRIP_FORM_URL = 'https://strategy1137274.activehosted.com/proc.php?jsonp=true';

    private $settings;
    private $request;

    public function __construct( $settings, $request, $response ) {
        parent::__construct( $response );

        $this->settings = $settings;
        $this->request = $request;
    }

    public function ajax() {
        if ( ! wp_verify_nonce( $this->request->post( 'nonce' ), 'drip-autoresponder' ) ) {
            return $this->error_response( __( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ) );
        }

        $action = $this->request->post( 'action' );

        if ( $action == 'awpcp-autoresponder-user-subscribed' ) {
            return $this->user_subscribed();
        } elseif ( $action == 'awpcp-autoresponder-dismissed' ) {
            return $this->autoresponder_dismissed();
        }
    }

    public function user_subscribed() {
        $posted_data = $this->get_posted_data();

        if ( ! awpcp_is_valid_email_address( $posted_data['email'] ) ) {
            return $this->error_response( _x( 'The email address entered is not valid.', 'drip-autoresponder', 'another-wordpress-classifieds-plugin' ) );
        }

        $response = wp_remote_post(
            self::DRIP_FORM_URL,
            array(
                'body' => array(
                    'firstname' => $posted_data['name'],
                    'email'     => $posted_data['email'],
                    'u'         => '19',
                    'f'         => '19',
                    'act'       => 'sub',
                    'c'         => 0,
                    'm'         => 0,
                    'v'         => '2',
                ),
            )
        );

        if ( $this->was_request_successful( $response ) ) {
            $this->disable_autoresponder();
            return $this->success( array( 'pointer' => $this->build_confirmation_pointer() ) );
        } elseif ( isset( $response['body'] ) ) {
            return $this->error_response( $this->get_error_from_response_body( $response['body'] ) );
        } else {
            return $this->error_response( $this->get_unexpected_error_message() );
        }
    }

    public function get_posted_data() {
        $current_user = wp_get_current_user();
        $name_alternatives = array( 'display_name', 'user_login', 'username' );

        return array(
            'name' => awpcp_get_object_property_from_alternatives( $current_user, $name_alternatives ),
            'email' => $this->request->post( 'email' ),
        );
    }

    private function was_request_successful( $response ) {
        if ( is_wp_error( $response ) ) {
            return false;
        }

        $response = (array) $response;

        if ( ! isset( $response['response']['code'] ) || $response['response']['code'] !== 200 ) {
            return false;
        }

        return true;
    }

    private function disable_autoresponder() {
        $this->settings->update_option( 'show-drip-autoresponder', false, true );
    }

    private function build_confirmation_pointer() {
        return array(
            'content' => $this->render_pointer_content(),
            'buttons' => array(
                array(
                    'label' => 'Got it!',
                    'event' => 'awpcp-autoresponder-confirmation-dismissed',
                    'elementClass' => 'button',
                    'elementCSS' => array(
                        'marginLeft' => '10px',
                    ),
                ),
            ),
            'position' => array(
                'edge' => 'top',
                'align' => 'center',
            ),
        );
    }

    private function render_pointer_content() {
        $template = '<h3><title></h3><p><content></p>';

        $title   = esc_html__( 'Thank you for signing up!', 'another-wordpress-classifieds-plugin' );
        $content = esc_html__( 'Please check your email and click the link provided to confirm your subscription.', 'another-wordpress-classifieds-plugin' );

        $template = str_replace( '<title>', $title, $template );
        $template = str_replace( '<content>', $content, $template );

        return $template;
    }

    private function get_error_from_response_body( $body ) {
        $errors = array();

        if ( preg_match_all( ';<span class="error">(.*?mail.*?)</span>;', $body, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $errors[] = $match[1];
            }
        }

        $errors = array_unique( $errors );

        if ( count( $errors ) == 1 ) {
            return trim( reset( $errors ) );
        } elseif ( ! empty( $errors ) ) {
            return sprintf( '<li>%s</li>', implode( '</li><li>', array_map( 'trim', $errors ) ) );
        } else {
            return $this->get_unexpected_error_message();
        }
    }

    private function get_unexpected_error_message() {
        return __( 'An unexpected error ocurred.', 'another-wordpress-classifieds-plugin' );
    }

    private function autoresponder_dismissed() {
        $this->disable_autoresponder();
        return $this->success();
    }
}
