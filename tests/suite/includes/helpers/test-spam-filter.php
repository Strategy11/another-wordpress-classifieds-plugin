<?php

class AWPCP_TestSpamFilter extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->subject = new stdClass();

        $this->akisment = Phake::mock( 'AWPCP_AkismetWrapper' );
        Phake::when( $this->akisment )->get_user_data()->thenReturn( array( 'user_data' => true ) );

        $this->data_source = Phake::mock( 'AWPCP_ListingAkismetDataSource' );
        Phake::when( $this->data_source )->get_request_data( $this->subject )->thenReturn( array( 'subject_data' => true ) );
    }

    public function test_is_spam() {
        Phake::when( $this->akisment )->http_post( Phake::anyParameters() )->thenReturn( array( null, true ) );

        $spam_filter = new AWPCP_SpamFilter( $this->akisment, $this->data_source );

        $this->assertTrue( $spam_filter->is_spam( $this->subject ) );

        Phake::verify( $this->akisment )->http_post( Phake::capture( $post_data ), Phake::capture( $api_method ) );

        $this->assertContains( 'user_data', $post_data );
        $this->assertContains( 'subject_data', $post_data );

        $this->assertEquals( 'comment-check', $api_method );
    }

    public function test_is_spam_with_ham() {
        Phake::when( $this->akisment )->http_post( Phake::anyParameters() )->thenReturn( array( null, false ) );

        $spam_filter = new AWPCP_SpamFilter( $this->akisment, $this->data_source );

        $this->assertFalse( $spam_filter->is_spam( $this->subject ) );
    }
}
