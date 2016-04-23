<?php

class AWPCP_TableEntryActionAjaxHandler extends AWPCP_AjaxHandler {

    protected $page;
    protected $request;

    public function __construct( $page, $request, $response ) {
        parent::__construct( $response );

        $this->page = $page;
        $this->request = $request;
    }

    public function ajax() {
        if ( ! awpcp_current_user_is_admin() ) {
            return $this->error_response( __( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ) );
        }

        return $this->process_entry_action();
    }

    protected function process_entry_action() {
        return false;
    }
}
