<?php

function awpcp_update_attachment_allowed_status_ajax_handler() {
    $attachment_action = new AWPCP_Update_Attachment_Allowed_Status_Ajax_Action(
        awpcp_attachments_logic(),
        awpcp_request()
    );

    return awpcp_attachment_action_ajax_handler( $attachment_action );
}

class AWPCP_Update_Attachment_Allowed_Status_Ajax_Action implements AWPCP_Attachment_Ajax_Action {

    private $attachments_logic;
    private $request;

    public function __construct( $attachments_logic, $request ) {
        $this->attachments_logic = $attachments_logic;
        $this->request = $request;
    }

    public function do_action( $ajax_handler, $attachment, $listing ) {
        if ( $this->request->param( 'action' ) == 'awpcp-approve-file' ) {
            return $this->attachments_logic->approve_attachment( $attachment );
        } else {
            return $this->attachments_logic->reject_attachment( $attachment );
        }
    }
}
