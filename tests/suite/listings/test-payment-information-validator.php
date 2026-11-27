<?php
/**
 * @package AWPCP\Tests
 */

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * Server-side checks for the categories and payment term of a listing.
 */
class AWPCP_PaymentInformationValidatorTest extends AWPCP_UnitTestCase {

    /**
     * @var \Mockery\MockInterface
     */
    public $categories;

    /**
     * @var \Mockery\MockInterface
     */
    public $payments;

    /**
     * @var \Mockery\MockInterface
     */
    public $roles;

    /**
     * @since x.x
     */
    public function setUp(): void {
        parent::setUp();

        $this->categories = Mockery::mock( 'AWPCP_Categories_Collection' );
        $this->payments   = Mockery::mock( 'AWPCP_PaymentsAPI' );
        $this->roles      = Mockery::mock( 'AWPCP_RolesAndCapabilities' );

        $this->payments->shouldReceive( 'get_payment_term' )->andReturn(
            (object) array(
                'id'      => 3,
                'type'    => 'fee',
                'private' => false,
            )
        );

        $this->roles->shouldReceive( 'current_user_is_administrator' )->andReturn( false );
        $this->roles->shouldReceive( 'current_user_is_moderator' )->andReturn( false );

        Functions\when( 'get_awpcp_option' )->justReturn( false );
    }

    /**
     * @since x.x
     */
    public function test_rejects_more_categories_than_the_default_limit() {
        $errors = $this->get_test_subject()->get_validation_errors( $this->get_data( array( 2, 3 ) ) );

        $this->assertArrayHasKey( 'categories', $errors );
    }

    /**
     * @since x.x
     *
     * @dataProvider allowed_selections
     *
     * @param int   $allowed    Number of categories the payment term allows.
     * @param array $categories Selected category IDs.
     */
    public function test_accepts_selections_within_the_limit( $allowed, $categories ) {
        Filters\expectApplied( 'awpcp-number-of-categories-allowed-in-post-listing-order-step' )->andReturn( $allowed );

        $errors = $this->get_test_subject()->get_validation_errors( $this->get_data( $categories ) );

        $this->assertSame( array(), $errors );
    }

    /**
     * @since x.x
     */
    public function allowed_selections() {
        return array(
            'one category, default limit'      => array( 1, array( 2 ) ),
            'two categories, limit 2'          => array( 2, array( 2, 3 ) ),
            'zero means unlimited'             => array( 0, array( 2, 3, 4, 5 ) ),
            'duplicates are counted once'      => array( 1, array( 2, '2' ) ),
        );
    }

    /**
     * @since x.x
     */
    public function test_rejects_more_categories_than_a_payment_term_limit_of_two() {
        Filters\expectApplied( 'awpcp-number-of-categories-allowed-in-post-listing-order-step' )->andReturn( 2 );

        $errors = $this->get_test_subject()->get_validation_errors( $this->get_data( array( 2, 3, 4 ) ) );

        $this->assertArrayHasKey( 'categories', $errors );
    }

    /**
     * @since x.x
     */
    public function test_keeps_the_required_categories_error() {
        $errors = $this->get_test_subject()->get_validation_errors( $this->get_data( array() ) );

        $this->assertSame( 'Ad Category field is required', $errors['categories'] );
    }

    /**
     * @since x.x
     *
     * @param array $categories Selected category IDs.
     */
    private function get_data( $categories ) {
        return array(
            'terms'       => array(
                'awpcp_listing_category' => $categories,
            ),
            'metadata'    => array(
                '_awpcp_payment_term_id'   => '3',
                '_awpcp_payment_term_type' => 'fee',
            ),
            'post_fields' => array(
                'post_author' => 1,
            ),
        );
    }

    /**
     * @since x.x
     */
    private function get_test_subject() {
        return new AWPCP_PaymentInformationValidator(
            'awpcp_listing_category',
            $this->categories,
            $this->payments,
            $this->roles
        );
    }
}
