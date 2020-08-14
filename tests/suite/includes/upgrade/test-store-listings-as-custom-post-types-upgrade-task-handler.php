<?php
/**
 * @package AWPCP\Tests\Upgrade
 */

/**
 * Tests for Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler.
 */
class AWPCP_Test_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler extends AWPCP_UnitTestCase {

	private $post_properties;

    public function setup() {
        parent::setup();

        $this->db = $GLOBALS['wpdb'];

        $this->categories              = Phake::mock( 'AWPCP_Categories_Registry' );
        $this->legacy_listing_metadata = Phake::mock( 'AWPCP_Legacy_Listings_Metadata' );
        $this->wordpress               = Phake::mock( 'AWPCP_WordPress' );

        $this->last_item_id = wp_rand() + 1;

        $this->item = (object) array(
            'ad_id'               => wp_rand() + 1,
            'adterm_id'           => wp_rand() + 1,
            'ad_details'          => 'Test Content',
            'ad_title'            => 'Test Listing',
            'ad_contact_name'     => 'John Doe',
            'ad_contact_phone'    => '316 632 98 61',
            'phone_number_digits' => '3166326198',
            'ad_contact_email'    => 'payer@example.org',
            'ad_item_price'       => wp_rand() + 1,
            'ad_postdate'         => current_time( 'mysql' ),
            'ad_last_updated'     => current_time( 'mysql' ),
            'ad_startdate'        => current_time( 'mysql' ),
            'ad_enddate'          => current_time( 'mysql' ),
            'disabled_date'       => current_time( 'mysql' ),
            'payment_status'      => current_time( 'mysql' ),
            'verified_at'         => current_time( 'mysql' ),
            'renewed_date'        => current_time( 'mysql' ),
            'payment_term_type'   => 'fee',
            'payment_gateway'     => '',
            'ad_fee_paid'         => 1500,
            'payer_email'         => 'payer@example.org',
            'websiteurl'          => 'http://example.org',
            'disabled'            => false,
            'verified'            => true,
            'flagged'             => false,
            'ad_views'            => wp_rand(),
            'ad_key'              => md5( 'key' . wp_rand() ),
            'ad_transaction_id'   => md5( 'transaction' . wp_rand() ),
            'posterip'            => '10.10.10.3',
            'renew_email_sent'    => true,
        );
    }

    public function test_count_pending_items() {
        $this->create_test_listings();

        $task_handler = new AWPCP_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler(
            null,
            null,
            null,
            $this->db
        );

        $this->assertEquals( 2, $task_handler->count_pending_items( null ) );
    }

    private function create_test_listings() {
        $this->db->query( awpcp_database_tables()->get_listings_table_definition() );

        $this->db->query( 'DELETE FROM ' . AWPCP_TABLE_ADS );

        $this->db->insert(
            AWPCP_TABLE_ADS,
            array(
                'ad_id'                 => wp_rand() + 1,
                'ad_fee_paid'           => 0,
                'ad_category_id'        => wp_rand() + 1,
                'ad_category_parent_id' => wp_rand() + 1,
                'ad_details'            => '',
                'websiteurl'            => 'http://example.org',
                'ad_item_price'         => 0,
                'ad_postdate'           => current_time( 'mysql' ),
                'ad_last_updated'       => current_time( 'mysql' ),
                'ad_startdate'          => current_time( 'mysql' ),
                'ad_enddate'            => current_time( 'mysql' ),
            )
        );

        $this->db->insert(
            AWPCP_TABLE_ADS,
            array(
                'ad_id'                 => wp_rand() + 1,
                'ad_fee_paid'           => 0,
                'ad_category_id'        => wp_rand() + 1,
                'ad_category_parent_id' => wp_rand() + 1,
                'ad_details'            => '',
                'websiteurl'            => 'http://example.org',
                'ad_item_price'         => 0,
                'ad_postdate'           => current_time( 'mysql' ),
                'ad_last_updated'       => current_time( 'mysql' ),
                'ad_startdate'          => current_time( 'mysql' ),
                'ad_enddate'            => current_time( 'mysql' ),
            )
        );
    }

    public function test_get_pending_items() {
        $this->create_test_listings();

        $task_handler = new AWPCP_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler(
            null,
            null,
            null,
            $this->db
        );

        $this->assertEquals( 2, count( $task_handler->get_pending_items( null ) ) );
    }

    public function test_process_item() {
        $task_handler = new AWPCP_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler(
            $this->categories,
            $this->legacy_listing_metadata,
            $this->wordpress,
            $this->db
        );

        $this->process_item( $task_handler );

        $this->assertEquals( $this->item->ad_title, $this->post_properties['post_title'] );
        $this->assertEquals( $this->item->ad_details, $this->post_properties['post_content'] );
    }

    private function process_item( $task_handler ) {
        $this->new_listing_id = $task_handler->process_item( $this->item, $this->last_item_id );

        Phake::verify( $this->wordpress )->insert_post( Phake::capture( $this->post_properties ), Phake::ignoreRemaining() );
        Phake::verify( $this->wordpress )->update_post_meta( Phake::capture( $post_id ), '_awpcp_most_recent_start_date', Phake::ignoreRemaining() );
        Phake::verify( $this->wordpress )->update_post_meta( Phake::capture( $post_id ), '_awpcp_renewed_date', Phake::ignoreRemaining() );
        Phake::verify( $this->wordpress )->update_post_meta( Phake::capture( $post_id ), '_awpcp_verified', Phake::ignoreRemaining() );
    }
}
