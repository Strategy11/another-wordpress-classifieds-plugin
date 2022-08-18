<?php

function awpcp_users_autocomplete() {
    return new AWPCP_UsersAutocomplete( awpcp_users_collection(), '', awpcp()->js );
}

class AWPCP_UsersAutocomplete extends AWPCP_UserField {

    private $users;
    private $javascript;

    public function __construct( $users, $void = '', $javascript  ) {
        $this->users = $users;
        $this->javascript = $javascript;
    }

    public function render( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'selected' => null,
        ) );

        $args['selected'] = $this->find_selected_user( $args );

        if ( ! empty( $args['selected'] ) ) {
            $user_info = $this->users->find_by_id( $args['selected'], array( 'ID', 'public_name' ) );
            $this->javascript->set( 'users-autocomplete-default-user', $user_info );
        }

        $template = AWPCP_DIR . '/frontend/templates/html-widget-users-autocomplete.tpl.php';

        return $this->render_template( $template, $args );
    }
}
