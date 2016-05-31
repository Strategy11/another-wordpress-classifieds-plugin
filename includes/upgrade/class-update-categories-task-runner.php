<?php

class AWPCP_Update_Categories_Task_Runner implements AWPCP_Upgrade_Task_Runner {

    private $delegate;
    private $categories;
    private $wordpress;
    private $db;

    public function __construct( $delegate, $categories, $wordpress, $db ) {
        $this->delegate = $delegate;
        $this->categories = $categories;
        $this->wordpress = $wordpress;
        $this->db = $db;
    }

    public function get_last_item_id() {
        $this->wordpress->get_option( $this->delegate->get_option_name() );
    }

    public function update_last_item_id( $last_item_id ) {
        $this->wordpress->update_option( $this->delegate->get_option_name(), $last_item_id );
    }

    public function count_pending_items( $last_item_id ) {
        $sql = $this->delegate->get_count_pending_items_sql();
        return intval( $this->db->get_var( $this->db->prepare( $sql, $last_item_id ) ) );
    }

    public function get_pending_items( $last_item_id ) {
        $sql = $this->delegate->get_pending_items_sql();
        return $this->db->get_results( $this->db->prepare( $sql, $last_item_id ) );
    }

    public function process_item( $item, $last_item_id ) {
        $categories_registry = $this->categories->get_categories_registry();

        $old_categories = $this->delegate->get_item_categories( $item );
        $new_categories = array();

        foreach ( $old_categories as $category ) {
            if ( isset( $categories_registry[ $category ] ) ) {
                $new_categories[] = $categories_registry[ $category ];
            } else {
                $new_categories[] = $category;
            }
        }

        if ( ! empty( $new_categories ) ) {
            $this->delegate->update_item_categories( $item, $new_categories );
        }

        return $this->delegate->get_item_id( $item );
    }
}
