<?php

function awpcp_send_to_facebook_helper() {
    return new AWPCP_SendToFacebookHelper(
        AWPCP_Facebook::instance(),
        awpcp_media_api(),
        awpcp_wordpress()
    );
}

class AWPCP_SendToFacebookHelper {

    private $facebook_config;
    private $media;
    private $wordpress;

    public function __construct( $facebook_config, $media, $wordpress ) {
        $this->facebook_config = $facebook_config;
        $this->media = $media;
        $this->wordpress = $wordpress;
    }

    public function send_listing_to_facebook_page( $listing ) {
        $this->facebook_config->set_access_token( 'page_token' );

        if ( ! $this->facebook_config->is_page_set() ) {
            throw new AWPCP_Exception( 'There is no page selected.' );
        }

        if ( $this->wordpress->get_post_meta( $listing->ID, '_awpcp_sent_to_facebook_page', true ) ) {
            throw new AWPCP_Exception( __( 'The Ad was already sent to Facebook Page.', 'another-wordpress-classifieds-plugin' ) );
        }

        if ( $listing->disabled ) {
            throw new AWPCP_Exception( __( "The Ad is currently disabled. If you share it, Facebook servers and users won't be able to access it.", 'another-wordpress-classifieds-plugin' ) );
        }

        $this->do_facebook_request( $listing,
                                    '/' . $this->facebook_config->get( 'page_id' ) . '/feed',
                                    'POST' );

        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_sent_to_facebook_page', true );
    }

    private function do_facebook_request( $listing, $path, $method ) {
        $primary_image = $this->media->get_ad_primary_image( $listing );
        $primary_image_thumbnail_url = $primary_image ? $primary_image->get_url( 'primary' ) : '';

        $params = array( 'link' => url_showad( $listing->ID ),
                         'name' => $listing->get_title(),
                         'picture' =>  $primary_image_thumbnail_url );

        try {
            $response = $this->facebook_config->api_request( $path, $method, $params );
        } catch ( Exception $e ) {
            $message = __( "There was an error trying to contact Facebook servers: %s.", 'another-wordpress-classifieds-plugin' );
            $message = sprintf( $message, $e->getMessage() );
            throw new AWPCP_Exception( $message );
        }

        if ( ! $response || ! isset( $response->id ) ) {
            $message = __( 'Facebook API returned the following errors: %s.', 'another-wordpress-classifieds-plugin' );
            $message = sprintf( $message, $this->facebook_config->get_last_error()->message );
            throw new AWPCP_Exception( $message );
        }
    }

    /**
     * Users should choose Friends (or something more public), not Only Me, when the application
     * request the permission, to avoid error:
     *
     * OAuthException: (#200) Insufficient permission to post to target on behalf of the viewer.
     *
     * http://stackoverflow.com/a/19653226/201354
     */
    public function send_listing_to_facebook_group( $listing ) {
        $this->facebook_config->set_access_token( 'user_token' );

        if ( ! $this->facebook_config->is_group_set() ) {
            throw new AWPCP_Exception( 'There is no group selected.' );
        }

        if ( $this->wordpress->get_post_meta( $listing->ID, '_awpcp_sent_to_facebook_group', true ) ) {
            throw new AWPCP_Exception( __( 'The Ad was already sent to Facebook Group.', 'another-wordpress-classifieds-plugin' ) );
        }

        if ( $listing->disabled ) {
            throw new AWPCP_Exception( __( "The Ad is currently disabled. If you share it, Facebook servers and users won't be able to access it.", 'another-wordpress-classifieds-plugin' ) );
        }

        $this->do_facebook_request( $listing,
                                    '/' . $this->facebook_config->get( 'group_id' ) . '/feed',
                                    'POST' );

        $this->wordpress->update_post_meta( $listing->ID, '_awpcp_sent_to_facebook_group', true );
    }
}
