<?php

function awpcp_users_field() {
    if ( get_awpcp_option( 'user-field-widget' ) == 'dropdown' ) {
        return awpcp_users_dropdown();
    } else {
        return awpcp_users_autocomplete();
    }
}

function awpcp_users_dropdown() {
    return new AWPCP_UsersDropdown( awpcp_users_collection(), awpcp_request() );
}

class AWPCP_UsersDropdown {

    private $users;
    private $request;

    public function __construct( $users, $request ) {
        $this->users = $users;
        $this->request = $request;
    }

    public function render( $selected_user_id = null ) {
        $current_user = $this->request->get_current_user();

        if ( $selected_user_id !== null && empty( $selected_user_id ) && $current_user ) {
            $selected_user_id = $current_user->ID;
        }

        $users = $this->get_users_information();

        ob_start();
            include(AWPCP_DIR . '/frontend/templates/html-widget-users-dropdown.tpl.php');
            $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    private function get_users_information() {
        if ( true ) {
            return $this->users->get_users_with_full_information();
        } else {
            return $this->users->get_users_with_basic_information();
        }
    }
}
