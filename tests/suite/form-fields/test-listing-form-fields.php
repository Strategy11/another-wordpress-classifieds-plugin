<?php
/**
 * @package AWPCP\Tests\FormFields
 */

/**
 * @since 4.0.2
 */
class AWPCP_ListingFormFieldsTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.2
     */
    public function setup() {
        parent::setup();

        $this->authorization = null;
    }

    /**
     * @since 4.0.2
     *
     * @dataProvider terms_of_service_field_data_provider
     */
    public function test_terms_of_service_field( $fields_definitions, $fields_order, $position ) {
        $this->redefine(
            'AWPCP_ListingFormFields::get_listing_form_fields',
            Patchwork\always( $fields_definitions )
        );

        $this->redefine(
            'AWPCP_ListingFormFields::get_fields_order',
            Patchwork\always( $fields_order )
        );

        // Execution.
        $fields = $this->get_test_subject()->register_listing_details_form_fields( [] );

        // Verification.
        $this->assertEquals(
            $position,
            array_search( 'terms_of_service', array_keys( $fields ), true )
        );
    }

    /**
     * @since 4.0.2
     */
    public function terms_of_service_field_data_provider() {
        $fields_definitions = [
            'foo-field'        => function() {
                return (object) [];
            },
            'terms_of_service' => function() {
                return (object) [];
            },
            'bar-field'        => function() {
                return (object) [];
            },
        ];

        return [
            // Show the Terms of Service field last.
            [
                $fields_definitions,
                [],
                count( $fields_definitions ) - 1,
            ],
            // Unless it is explicitly configured to appear in a different
            // position.
            [
                $fields_definitions,
                [ 'terms_of_service', 'bar_field' ],
                0,
            ],
        ];
    }
    /**
     * @since 4.0.2
     */
    private function get_test_subject() {
        return new AWPCP_ListingFormFields( $this->authorization );
    }
}
