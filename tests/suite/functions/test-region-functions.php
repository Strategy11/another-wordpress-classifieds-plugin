<?php

/**
 * @group core
 */
class AWPCP_TestRegionFunctions extends AWPCP_UnitTestCase {

    function test_default_region_fields() {
        foreach ( array( 'details', 'search' ) as $context ) {
            foreach ( awpcp_default_region_fields( $context ) as $field ) {
                $this->assertTrue( $field['alwaysShown'] );
            }
        }
    }
}
