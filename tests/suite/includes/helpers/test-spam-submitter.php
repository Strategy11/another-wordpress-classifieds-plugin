<?php
/**
 * @package AWPCP\Tests\Plugin\Helpers
 */

/**
 * Tests for Spam Submitter class.
 */
class AWPCP_TestSpamSubmitter extends AWPCP_UnitTestCase {

    /**
     * Prepares mock objects before each test.
     */
    public function setup() {
        parent::setup();

        $this->subject = new stdClass();

        $this->akismet = Phake::mock( 'AWPCP_AkismetWrapper' );
        Phake::when( $this->akismet )->get_user_data()->thenReturn( array( 'user_data' => true ) );
        Phake::when( $this->akismet )->get_reporter_data()->thenReturn( array( 'reporter_data' => true ) );

        $this->akismet_factory = Mockery::mock( 'AWPCP_AkismetWrapperFactory' );

        $this->akismet_factory->shouldReceive( 'get_akismet_wrapper' )->andReturn( $this->akismet );

        $this->data_source = Phake::mock( 'AWPCP_ListingAkismetDataSource' );
        Phake::when( $this->data_source )->get_request_data( $this->subject )->thenReturn( array( 'subject_data' => true ) );
    }

    /**
     * Test submit() when the request is successful.
     */
    public function test_submit() {
        $akismet_response = array( null, 'Thanks for making the web a better place.' );
        Phake::when( $this->akismet )->http_post( Phake::anyParameters() )->thenReturn( $akismet_response );

        $spam_filter = new AWPCP_SpamSubmitter( $this->akismet_factory, $this->data_source );

        $this->assertTrue( $spam_filter->submit( $this->subject ) );

        Phake::verify( $this->akismet )->http_post( Phake::capture( $post_data ), Phake::capture( $api_method ) );

        $this->assertContains( 'user_data', $post_data );
        $this->assertContains( 'reporter_data', $post_data );
        $this->assertContains( 'subject_data', $post_data );

        $this->assertEquals( 'submit-spam', $api_method );
    }

    /**
     * Test submit() when the request fails.
     */
    public function test_unsuccesful_submit() {
        Phake::when( $this->akismet )->http_post( Phake::anyParameters() )->thenReturn( array( null, 'whatever!' ) );

        $spam_filter = new AWPCP_SpamSubmitter( $this->akismet_factory, $this->data_source );

        $this->assertFalse( $spam_filter->submit( $this->subject ) );
    }
}
