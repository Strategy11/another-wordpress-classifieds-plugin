<?php
/**
 * @package AWPCP\Tests\FormFields
 */

/**
 * @since 4.0.2
 */
class AWPCP_TermsOfServiceFormFieldTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.2
     */
    public function setup() {
        parent::setup();

        $this->roles             = Mockery::mock( 'AWPCP_RolesAndCapabilities' );
        $this->settings          = Mockery::mock( 'AWPCP_Settings' );
        $this->template_renderer = null;
    }

    /**
     * @since 4.0.2
     *
     * @dataProvider is_allowed_in_context_data_provider
     */
    public function test_is_allowed_in_context( $expected_result, $require_tos, $is_moderator, $context = [] ) {
        $default_context = [
            'action' => 'not-search',
        ];

        $context = $context + $default_context;

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'requiredtos' )
            ->andReturn( $require_tos );

        $this->roles->shouldReceive( 'current_user_is_moderator' )
            ->andReturn( $is_moderator );

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
                'is_moderator'    => false,
            ],
            [
                'expected_result' => false,
                'require_tos'     => true,
                'is_moderator'    => true,
            ],
            [
                'expected_result' => false,
                'require_tos'     => false,
                'is_moderator'    => false,
            ],
            [
                'expected_result' => false,
                'require_tos'     => true,
                'is_moderator'    => false,
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
            $this->roles,
            $this->settings,
            $this->template_renderer
        );
    }
}
