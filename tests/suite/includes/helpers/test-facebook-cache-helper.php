<?php
/**
 * @package AWPCP\Tests
 */

// @phpcs:disable Squiz.Commenting

class AWPCP_TestFacebookCacheHelper extends AWPCP_UnitTestCase {

    public function test_clear_ad_cache_uses_the_user_token_in_the_post_request() {
        $listing = Phake::mock( 'AWPCP_Ad' );

        $helper = new AWPCP_FacebookCacheHelper( null, null, null );

        // // Execution
        // $helper->clear_ad_cache( $listing );

        // Verification

        // TODO: add post() method to HTTP class and use an instance of HTTP class
        // in FacebookCacheHelper so that we can use Phake to verify the parameters
        // being sent.
        $this->markTestSkipped();
    }
}
