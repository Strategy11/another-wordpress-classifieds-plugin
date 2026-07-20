<?php
/**
 * @package AWPCP\Tests\FormFields
 */

/**
 * @since 4.0.2
 */
class AWPCP_TermsOfServiceFormFieldTest extends AWPCP_UnitTestCase {

    /**
     * @var mixed
     */
    public $settings;

    /**
     * @var mixed
     */
    public $template_renderer;

    /**
     * @since 4.0.2
     */
    public function setUp(): void {
        parent::setUp();

        $this->settings          = Mockery::mock( 'AWPCP_Settings' );
        $this->template_renderer = null;
    }

    /**
     * @since 4.0.2
     *
     * @dataProvider is_allowed_in_context_data_provider
     */
    public function test_is_allowed_in_context( $expected_result, $require_tos, $context = [] ) {
        $default_context = [
            'action' => 'not-search',
        ];

        $context = $context + $default_context;

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'requiredtos' )
            ->andReturn( $require_tos );

        // Execution.
        $is_allowed = $this->get_test_subject()->is_allowed_in_context( $context );

        // Verification.
        $this->assertSame( $expected_result, $is_allowed );
    }

    /**
     * @since 4.0.2
     */
    public function is_allowed_in_context_data_provider() {
        return [
            [
                'expected_result' => true,
                'require_tos'     => true,
            ],
            [
                'expected_result' => false,
                'require_tos'     => false,
            ],
            [
                'expected_result' => false,
                'require_tos'     => true,
                'context'         => [ 'action' => 'search' ],
            ],
        ];
    }

    /**
     * @since 4.0.2
     */
    private function get_test_subject() {
        return new AWPCP_TermsOfServiceFormField(
            'slug',
            $this->settings,
            $this->template_renderer
        );
    }
}
