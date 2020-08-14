<?php

class AWPCP_Test_Listings_Media_Uploader_Component extends AWPCP_UnitTestCase {

    public function test_render() {
        $media_uploader_component = Phake::mock( 'AWPCP_MediaUploaderComponent' );
        $validation_errors = Phake::mock( 'AWPCP_FileValidationErrors' );
        $javascript = Phake::mock( 'AWPCP_JavaScript' );

        $content = 'Whatever';
        $configuration = array();

        Phake::when( $media_uploader_component )->render->thenReturn( $content );

        $component = new AWPCP_Listings_Media_Uploader_Component(
            $media_uploader_component,
            $validation_errors,
            $javascript
        );

        $rendered_content = $component->render( $configuration );

        $this->assertEquals( $rendered_content, $content );
    }
}
