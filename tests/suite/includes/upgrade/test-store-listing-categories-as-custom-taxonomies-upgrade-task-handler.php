<?php
/**
 * @package AWPCP\Tests\Plugin\Upgrade
 */

// phpcs:disable

use Brain\Monkey\Functions;

class AWPCP_Test_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler extends AWPCP_UnitTestCase {

	private $term_name;
	private $term_taxonomy;
	private $term_properties;
	private $category_id;
	private $stored_term_id;

    public function setup() {
        parent::setup();

        if ( ! defined( 'AWPCP_TABLE_CATEGORIES' ) ) {
            define( 'AWPCP_TABLE_CATEGORIES', 'categories' );
        }

        $this->db = Mockery::mock( 'wpdb' );

        $this->db->terms = 'wp_terms';

        $this->categories = Mockery::mock( 'AWPCP_Categories_Registry' );
        $this->wordpress = Phake::mock( 'AWPCP_WordPress' );

        $this->item = new stdClass();
        $this->item->category_id = rand() + 1;
        $this->item->category_name = 'Category';
        $this->item->category_parent_id = 0;

        $this->term_id = rand() + 1;
        $this->last_item_id = rand() + 1;

        Functions\when( 'is_wp_error' )->justReturn( false );
    }

    public function test_process_item() {
        $this->markTestIncomplete();

        $handler = new AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler(
            $this->categories,
            $this->wordpress,
            null
        );

        $this->process_item( $handler );

        $this->assertEquals( $this->last_item_id + 1, $this->new_last_item_id );
        $this->assertEquals( $this->item->category_name, $this->term_name );
        $this->assertEquals( 0, $this->term_properties['parent'] );
        $this->assertEquals( $this->item->category_id, $this->category_id );
        $this->assertEquals( $this->term_id, $this->stored_term_id );
    }

    private function process_item( $handler ) {
        Phake::when( $this->wordpress )->insert_term->thenReturn( array( 'term_id' => $this->term_id ) );

        $this->new_last_item_id = $handler->process_item( $this->item, $this->last_item_id );

        Phake::verify( $this->wordpress )->insert_term(
            Phake::capture( $this->term_name ),
            Phake::capture( $this->term_taxonomy ),
            Phake::capture( $this->term_properties )
        );

        Phake::verify( $this->categories )->update_categories_registry(
             Phake::capture( $this->category_id ),
             Phake::capture( $this->stored_term_id )
        );
    }

    public function test_process_item_handles_categories_with_duplicated_names() {
        $this->markTestIncomplete();

        $existing_term = new stdClass();
        $existing_term->name = 'Duplicated Category';

        Phake::when( $this->wordpress )
            ->get_term_by( 'name', $this->item->category_name, 'awpcp_listing_category' )
            ->thenReturn( $existing_term );

        $similar_terms = array(
            (object) array( 'name' => 'Duplicated Category (Copy 1)' ),
            (object) array( 'name' => 'Duplicated Category (Copy 2)' ),
            (object) array( 'name' => 'Duplicated Category (Copy 3)' ),
            (object) array( 'name' => 'Duplicated Category (Copy 4)' )
        );

        Phake::when( $this->wordpress )->get_terms->thenReturn( $similar_terms );

        $handler = new AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler(
            $this->categories,
            $this->wordpress,
            null
        );

        $this->process_item( $handler );

        $this->assertEquals( 'Duplicated Category (Copy 5)', $this->term_name );
    }

    public function test_process_item_handles_subcategories() {
        $this->markTestIncomplete();

        $this->item->category_parent_id = rand() + 1;

        $parent_category_id = rand() + 1;
        $parent_item = (object) array(
            'category_id' => $parent_category_id,
        );

        $this->db->shouldReceive( 'prepare' )
            ->andReturn( '' );

        $this->db->shouldReceive( 'get_row' )
            ->once()
            ->andReturn( $parent_item );

        $parent_term_id = rand() + 1;
        $categories_registry = array( $parent_category_id => $parent_term_id );

        Phake::when( $this->categories )->get_categories_registry->thenReturn( $categories_registry );

        $handler = new AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler(
            $this->categories,
            $this->wordpress,
            $this->db
        );

        $this->process_item( $handler );

        $this->assertEquals( $parent_term_id, $this->term_properties['parent'] );
    }

    /**
     * @since 4.0.0
     */
    public function test_process_item_ignores_already_processed_categories() {
        $this->markTestIncomplete();

        $this->categories = Mockery::mock( 'AWPCP_Categories_Registry' );
        $this->wordpress  = Mockery::mock( 'AWPCP_WordPress' );

        $this->categories->shouldReceive( 'get_categories_registry' )
             ->once()
             ->andReturn( [ $this->item->category_id => rand() + 1 ] );

        $this->wordpress->shouldReceive( 'insert_term' )
             ->never();

        $handler = $this->get_test_subject();

        // Execution.
        $new_last_item_id = $handler->process_item( $this->item, $this->last_item_id );

        // Verification.
        $this->assertEquals( $this->last_item_id + 1, $new_last_item_id );
    }

    private function get_test_subject() {
        return new AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler(
            $this->categories,
            $this->wordpress,
            $this->db
        );
    }

    /**
     * Test that the upgrade routine uses 'Unknown' as the name of the category
     * when the category object has an empty name.
     *
     * See https://github.com/drodenbaugh/awpcp/issues/2536.
     *
     * @since 4.0.3
     */
    public function test_process_item_with_empty_name() {
        $item = (object) [
            'category_id'        => wp_rand(),
            'category_name'      => '',
            'category_parent_id' => 0,
        ];

        $class = 'AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler';

        $this->redefine( $class . '::category_has_duplicated_name', Patchwork\always( false ) );

        $this->redefine(
            $class . '::insert_term',
            function( $category_name, $category_slug ) {
                if ( $category_slug !== 'unknown' ) {
                    return [];
                }

                if ( $category_name !== 'Unknown' ) {
                    return [];
                }

                return [ 'term_id' => wp_rand() ];
            }
        );

        Functions\when( 'sanitize_title' )->alias( 'strtolower' );

        Functions\expect( 'get_term_by' )
            ->with( 'slug', 'unknown', 'awpcp_listing_category' )
            ->andReturn( null );

        $this->categories->shouldReceive( 'get_categories_registry' )
            ->andReturn( null );

        $this->categories->shouldReceive( 'update_categories_registry' )
            ->andReturn( null );

        $this->get_test_subject()->process_item( $item, null );
    }
}
