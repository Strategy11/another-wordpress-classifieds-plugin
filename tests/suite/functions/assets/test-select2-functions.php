<?php
/**
 * @package AWPCP\Tests\Functions
 */

/**
 * @since 4.0.2
 */
class AWPCP_Select2FunctionsTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.2
     *
     * @dataProvider should_register_select2_script_data_provider
     */
    public function test_should_register_select2_script( $should, $wc_version ) {
        if ( ! is_null( $wc_version ) ) {
            $this->redefine( 'defined', Patchwork\always( true ) );
            $this->redefine( 'constant', Patchwork\always( $wc_version ) );
        }

        $this->assertSame( $should, awpcp_should_register_select2_script() );
    }

    /**
     * @since 4.0.2
     */
    public function should_register_select2_script_data_provider() {
        return [
            [
                true,
                null,
            ],
            [
                true,
                '3.1.9',
            ],
            [
                false,
                '3.2.0',
            ],
            [
                false,
                '3.6.5',
            ],
        ];
    }
}
