<?php

function awpcp_users_autocomplete() {
    return new AWPCP_UsersAutocomplete( awpcp_users_collection(), awpcp_request(), awpcp()->js );
}

class AWPCP_UsersAutocomplete {

    private $users;
    private $request;
    private $javascript;

    public function __construct( $users, $request, $javascript  ) {
        $this->users = $users;
        $this->request = $request;
        $this->javascript = $javascript;
    }

    public function render( $selected_user_id = null ) {
        $current_user = $this->request->get_current_user();

        if ( $selected_user_id !== null && empty( $selected_user_id ) && $current_user ) {
            $selected_user_id = $current_user->ID;
        }

        if ( ! empty( $selected_user_id ) ) {
            $user_info = $this->users->find_by_id( $selected_user_id );
            $this->javascript->set( 'users-autocomplete-default-user', $user_info );
        }

        ob_start();
            include(AWPCP_DIR . '/frontend/templates/html-widget-users-autocomplete.tpl.php');
            $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}
