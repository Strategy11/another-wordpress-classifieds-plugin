<?php

class AWPCP_Test_Formatting_Functions extends AWPCP_UnitTestCase {

    public function test_format_number() {
        awpcp()->settings->set_or_update_option( 'thousands-separator', ' ' );
        awpcp()->settings->set_or_update_option( 'decimal-separator', '.' );

        $formatted = awpcp_format_number( 1234.56 );

        $this->assertEquals( '1 234.56', $formatted );
    }

    public function test_format_money_uses_non_breaking_space_to_separate_currency_symbol_from_amount() {
        $nbsp = ' '; // U+00A0

        awpcp()->settings->set_or_update_option( 'currency-code', 'USD' );
        awpcp()->settings->set_or_update_option( 'thousands-separator', ',' );
        awpcp()->settings->set_or_update_option( 'decimal-separator', '.' );
        awpcp()->settings->set_or_update_option( 'include-space-between-currency-symbol-and-amount', true );

        $formatted = awpcp_format_money( 1234.56 );

        $this->assertEquals( "\$${nbsp}1,234.56", $formatted );
    }

    public function test_format_money() {
        $nbsp = ' '; // U+00A0

        awpcp()->settings->set_or_update_option( 'currency-code', 'USD' );
        awpcp()->settings->set_or_update_option( 'thousands-separator', $nbsp );
        awpcp()->settings->set_or_update_option( 'decimal-separator', '.' );

        $formatted = awpcp_format_money( 1234.56 );

        $this->assertEquals( "\$${nbsp}1${nbsp}234.56", $formatted );
    }

    public function test_get_digits_from_string() {
        $this->assertEquals( '6663018372941' , awpcp_get_digits_from_string( '(666) 301-837-2941' ) );
    }

    public function test_trim_html_content() {
        $content = 'Welcome to <strong>WordPress</strong>. Click here to go to <a href="https://google.com">Google</a>.';
        $trimmed_content = awpcp_trim_html_content( $content, 5 );

        $this->assertEquals( 'Welcome to <strong>WordPress</strong>.', $trimmed_content );
    }
}
