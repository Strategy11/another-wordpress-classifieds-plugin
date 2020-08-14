<?php
/**
 * @package AWPCP\Tests
 */

/**
 * Unit tests for the Rewrite Rules Helper class.
 */
class AWPCP_TestRewriteRulesHelper extends AWPCP_UnitTestCase {

    public function test_generate_page_uri_variants() {
        $uri = strtolower( urlencode( 'доска-объявлений/browse-categories/1/general' ) );

        $helper = new AWPCP_Rewrite_Rules_Helper();

        // Execution
        $variants = $helper->generate_page_uri_variants( $uri );

        // Verificatiol
        $this->assertEquals( 2, count( $variants ) );
    }
}
