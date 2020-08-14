<?php

class AWPCP_TestListingAttachmentCreator extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->markTestIncomplete( 'Test not yet implement.' );
    }

    public function test_attachment_builder() {
        $listing = (object) array( 'ID' => rand() + 1 );

        $attachment_builder = new AWPCP_AttachmentBuilder();
        $attachment_builder->set_listing( $listing );
        $attachment_builder->set_file( $file );
        $attachment = $attachment_builder->make();

        $this->assertTrue( ! is_null( $attachment ) );

        $this->markTestIncomplete( 'Test not yet implement.' );
    }

    public function test_create_attachment_from_uploaded_file() {
        $listing = (object) array( 'ID' =>13 );

        $file = $this->getMockBuilder( 'AWPCP_File' )
                     ->setConstructorArgs( array( 'test-image.png', 'test-image.png', 'image/png' ) )
                     ->getMock();

        $file_mover = $this->createPartialMock( 'AWPCP_UploadedFileMover', array( 'move' ) );
        $file_mover->expects( $this->once() )
                   ->method( 'move' )
                   ->will( $this->returnValue( $file ) );

        $attachment_creator = new AWPCP_ListingAttachmentCreator( $listing, array(), $file_mover );
        $attachment = $attachment_creator->create( $file );

        $this->assertEquals( $listing->ID, $attachment->ad_id );
        $this->assertEquals( $file->name, $attachment->name );
        $this->assertEquals( $file->mime_type, $attachment->mime_type );

        $this->markTestIncomplete( 'Test not yet implement.' );
    }

    public function test_throws_an_exception_when_a_validator_fails() {
        $listing = (object) array( 'ID' => rand() + 1 );
        $file = $this->getMock( 'AWPCP_File' );
        $file_mover = $this->getMock( 'AWPCP_UploadedFileMover' );
        $validator = new AWPCP_FailedFileValidator();

        $this->setExpectedException( 'AWPCP_Exception' );

        $attachment_creator = new AWPCP_ListingAttachmentCreator( $listing, array( $validator ), $file_mover );
        $attachment_creator->create( $file );

        $this->markTestIncomplete( 'Test not yet implement.' );
    }

    public function test_orphan_files_are_removed_when_an_exception_is_thrown() {
        $this->markTestIncomplete( 'Test not yet implement.' );
    }
}

// class AWPCP_FailedFileValidator implements AWPCP_FileValidator {
//     public function validate( AWPCP_File $file, AWPCP_Ad $listing ) {
//         throw new AWPCP_Exception( 'FailedFileValidator' );
//     }
// }
