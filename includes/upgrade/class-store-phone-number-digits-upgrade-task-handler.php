<?php

class AWPCP_Store_Phone_Number_Digits_Upgrade_Task_Handler implements AWPCP_Upgrade_Task_Runner {

    public function __construct( $db ) {
        $this->db = $db;
    }

    public function get_last_item_id() {
        return get_option( 'awpcp-spnd-last-file-id' );
    }

    public function update_last_item_id( $last_item_id ) {
        return update_option( 'awpcp-spnd-last-file-id', $last_item_id, false );
    }

    public function count_pending_items( $last_item_id ) {
        $query = 'SELECT COUNT(ad_id) FROM ' . AWPCP_TABLE_ADS . ' WHERE ad_id > %d';
        return intval( $this->db->get_var( $this->db->prepare( $query, intval( $last_item_id ) ) ) );
    }

    public function get_pending_items( $last_item_id ) {
        $query = 'SELECT * FROM ' . AWPCP_TABLE_ADS . ' WHERE ad_id > %d ORDER BY ad_id LIMIT 0, 10';
        return $this->db->get_results( $this->db->prepare( $query, intval( $last_item_id ) ) );
    }

    public function process_item( $item, $last_item_id ) {
        $phone_number_digits = awpcp_get_digits_from_string( $item->ad_contact_phone );

        $this->db->update(
            AWPCP_TABLE_ADS,
            array( 'phone_number_digits' => $phone_number_digits ),
            array( 'ad_id' => $item->ad_id )
        );

        return $item->ad_id;
    }
}
