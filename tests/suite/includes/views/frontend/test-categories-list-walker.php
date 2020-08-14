<?php

class AWPCP_TestCategoriesListWalker extends AWPCP_UnitTestCase {

    public function test_walk_without_hierarchy() {
        $categories = $this->get_categories_list_without_hierarchy();
        $expected_output = '#<div id="awpcpcatlayout" class="awpcp-categories-list"><ul class="top-level-categories showcategoriesmainlist clearfix"><li class="columns-1"><p class="top-level-category maincategoryclass"><a class="toplevelitem" href="[^"]+">Novels</a> \(\d+\)</p></li><li class="columns-1"><p class="top-level-category maincategoryclass"><a class="toplevelitem" href="[^"]+">Video Games</a> \(\d+\)</p></li></ul></div><div class="fixfloat"></div>#';

        $this->verify_walker_output( $categories, array(), $expected_output );
    }

    private function get_categories_list_without_hierarchy() {
        $categories = array();

        $categories[1] = new stdClass();
        $categories[1]->name = 'Novels';
		$categories[1]->term_id = 33;
        $categories[1]->parent = 1;
        $categories[1]->listings_count = rand() + 1;

        $categories[0] = new stdClass();
        $categories[0]->name = 'Video Games';
		$categories[0]->term_id = 23;
        $categories[0]->parent = 1;
        $categories[0]->listings_count = rand() + 1;

        return $categories;
    }

    private function verify_walker_output( $categories, $params = array(), $expected_output ) {
        $walker = new AWPCP_CategoriesListWalker();

        if ( $walker->configure( $params ) ) {
            $output = $walker->walk( $categories );
        } else {
            $this->fail();
        }

        $this->assertRegExp( $expected_output, $output );
    }

    public function test_walk_without_listings_count() {
        $categories = $this->get_categories_list_without_hierarchy();
        $expected_output = '#<div id="awpcpcatlayout" class="awpcp-categories-list"><ul class="top-level-categories showcategoriesmainlist clearfix"><li class="columns-1"><p class="top-level-category maincategoryclass"><a class="toplevelitem" href="[^"]+">Novels</a> </p></li><li class="columns-1"><p class="top-level-category maincategoryclass"><a class="toplevelitem" href="[^"]+">Video Games</a> </p></li></ul></div><div class="fixfloat"></div>#';

        $this->verify_walker_output( $categories, array( 'show_listings_count' => false ), $expected_output );
    }

    public function test_walk_with_hierarchy() {
        $categories = array();

        $categories[7] = new stdClass();
        $categories[7]->name = 'Category A';
		$categories[7]->term_id = 1;
        $categories[7]->parent = 0;
        $categories[7]->listings_count = rand() + 1;

        $categories[0] = new stdClass();
        $categories[0]->name = 'Category B';
		$categories[0]->term_id = 2;
        $categories[0]->parent = 0;
        $categories[0]->listings_count = rand() + 1;

        $categories[1] = new stdClass();
        $categories[1]->name = 'Category C';
		$categories[1]->term_id = 3;
        $categories[1]->parent = 0;
        $categories[1]->listings_count = rand() + 1;

        $categories[2] = new stdClass();
        $categories[2]->name = 'Category D';
		$categories[2]->term_id = 4;
        $categories[2]->parent = 0;
        $categories[2]->listings_count = rand() + 1;

        $categories[3] = new stdClass();
        $categories[3]->name = 'Category E';
		$categories[3]->term_id = 5;
        $categories[3]->parent = 0;
        $categories[3]->listings_count = rand() + 1;

        $categories[4] = new stdClass();
        $categories[4]->name = 'Category B.1';
		$categories[4]->term_id = 6;
        $categories[4]->parent = 2;
        $categories[4]->listings_count = rand() + 1;

        $categories[5] = new stdClass();
        $categories[5]->name = 'Category B.2';
		$categories[5]->term_id = 7;
        $categories[5]->parent = 2;
        $categories[5]->listings_count = rand() + 1;

        $categories[6] = new stdClass();
        $categories[6]->name = 'Category D.1';
		$categories[6]->term_id = 8;
        $categories[6]->parent = 4;
        $categories[6]->listings_count = rand() + 1;

        $expected_output = '#<div id="awpcpcatlayout" class="awpcp-categories-list"><ul class="top-level-categories showcategoriesmainlist clearfix"><li class="columns-2"><p class="top-level-category maincategoryclass"><a class="toplevelitem" href="[^"]+">Category A</a> \(\d+\)</p></li><li class="columns-2"><p class="top-level-category maincategoryclass"><a class="toplevelitem" href="[^"]+">Category B</a> \(\d+\)</p><ul class="sub-categories showcategoriessublist clearfix"><li><a class="" href="[^"]+">Category B.1</a> \(\d+\)</li><li><a class="" href="[^"]+">Category B.2</a> \(\d+\)</li></ul></li></ul><ul class="top-level-categories showcategoriesmainlist clearfix"><li class="columns-2"><p class="top-level-category maincategoryclass"><a class="toplevelitem" href="[^"]+">Category C</a> \(\d+\)</p></li><li class="columns-2"><p class="top-level-category maincategoryclass"><a class="toplevelitem" href="[^"]+">Category D</a> \(\d+\)</p><ul class="sub-categories showcategoriessublist clearfix"><li><a class="" href="[^"]+">Category D.1</a> \(\d+\)</li></ul></li></ul><ul class="top-level-categories showcategoriesmainlist clearfix"><li class="columns-2"><p class="top-level-category maincategoryclass"><a class="toplevelitem" href="[^"]+">Category E</a> \(\d+\)</p></li></ul></div><div class="fixfloat"></div>#';

        $this->verify_walker_output( $categories, array( 'show_listings_count' => true, 'show_in_columns' => 2 ), $expected_output );
    }
}
