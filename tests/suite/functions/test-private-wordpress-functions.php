<?php

/**
 * @group core
 */
class AWPCP_TestPrivateWordPressFunctions extends AWPCP_UnitTestCase {

    public function test_deprecated_function() {
        $this->assertTrue( function_exists( '_deprecated_function' ) );
    }
}
