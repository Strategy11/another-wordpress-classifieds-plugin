<?php
/**
 * @package AWPCP\Tests\Categories
 */

use Brain\Monkey\Functions;

/**
 * @since 4.0.0
 */
class AWPCP_CategoryPresenterTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        $this->categories_collection = Mockery::mock( 'AWPCP_CategoriesCollection' );
    }

    /**
     * @dataProvider get_full_name_data_provider
     * @since 4.0.0
     */
    public function test_get_full_name_from_meta( $category, $full_name, $stored_name, $categories ) {
        Functions\expect( 'get_term_meta' )
            ->with( $category->term_id, '_awpcp_full_name', true )
            ->andReturn( $stored_name );

        foreach ( $categories as $c ) {
            $this->categories_collection->shouldReceive( 'get' )
                ->with( $c->term_id )
                ->andReturn( $c );
        }

        $generated_full_name = $this->get_test_subject()->get_full_name( $category );

        $this->assertEquals( $full_name, $generated_full_name );
    }

    /**
     * @since 4.0.0
     */
    public function get_full_name_data_provider() {
        $grandparent = (object) [
            'term_id' => wp_rand(),
            'name'    => 'A',
            'parent'  => 0,
        ];

        $parent = (object) [
            'term_id' => wp_rand(),
            'name'    => 'B',
            'parent'  => $grandparent->term_id,
        ];

        $category = (object) [
            'term_id' => wp_rand(),
            'name'    => 'C',
            'parent'  => $parent->term_id,
        ];

        $categories = [ $grandparent, $parent, $category ];

        return [
            'from term meta'     => [
                $category,
                'Stored Name',
                'Stored Name',
                $categories,
            ],
            'sub-category'       => [
                $category,
                "{$grandparent->name}: {$parent->name}: {$category->name}",
                false,
                $categories,
            ],
            'top-level-category' => [
                $grandparent,
                $grandparent->name,
                false,
                $categories,
            ],
        ];
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_CategoryPresenter( $this->categories_collection );
    }
}
