<?php
/**
 * @package AWPCP\Tests\Plugin\Listings
 */

use Brain\Monkey\Functions;

/**
 * Unit tests for the class that integrates with WP Query to support Classifieds
 * query parameters.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class AWPCP_QueryIntegrationTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->settings = Mockery::mock( 'AWPCP_SettingsAPI' );
        $this->db       = Mockery::mock( 'wpdb' );

        $this->db->posts = 'wp_posts';

        $this->post_type = 'awpcp_listing';

        $this->query_vars = array(
            'classifieds_query' => array(),
        );
    }

    /**
     * @since 4.0.4
     */
    public function test_parse_query() {
        $query = (object) [
            'query_vars' => [
                'classifieds_query' => [],
            ],
        ];

        // Execution.
        $this->get_test_subject()->parse_query( $query );

        // Verification.
        $this->assertEquals( $this->post_type, $query->query_vars['post_type'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts() {
        $query = (object) [
            'query_vars' => [
                'classifieds_query' => [],
            ],
        ];

        // Execution.
        $this->get_test_subject()->pre_get_posts( $query );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts_does_nothing_for_non_classifieds_queries() {
        // Anything that does not include a classifieds_query index.
        $query_vars = array();

        $query = (object) array(
            'query_vars' => $query_vars,
        );

        $query_integration = $this->get_test_subject();

        // Execution.
        $query_integration->pre_get_posts( $query );

        // Verification.
        $this->assertEquals( $query_vars, $query->query_vars );
    }

    /**
     * @since 4.0.0
     */
    public function test_normalize_sets_post_status() {
        $default_post_status = array( 'disabled', 'draft', 'pending', 'publish' );

        // Verification.
        $this->execute_normalize_method(
            function( $normalized_query_vars ) use ( $default_post_status ) {
                    $this->assertEquals( $default_post_status, $normalized_query_vars['post_status'] );
            }
        );
    }

    /**
     * @param callable $verification_callback  Function to run the verification
     *                                         part of the test.
     * @since 4.0.0
     */
    private function execute_normalize_method( $verification_callback ) {
        $query_integration = $this->get_test_subject();

        // Execution.
        $normalized_query_vars = $query_integration->normalize_query_vars( $this->query_vars );

        call_user_func( $verification_callback, $normalized_query_vars );
    }

    /**
     * @since 4.0.0
     */
    public function test_normalize_sets_post_status_if_not_defined_only() {
        $user_post_status = 'disabled';

        $this->query_vars['post_status'] = $user_post_status;

        // Verification.
        $this->execute_normalize_method(
            function( $normalized_query_vars ) use ( $user_post_status ) {
                    $this->assertEquals( $user_post_status, $normalized_query_vars['post_status'] );
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_normalize_sets_order() {
        // Verification.
        $this->execute_normalize_method(
            function( $normalized_query_vars ) {
                    $this->assertEquals( 'DESC', $normalized_query_vars['order'] );
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_normalize_sets_order_if_not_defined_only() {
        $user_order = 'ASC';

        $this->query_vars['order'] = $user_order;

        // Verification.
        $this->execute_normalize_method(
            function( $normalized_query_vars ) use ( $user_order ) {
                    $this->assertEquals( $user_order, $normalized_query_vars['order'] );
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_normalize_region_query_parameter() {
        $region_name = 'Somewhere';

        $this->query_vars['classifieds_query']['region'] = $region_name;

        $this->execute_normalize_method(
            function( $normalized_query_vars ) use ( $region_name ) {
                    $this->assertEquals( $normalized_query_vars['classifieds_query']['regions'][0]['country'], $region_name );
                    $this->assertEquals( $normalized_query_vars['classifieds_query']['regions'][1]['state'], $region_name );
                    $this->assertEquals( $normalized_query_vars['classifieds_query']['regions'][2]['city'], $region_name );
                    $this->assertEquals( $normalized_query_vars['classifieds_query']['regions'][3]['county'], $region_name );
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_normalize_type_specific_region_query_parameter() {
        $region_name = 'Somewhere';

        $this->query_vars['classifieds_query'] = array(
            'country' => $region_name,
            'state'   => $region_name,
            'city'    => $region_name,
            'county'  => $region_name,
        );

        $this->execute_normalize_method(
            function( $normalized_query_vars ) use ( $region_name ) {
                    $this->assertEquals( $normalized_query_vars['classifieds_query']['regions'][0]['country'], $region_name );
                    $this->assertEquals( $normalized_query_vars['classifieds_query']['regions'][0]['state'], $region_name );
                    $this->assertEquals( $normalized_query_vars['classifieds_query']['regions'][0]['city'], $region_name );
                    $this->assertEquals( $normalized_query_vars['classifieds_query']['regions'][0]['county'], $region_name );
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_normalize_remove_other_region_query_parameters() {
        $region_name = 'Somewhere';

        $this->query_vars['classifieds_query'] = array(
            'region'  => $region_name,
            'country' => $region_name,
            'state'   => $region_name,
            'city'    => $region_name,
            'county'  => $region_name,
        );

        $this->execute_normalize_method(
            function( $normalized_query_vars ) {
                    $this->assertArrayNotHasKey( 'region', $normalized_query_vars['classifieds_query'] );
                    $this->assertArrayNotHasKey( 'country', $normalized_query_vars['classifieds_query'] );
                    $this->assertArrayNotHasKey( 'state', $normalized_query_vars['classifieds_query'] );
                    $this->assertArrayNotHasKey( 'city', $normalized_query_vars['classifieds_query'] );
                    $this->assertArrayNotHasKey( 'county', $normalized_query_vars['classifieds_query'] );
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_normalize_remove_empty_regions_query_parameters() {
        $this->query_vars['classifieds_query']['regions'] = [
            [
                'country' => '',
                'state'   => '',
                'city'    => '',
            ],
        ];

        $this->execute_normalize_method(
            function( $normalized_query_vars ) {
                    $this->assertEquals( [], $normalized_query_vars['classifieds_query']['regions'] );
            }
        );
    }

    /**
     * @dataProvider query_paramaters_that_require_is_valid
     * @param stirng $query_parameter   The name of the parameter to test.
     * @since 4.0.0
     */
    public function test_normalize_adds_is_valid_query_parameter( $query_parameter ) {
        $this->query_vars['classifieds_query'][ $query_parameter ] = true;

        $this->execute_normalize_method(
            function( $normalized_query_vars ) {
                    $this->assertTrue( $normalized_query_vars['classifieds_query']['is_valid'] );
            }
        );
    }

    /**
     * @since 4.0.0
     */
    public function query_paramaters_that_require_is_valid() {
        return [
            [ 'is_new' ],
            [ 'is_expired' ],
            [ 'is_about_to_expire' ],
            [ 'is_enabled' ],
            [ 'is_disabled' ],
            [ 'is_awaiting_approval' ],
            [ 'has_images_awaiting_approval' ],
            [ 'is_featured' ],
            [ 'is_flagged' ],
        ];
    }

    /**
     * @since 4.0.0
     */
    public function test_normalize_is_valid_query_parameter() {
        $this->query_vars['classifieds_query']['is_valid'] = true;

        $this->execute_normalize_method(
            function( $normalized_query_vars ) {
                    $this->assertArrayHasKey( 'is_verified', $normalized_query_vars['classifieds_query'] );
                    $this->assertArrayHasKey( 'is_successfully_paid', $normalized_query_vars['classifieds_query'] );
            }
        );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_QueryIntegration(
            $this->post_type,
            'awpcp_category',
            $this->settings,
            $this->db
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_verified_query_parameter() {
        $this->query_vars['classifieds_query']['is_verified'] = true;

        $query_vars = $this->process_query_parameters();
        $meta_keys  = $this->get_meta_keys( $query_vars );

        // Verification.
        $this->assertContains( '_awpcp_verified', $meta_keys );
    }

    /**
     * @since 4.0.0
     */
    private function process_query_parameters() {
        $query_integration = $this->get_test_subject();

        // Execution.
        $query_vars = $query_integration->process_query_parameters( $this->query_vars );

        // Make sure the original query is returned.
        $this->assertArrayHasKey( 'classifieds_query', $query_vars );

        return $query_vars;
    }

    /**
     * @param array $query_vars     The full array of query vars to extract
     *                                  the meta keys from.
     * @since 4.0.0
     */
    private function get_meta_keys( $query_vars ) {
        return array_map(
            function( $item ) {
                    return $item['key'];
            },
            $query_vars['meta_query']
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_successfully_paid_query_parameter() {
        $this->query_vars['classifieds_query']['is_successfully_paid'] = true;

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'enable-ads-pending-payment' )
            ->andReturn( false );

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'freepay' )
            ->andReturn( 1 );

        $query_vars = $this->process_query_parameters();
        $meta_keys  = $this->get_meta_keys( $query_vars );

        // Verification.
        $this->assertContains( '_awpcp_payment_status', $meta_keys );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_new_query_parameter() {
        $this->query_vars['classifieds_query']['is_new'] = true;

        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( '_awpcp_content_needs_review', $query_vars['meta_query'][0]['key'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_disabled_query_parameter() {
        $this->query_vars['classifieds_query']['is_disabled'] = true;

        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( [ 'disabled', 'pending' ], $query_vars['post_status'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_enabled_query_parameter() {
        $this->query_vars['classifieds_query']['is_enabled'] = true;

        Functions\when( 'current_time' )->justReturn( 1 );

        $query_vars = $this->process_query_parameters();
        $meta_keys  = $this->get_meta_keys( $query_vars );

        // Verification.
        $this->assertEquals( 'publish', $query_vars['post_status'] );
        $this->assertContains( '_awpcp_start_date', $meta_keys );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_about_to_expire_query_parameter() {
        $this->query_vars['classifieds_query']['is_about_to_expire'] = true;

        $this->settings->shouldReceive( 'get_option' )
            ->with( 'ad-renew-email-threshold' )
            ->andReturn( 1 );

        Functions\when( 'current_time' )->justReturn( 1 );

        $query_vars = $this->process_query_parameters();
        $meta_keys  = $this->get_meta_keys( $query_vars );

        // Verification.
        $this->assertContains( '_awpcp_end_date', $meta_keys );
        $this->assertContains( '_awpcp_renew_email_sent', $meta_keys );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_expired_query_parameter() {
        $this->query_vars['classifieds_query']['is_expired'] = true;

        Functions\when( 'current_time' )->justReturn( 1 );

        $query_vars = $this->process_query_parameters();
        $meta_keys  = $this->get_meta_keys( $query_vars );

        // Verification.
        $this->assertContains( '_awpcp_expired', $meta_keys );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_awaiting_approval_query_parameter() {
        $this->query_vars['classifieds_query']['is_awaiting_approval'] = true;

        $query_vars = $this->process_query_parameters();
        $meta_keys  = $this->get_meta_keys( $query_vars );

        // Verification.
        $this->assertEquals( 'disabled', $query_vars['post_status'] );
        $this->assertContains( '_awpcp_disabled_date', $meta_keys );
        $this->assertEquals( 'NOT EXISTS', $query_vars['meta_query'][0]['compare'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_has_images_awaiting_approval_query_parameter() {
        $this->query_vars['classifieds_query']['has_images_awaiting_approval'] = true;

        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( '_awpcp_has_images_awaiting_approval', $query_vars['meta_query'][0]['key'] );
        $this->assertEquals( 'EXISTS', $query_vars['meta_query'][0]['compare'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_awaiting_verification_query_parameter() {
        $this->query_vars['classifieds_query']['is_awaiting_verification'] = true;

        $query_vars = $this->process_query_parameters();
        $meta_keys  = $this->get_meta_keys( $query_vars );

        // Verification.
        $this->assertContains( '_awpcp_verification_needed', $meta_keys );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_flagged_query_parameter() {
        $this->query_vars['classifieds_query']['is_flagged'] = true;

        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( '_awpcp_flagged', $query_vars['meta_query'][0]['key'] );
        $this->assertEquals( 'EXISTS', $query_vars['meta_query'][0]['compare'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_is_incomplete_query_parameter() {
        $this->query_vars['classifieds_query']['is_incomplete'] = true;

        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( '_awpcp_payment_status', $query_vars['meta_query'][0]['key'] );
        $this->assertEquals( 'Unpaid', $query_vars['meta_query'][0]['value'] );
        $this->assertEquals( '=', $query_vars['meta_query'][0]['compare'] );
        $this->assertEquals( 'CHAR', $query_vars['meta_query'][0]['type'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_previous_id_query_parameter() {
        $previous_id = wp_rand() + 1;

        $this->query_vars['classifieds_query']['previous_id'] = $previous_id;

        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( "_awpcp_old_id_{$previous_id}", $query_vars['meta_query'][0]['key'] );
        $this->assertEquals( 'EXISTS', $query_vars['meta_query'][0]['compare'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_category_query_parameter() {
        $tax_query = $this->check_terms_support( 'category' );

        $this->assertTrue( $tax_query['include_children'] );
    }

    /**
     * @param string $parameter_name     Name of the parameter to test.
     * @since 4.0.0
     */
    private function check_terms_support( $parameter_name ) {
        $tax_query = $this->check_parameter_accepts_integers( $parameter_name );
        $tax_query = $this->check_parameter_converts_terms_to_int( $parameter_name );
        $tax_query = $this->check_parameter_does_not_accept_string_terms( $parameter_name );

        return $tax_query;
    }

    /**
     * @param string $parameter_name     Name of the parameter to test.
     * @since 4.0.0
     */
    private function check_parameter_accepts_integers( $parameter_name ) {
        $term_id = wp_rand() + 1;

        $tax_query = $this->process_tax_query_parameter( $parameter_name, $term_id );

        $this->assertEquals( array( $term_id ), $tax_query['terms'] );

        return $tax_query;
    }

    /**
     * @param string $parameter_name     Name of the parameter to test.
     * @param mixed  $terms              Search terms.
     * @since 4.0.0
     */
    private function process_tax_query_parameter( $parameter_name, $terms ) {
        $this->query_vars['classifieds_query'][ $parameter_name ] = $terms;

        $query_vars = $this->process_query_parameters();
        $tax_query  = $query_vars['tax_query'][0];

        return $tax_query;
    }

    /**
     * @param string $parameter_name     Name of the parameter to test.
     * @since 4.0.0
     */
    private function check_parameter_converts_terms_to_int( $parameter_name ) {
        $term_id = sprintf( '%d', wp_rand() + 1 );

        $tax_query = $this->process_tax_query_parameter( $parameter_name, array( $term_id ) );

        // Verification.
        $this->assertEquals( array( intval( $term_id ) ), $tax_query['terms'] );

        return $tax_query;
    }

    /**
     * @param string $parameter_name     Name of the parameter to test.
     * @since 4.0.0
     */
    private function check_parameter_does_not_accept_string_terms( $parameter_name ) {
        $term_id = 'term-slug';

        $tax_query = $this->process_tax_query_parameter( $parameter_name, array( $term_id ) );

        // Verification.
        $this->assertEquals( array(), $tax_query['terms'] );

        return $tax_query;
    }

    /**
     * @since 4.0.0
     */
    public function test_category__not_in_query_parameter() {
        $tax_query = $this->check_terms_support( 'category__not_in' );

        $this->assertTrue( $tax_query['include_children'] );
        $this->assertEquals( 'NOT IN', $tax_query['operator'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_category__exclude_children_query_parameter() {
        $tax_query = $this->check_terms_support( 'category__exclude_children' );

        $this->assertFalse( $tax_query['include_children'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_contact_name_query_parameter() {
        $this->query_vars['classifieds_query']['contact_name'] = 'Peter';

        // Execution.
        $query_vars = $this->process_query_parameters();
        $meta_keys  = $this->get_meta_keys( $query_vars );

        // Verification.
        $this->assertContains( '_awpcp_contact_name', $meta_keys );
        $this->assertEquals( 'LIKE', $query_vars['meta_query'][0]['compare'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_contact_phone_query_parameter() {
        $phone_number_digits = '3166326198';

        $this->query_vars['classifieds_query']['contact_phone'] = '(316) 632 61 98';

        Functions\when( 'awpcp_get_digits_from_string' )->justReturn( $phone_number_digits );

        // Execution.
        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( '_awpcp_contact_phone_number_digits', $query_vars['meta_query'][0]['key'] );
        $this->assertEquals( $phone_number_digits, $query_vars['meta_query'][0]['value'] );
        $this->assertEquals( 'LIKE', $query_vars['meta_query'][0]['compare'] );
    }

    /**
     * @dataProvider price_query_parameter_provider
     * @param string $parameter_name     Name of the parameter to test.
     * @param string $expected_operator  Expected compare operator.
     */
    public function test_price_query_parameter( $parameter_name, $expected_operator ) {
        $this->query_vars['classifieds_query'][ $parameter_name ] = 9.95;

        // Execution.
        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( '_awpcp_price', $query_vars['meta_query'][0]['key'] );
        $this->assertEquals( $expected_operator, $query_vars['meta_query'][0]['compare'] );
        $this->assertEquals( 995, $query_vars['meta_query'][0]['value'] );
    }

    /**
     * @since 4.0.0
     */
    public function price_query_parameter_provider() {
        return [
            [ 'price', '=' ],
            [ 'min_price', '>=' ],
            [ 'max_price', '<=' ],
        ];
    }

    /**
     * @dataProvider payment_status_query_parameter_provider
     * @param string $parameter_name     Name of the parameter to test.
     * @param mixed  $search_value       Value to search for.
     * @param string $expected_operator  Expected compare operator.
     * @since 4.0.0
     */
    public function test_payment_status_query_parameters( $parameter_name, $search_value, $expected_operator ) {
        $this->query_vars['classifieds_query'][ $parameter_name ] = $search_value;

        // Execution.
        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( '_awpcp_payment_status', $query_vars['meta_query'][0]['key'] );
        $this->assertEquals( $expected_operator, $query_vars['meta_query'][0]['compare'] );
    }

    /**
     * @since 4.0.0
     */
    public function payment_status_query_parameter_provider() {
        return [
            [ 'payment_status', 'Commplete', '=' ],
            [ 'payment_status', [ 'Commplete' ], 'IN' ],
            [ 'payment_status__not_in', 'Commplete', 'NOT IN' ],
        ];
    }

    /**
     * @since 4.0.0
     */
    public function test_payment_email_query_parameter() {
        $this->query_vars['classifieds_query']['payer_email'] = 'jonh@example.org';

        // Execution.
        $query_vars = $this->process_query_parameters();

        // Verification.
        $this->assertEquals( '_awpcp_payer_email', $query_vars['meta_query'][0]['key'] );
        $this->assertEquals( $this->query_vars['classifieds_query']['payer_email'], $query_vars['meta_query'][0]['value'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_posts_where_for_title_query_parameter() {
        $search_term         = 'search-term';
        $escaped_search_term = 'escaped-search-term';

        $escaped_sql = 'escaped-SQL';

        $where = '';
        $query = (object) [
            'query_vars' => [
                'classifieds_query' => [
                    'title' => $search_term,
                ],
            ],
        ];

        $this->db->shouldReceive( 'esc_like' )
            ->once()
            ->with( $search_term )
            ->andReturn( $escaped_search_term );

        $this->db->shouldReceive( 'prepare' )
            ->once()
            ->with( Mockery::pattern( '/ AND \w+\.post_title LIKE %s/' ), "%$escaped_search_term%" )
            ->andReturn( $escaped_sql );

        // Execution.
        $where = $this->get_test_subject()->posts_where( $where, $query );

        // Verification.
        $this->assertContains( $escaped_sql, $where );
    }
}
