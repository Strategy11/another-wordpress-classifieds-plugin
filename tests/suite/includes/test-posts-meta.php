<?php

class APWCP_Test_Posts_Meta extends AWPCP_UnitTestCase {


    /**
     * @large
     */
    public function test_get_meta_values() {
        $first_listing = awpcp_tests_create_listing();
        $second_listing = awpcp_tests_create_listing();
        $third_listing = awpcp_tests_create_listing();

        wp_update_post( array( 'ID' => $first_listing->ID, 'post_status' => 'publish' ) );
        wp_update_post( array( 'ID' => $third_listing->ID, 'post_status' => 'publish' ) );

        update_post_meta( $first_listing->ID, '_awpcp_contact_name', 'Carlos' );
        update_post_meta( $second_listing->ID, '_awpcp_contact_name', 'Byron' );
        update_post_meta( $third_listing->ID, '_awpcp_contact_name', 'Aaron' );

        $configuration = Phake::mock( 'AWPCP_Posts_Meta_Configuration' );

        Phake::when( $configuration )->get_post_type->thenReturn( AWPCP_LISTING_POST_TYPE );
        Phake::when( $configuration )->prepare_meta_key->thenReturn( '_awpcp_contact_name' );

        $posts_meta = new AWPCP_Posts_Meta( $configuration, $GLOBALS['wpdb'] );

        $contact_names = $posts_meta->get_meta_values( 'contact_name' );

        $this->assertEquals( array( 'Aaron', 'Carlos' ), $contact_names );
    }
}
