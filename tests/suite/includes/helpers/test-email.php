<?php

class AWPCP_Test_Email extends AWPCP_UnitTestCase {

    public function test_send() {
        $email = Phake::partMock( 'AWPCP_Email' );
        $email->send();

        Phake::verify( $email )->send_email(
            Phake::capture( $to ),
            Phake::capture( $subject ),
            Phake::capture( $body ),
            Phake::capture( $headers ),
            Phake::capture( $attachments )
        );

        $this->assertInternalType( 'array', $headers );
    }
}
