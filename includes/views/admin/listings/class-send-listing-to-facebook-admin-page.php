<?php

function awpcp_send_listing_to_facebook_admin_page() {
    return new AWPCP_SendListingToFacebookAdminPage( awpcp_listings_collection(), awpcp_listings_metadata(), awpcp_media_api(), AWPCP_Facebook::instance(), awpcp_request() );
}

class AWPCP_SendListingToFacebookAdminPage extends AWPCP_ListingActionAdminPage {

    private $listings_metadata;
    private $media;
    private $facebook;

    public $successful = array( 'page' => 0, 'group' => 0 );
    public $failed = array( 'page' => 0, 'group' => 0 );
    public $errors = array();

    public function __construct( $listings, $listings_metadata, $media, $facebook, $request ) {
        parent::__construct( $listings, $request );

        $this->listings_metadata = $listings_metadata;
        $this->media = $media;
        $this->facebook = $facebook;
    }

    public function dispatch() {
        $destinations = array();

        $selected_page = $this->facebook->get( 'page_id' );
        if ( ! empty( $selected_page ) ) {
            $destinations['page'] = __( 'Facebook Page', 'AWPCP' );
        }

        $selected_group = $this->facebook->get( 'group_id' );
        if ( ! empty( $selected_group ) ) {
            $destinations['group'] = __( 'Facebook Group', 'AWPCP' );
        }

        if ( empty( $destinations ) ) {
            $this->errors[] = __( "AWPCP could not post to Facebook because you haven't selected a Page or a Group.", 'AWPCP' );
        } else {
            foreach ( $this->get_selected_listings() as $listing ) {
                $this->try_to_send_listing_to_facebook( $listing, $destinations );
            }
        }

        $this->show_results();
    }

    private function try_to_send_listing_to_facebook( $listing, $destinations ) {
        if ( $listing->disabled ) {
            $message = __( "The Ad %s was not sent to Facebook because is currently disabled. If you share it, Facebook servers and users won't be able to access it.", 'AWPCP' );
            $this->errors[] = sprintf( $message, '<strong>' . $listing->get_title() . '</strong>' );
            return;
        }

        foreach ( $destinations as $destination => $label ) {
            try {
                call_user_func( array( $this, 'send_listing_to_facebook_' . $destination ), $listing );
            } catch ( AWPCP_Exception $exception ) {
                $message = _x( 'There was an error trying to send the listing %s to a %s.', '... <listing-title> to a <Facebook Group/Page>', 'AWPCP' );
                $message = sprintf( $message, '<strong>' . $listing->get_title() . '</strong>', $label );

                $this->errors[] = $message . ' ' . $exception->format_errors();
                $this->failed[ $destination ] = $this->failed[ $destination ] + 1;
            }
        }
    }

    public function send_listing_to_facebook_page( $listing ) {
        $this->facebook->set_access_token( 'page_token' );

        if ( $this->listings_metadata->get( $listing->ad_id, 'sent-to-facebook' ) ) {
            throw new AWPCP_Exception( __( 'The Ad was already sent to Facebook Page.', 'AWPCP' ) );
        }

        $this->do_facebook_request( $listing,
                                    '/' . $this->facebook->get( 'page_id' ) . '/links',
                                    'POST' );

        $this->listings_metadata->set( $listing->ad_id, 'sent-to-facebook', true );
        $this->successful['page'] = $this->successful['page'] + 1;
    }

    private function do_facebook_request( $listing, $path, $method ) {
        $primary_image = $this->media->get_ad_primary_image( $listing );
        $primary_image_thumbnail_url = $primary_image ? $primary_image->get_url( 'primary' ) : '';

        $params = array( 'link' => url_showad( $listing->ad_id ),
                         'name' => $listing->get_title(),
                         'picture' =>  $primary_image_thumbnail_url );

        try {
            $response = $this->facebook->api_request( $path, $method, $params );
        } catch ( Exception $e ) {
            $message = __( "There was an error trying to contact Facebook servers: %s.", 'AWPCP' );
            $message = sprintf( $message, $e->getMessage() );
            throw new AWPCP_Exception( $message );
        }

        if ( ! $response || ! isset( $response->id ) ) {
            $message = __( 'Facebook API returned the following errors: %s.', 'AWPCP' );
            $message = sprintf( $message, $this->facebook->get_last_error()->message );
            throw new AWPCP_Exception( $message );
        }
    }

    public function send_listing_to_facebook_group( $listing ) {
        $this->facebook->set_access_token( 'user_token' );

        if ( $this->listings_metadata->get( $listing->ad_id, 'sent-to-facebook-group' ) ) {
            throw new AWPCP_Exception( __( 'The Ad was already sent to Facebook Group.', 'AWPCP' ) );
        }

        if ( $listing->disabled ) {
            throw new AWPCP_Exception( __( "The Ad is currently disabled. If you share it, Facebook servers and users won't be able to access it.", 'AWPCP' ) );
        }

        $this->do_facebook_request( $listing,
                                    '/' . $this->facebook->get( 'group_id' ) . '/feed',
                                    'POST' );

        $this->listings_metadata->set( $listing->ad_id, 'sent-to-facebook-group', true );
        $this->successful['group'] = $this->successful['group'] + 1;
    }

    private function show_results() {
        $listings_processed = array_sum( $this->successful );
        $listings_failed = array_sum( $this->failed );

        if ( ( $listings_processed + $listings_failed ) == 0 ) {
            awpcp_flash( __( 'No Ads were selected', 'AWPCP' ), 'error' );
        } else {
            $this->show_send_to_facebook_page_results();
            $this->show_send_to_facebook_group_results();
        }

        if ( $listings_processed == 0 && $listings_failed > 0 && ! empty( $this->errors ) ) {
            $link = '<a href="' . admin_url( 'admin.php?page=awpcp-admin-settings&g=facebook-settings' ) . '">';
            $message = __( 'There were errors trying to Send Ads to Facebook, perhaps your credentials are invalid or have expired. Please check your <a>settings</a>. If your token expired, please try to get a new access token from Facebook using the link in step 2 of the settings.', 'AWPCP' );
            $this->errors[] = str_replace( '<a>', $link, $message );
        }

        foreach ( $this->errors as $error ) {
            awpcp_flash( $error, 'error' );
        }
    }

    private function show_send_to_facebook_page_results() {
        $success_message = _n( '%d Ad was sent to a Facebook Page', '%d Ads were sent to a Facebook Page', $this->successful['page'], 'AWPCP' );
        $success_message = sprintf( $success_message, $this->successful['page'] );
        $error_message = sprintf( __('there was an error trying to send %d Ads to a Facebook Page', 'AWPCP'), $this->failed['page'] );

        $this->show_bulk_operation_result_message( $this->successful['page'], $this->failed['page'], $success_message, $error_message );
    }

    private function show_send_to_facebook_group_results() {
        $success_message = _n( '%d Ad was sent to a Facebook Group', '%d Ads were sent to a Facebook Group', $this->successful['group'], 'AWPCP' );
        $success_message = sprintf( $success_message, $this->successful['group'] );
        $error_message = sprintf( __('there was an error trying to send %d Ads to a Facebook Group', 'AWPCP'), $this->failed['group'] );

        $this->show_bulk_operation_result_message( $this->successful['group'], $this->failed['group'], $success_message, $error_message );
    }
}
