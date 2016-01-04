<?php

function AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler() {
    return new AWPCP_Upgrade_Task_Handler(
        new AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler(
            awpcp_categories_registry(),
            awpcp_wordpress(),
            $GLOBALS['wpdb']
        )
    );
}

class AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler implements AWPCP_Upgrade_Task_Handler_Implementation {

    private $categories;
    private $wordpress;
    private $db;

    public function __construct( $categories, $wordpress, $db ) {
        $this->categories = $categories;
        $this->wordpress = $wordpress;
        $this->db = $db;
    }

    public function get_last_item_id() {
        return $this->wordpress->get_option( 'awpcp-slcact-last-listing-id' );
    }

    public function update_last_item_id( $last_item_id  ) {
        $this->wordpress->update_option( 'awpcp-slcact-last-listing-id', $last_item_id );
    }

    public function count_pending_items( $last_item_id ) {
        $items_count = $this->db->get_var( 'SELECT COUNT(category_id) FROM ' . AWPCP_TABLE_CATEGORIES );

        return intval( $items_count ) - $last_item_id;
    }

    public function get_pending_items( $last_item_id ) {
        $query = 'SELECT * FROM ' . AWPCP_TABLE_CATEGORIES . ' ORDER BY category_parent_id ASC LIMIT %d, 50';
        return $this->db->get_results( $this->db->prepare( $query, $last_item_id ) );
    }

    public function process_item( $item, $last_item_id ) {
        $existing_term = $this->wordpress->get_term_by( 'name', $item->category_name, 'awpcp_listing_category' );

        if ( is_object( $existing_term ) ) {
            $category_name = $this->get_unique_category_name( $existing_term );
        } else {
            $category_name = $item->category_name;
        }

        $term = $this->wordpress->insert_term(
            $category_name,
            'awpcp_listing_category',
            array(
                'parent' => $this->get_item_parent_id( $item ),
            )
        );

        if ( is_wp_error( $term ) ) {
            throw new AWPCP_Exception( sprintf( "A custom taxonomy term coulnd't be created for listing category \"%s\".", $item->category_name ) );
        }

        $this->categories->update_categories_registry( $item->category_id, $term['term_id'] );

        return $last_item_id + 1;
    }

    private function get_unique_category_name( $term ) {
        $similar_terms = $this->wordpress->get_terms(
            'awpcp_listing_category',
            array(
                'name__like' => "{$term->name} (Copy %",
                'hide_empty' => false,
            )
        );

        $numbers = array();

        foreach ( $similar_terms as $similar_term ) {
            $numbers[] = absint( str_replace( "{$term->name} (Copy ", '', $similar_term->name ) );
        }

        if ( empty( $numbers ) ) {
            $category_name = "{$term->name} (Copy 1)";
        } else {
            $category_name = sprintf( "{$term->name} (Copy %d)", max( $numbers ) + 1 );
        }

        return $category_name;
    }

    private function get_item_parent_id( $item ) {
        if ( ! $item->category_parent_id ) {
            return 0;
        }

        $parent_item = $this->get_parent_item( $item->category_parent_id );

        if ( is_null( $parent_item ) ) {
            return 0;
        }

        $categories_registry = $this->categories->get_categories_registry();

        if ( ! isset( $categories_registry[ $parent_item->category_id ] ) ) {
            return 0;
        }

        return $categories_registry[ $parent_item->category_id ];
    }

    private function get_parent_item( $parent_id ) {
        $query = 'SELECT * FROM ' . AWPCP_TABLE_CATEGORIES . ' WHERE category_id = %d';
        return $this->db->get_row( $this->db->prepare( $query, $parent_id ) );
    }
}
