<?php

class AWPCP_TestStringFunctions extends AWPCP_UnitTestCase {

    public function test_utf8_strlen() {
        $this->assertEquals( 5, awpcp_utf8_strlen( 'כרטיס' ) );
        $this->assertEquals( 5, awpcp_utf8_strlen( 'hello' ) );
    }

    public function test_utf8_substr() {
        $this->check_utf8_substr_function( 'awpcp_utf8_substr' );
    }

    public function test_utf8_substr_pcre() {
        $this->check_utf8_substr_function( 'awpcp_utf8_substr_pcre' );
    }

    private function check_utf8_substr_function( $fn ) {
        $this->assertEquals( 'ell', call_user_func( $fn, 'hello', 1, 3 ) );
        $this->assertEquals( 'כרטיס עסק מס', call_user_func( $fn, 'כרטיס עסק מסעדה לדוגמה כרטיס עסק מסעדה לדוגמה כרטיס עס', 0, 12 ) );
        $this->assertEquals( 'מסעדה לדוגמה', call_user_func( $fn, 'כרטיס עסק מסעדה לדוגמה כרטיס עסק מסעדה לדוגמה כרטיס עס', 10, 12 ) );

        $this->assertEquals( 'ello', call_user_func( $fn, 'hello', 1 ) );
        $this->assertEquals( 'מסעדה לדוגמה כרטיס עסק מסעדה לדוגמה כרטיס עס', call_user_func( $fn, 'כרטיס עסק מסעדה לדוגמה כרטיס עסק מסעדה לדוגמה כרטיס עס', 10 ) );
    }
}
