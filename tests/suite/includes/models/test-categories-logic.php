<?php

class AWPCP_Test_Categories_Logic extends AWPCP_UnitTestCase {

    public function test_create_category() {
        $data_mapper = new AWPCP_Categories_Logic(
            AWPCP_CATEGORY_TAXONOMY,
            awpcp_listings_api(),
            awpcp_listings_collection(),
            awpcp_wordpress()
        );

        $term_id = $data_mapper->create_category( array( 'name' => 'Test Category' ) );

        $this->assertInternalType( 'int', $term_id );
        $this->assertTrue( $term_id > 0 );
    }

    public function test_save_existing_category() {
        $data_mapper = new AWPCP_Categories_Logic(
            AWPCP_CATEGORY_TAXONOMY,
            awpcp_listings_api(),
            awpcp_listings_collection(),
            awpcp_wordpress()
        );

        // create a category
        $term_id = $data_mapper->create_category( array( 'name' => 'Test Category' ) );

        // update the category
        $modified_name = 'Modified Name';
        $found_category = get_term_by( 'id', $term_id, AWPCP_CATEGORY_TAXONOMY );
        $found_category->name = $modified_name;

        $term_id = $data_mapper->update_category( $found_category );
        $found_category = get_term_by( 'id', $term_id, AWPCP_CATEGORY_TAXONOMY );

        $this->assertInternalType( 'int', $term_id );
        $this->assertEquals( $modified_name, $found_category->name );
    }

    public function test_save_category_with_invalid_id() {
        if ( version_compare( get_bloginfo( 'version' ), '4.1.7', '<=' ) ) {
            $this->markTestSkipped();
        }

        $category = (object) array(
            'term_id' => rand() + 1,
            'name' => 'Test Category',
        );

        $data_mapper = new AWPCP_Categories_Logic(
            AWPCP_CATEGORY_TAXONOMY,
            awpcp_listings_api(),
            awpcp_listings_collection(),
            awpcp_wordpress()
        );

        $this->setExpectedException( 'AWPCP_Exception' );
        $term_id = $data_mapper->update_category( $category );
    }
}
