<?php

/**
 * @group core
 */
class AWPCP_TestLegacyFunctions extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $terms = get_terms( 'awpcp_listing_category', array( 'hide_empty' => false ) );

        foreach ( $terms as $term ) {
            wp_delete_term( $term->term_id, 'awpcp_listing_category' );
        }
    }

    public function test_awpcpistableempty() {
        global $wpdb;

        $wpdb->query( 'DELETE FROM ' . AWPCP_TABLE_ADFEES );

        $this->assertTrue( awpcpistableempty( AWPCP_TABLE_ADFEES ) );

        $wpdb->insert( AWPCP_TABLE_ADFEES, array(
            'adterm_id' => rand() + 1,
            'description' => '',
        ) );

        $this->assertFalse( awpcpistableempty( AWPCP_TABLE_ADFEES ) );
    }

    /**
     * @large
     */
    public function test_count_listings_in_categories_does_not_count_unpaid_listings() {
        awpcp_tests_delete_all_listings();

        $test_category = wp_insert_term( 'Test Category', 'awpcp_listing_category' );

        $count = awpcp_count_listings_in_categories();
        $this->assertEquals( 0, isset( $count[ $test_category['term_id'] ] ) ? $count[ $test_category['term_id'] ] : 0 );

        $unpaid_listing = awpcp_tests_create_empty_listing();

        wp_update_post( array( 'ID' => $unpaid_listing->ID, 'post_status' => 'publish' ) );

        wp_add_object_terms( $unpaid_listing->ID, $test_category['term_id'], 'awpcp_listing_category' );

        update_post_meta( $unpaid_listing->ID, '_awpcp_payment_status', 'Unpaid' );
        update_post_meta( $unpaid_listing->ID, '_awpcp_verified', true );

        $count = awpcp_count_listings_in_categories();
        $this->assertEquals( 0, isset( $count[ $test_category['term_id'] ] ) ? $count[ $test_category['term_id'] ] : 0 );

        update_post_meta( $unpaid_listing->ID, '_awpcp_payment_status', 'Completed' );

        $count = awpcp_count_listings_in_categories();
        $this->assertEquals( 1, isset( $count[ $test_category['term_id'] ] ) ? $count[ $test_category['term_id'] ] : 0 );
    }

    public function test_countcategories() {
        $result = wp_insert_term( 'A', AWPCP_CATEGORY_TAXONOMY, array() );

        wp_insert_term( 'B', AWPCP_CATEGORY_TAXONOMY, array( 'parent' => $result['term_id'] ) );
        wp_insert_term( 'C', AWPCP_CATEGORY_TAXONOMY, array( 'parent' => $result['term_id'] ) );

        $this->assertEquals( 3, countcategories() );

        if ( version_compare( get_bloginfo( 'version' ), '4.1.7', '<=' ) ) {
            $this->markTestIncomplete( 'Necessary WordPress features are not available before WP 4.2.' );
        }

        $this->assertEquals( 2, countcategorieschildren() );
        $this->assertEquals( 1, countcategoriesparents() );
    }
}
