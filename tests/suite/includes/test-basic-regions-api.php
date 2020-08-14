<?php

class AWPCP_TestBasicRegionsAPI extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->db = Phake::mock( 'wpdb' );
    }

    public function test_update_ad_regions() {
        $listing = awpcp_tests_create_listing();
        $regions_data = array( array( 'country' => 'Colombia', 'state' => 'Antioquia' ) );

        $regions = new AWPCP_BasicRegionsAPI( $this->db );

        $regions->update_ad_regions( $listing, $regions_data, 1 );

        Phake::verify( $this->db )->prepare( Phake::capture( $sql ), $listing->ID );
        Phake::verify( $this->db )->insert( AWPCP_TABLE_AD_REGIONS, Phake::capture( $region_data ) );

        $this->assertEquals( $listing->ID, $region_data['ad_id'] );
        $this->assertEquals( $regions_data[0]['country'], $region_data['country'] );
    }
}
