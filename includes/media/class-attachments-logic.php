<?php

function awpcp_attachments_logic() {
    return new AWPCP_Attachments_Logic( awpcp_wordpress() );
}

class AWPCP_Attachments_Logic {

    private $wordpress;

    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    public function approve_attachment(  ) {
        return $this->wordpress->update_post_meta(array(
            'ID' => $attachment->ID,
            '_allowed_status' => AWPCP_Attachment_Status::STATUS_APPROVED,
        ));
    }
}
