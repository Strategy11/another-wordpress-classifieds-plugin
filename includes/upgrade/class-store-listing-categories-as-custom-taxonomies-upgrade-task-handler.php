<?php
/**
 * @package AWPCP\Upgrade
 */

// phpcs:disable

class AWPCP_Store_Listing_Categories_As_Custom_Taxonomies_Upgrade_Task_Handler implements AWPCP_Upgrade_Task_Runner {

    private $categories;
    private $wordpress;
    private $db;

    use AWPCP_UpgradeCategoriesTaskHandlerHelper;

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
        $categories_registry = $this->categories->get_categories_registry();

        if ( isset( $categories_registry[ $item->category_id ] ) ) {
            return $last_item_id + 1;
        }

        $category_slug = $this->generate_category_slug( $item );
        $existing_term = $this->wordpress->get_term_by( 'slug', $category_slug, 'awpcp_listing_category' );
        $category_name = $item->category_name;

        if ( is_object( $existing_term ) ) {
            $category_name = $this->generate_unique_category_name( $existing_term );
            $category_slug = null;
        }

        $term = $this->insert_term( $category_name, $category_slug, $item );

        if ( is_wp_error( $term ) ) {
            throw new AWPCP_Exception( sprintf( "A custom taxonomy term coulnd't be created for listing category \"%s\".", $item->category_name ) );
        }

        $this->categories->update_categories_registry( $item->category_id, $term['term_id'] );

        return $last_item_id + 1;
    }

    /**
     * @since 4.0.0
     */
    private function generate_category_slug( $item ) {
        if ( $this->category_has_duplicated_name( $item ) && $item->category_parent_id ) {
            return $this->generate_slug_including_parent( $item );
        }

        return sanitize_title( $item->category_name );
    }

    /**
     * @since 4.0.0
     */
    private function category_has_duplicated_name( $item ) {
        $sql = 'SELECT COUNT(category_id) FROM ' . AWPCP_TABLE_CATEGORIES . ' WHERE category_name = %s';
        $sql = $this->db->prepare( $sql, $item->category_name );

        return intval( $this->db->get_var( $sql ) ) > 1;
    }

    /**
     * @since 4.0.0
     */
    private function generate_slug_including_parent( $item ) {
        $category_slug = sanitize_title( $item->category_name );

        $sql = 'SELECT category_name FROM ' . AWPCP_TABLE_CATEGORIES . ' WHERE category_id = %d';
        $sql = $this->db->prepare( $sql, $item->category_parent_id );

        $parent_category_name = $this->db->get_var( $sql );
        $parent_category_slug = sanitize_title( $parent_category_name );

        if ( ! $parent_category_name ) {
            return $category_slug;
        }

        return "{$parent_category_slug}-{$category_slug}";
    }

    private function generate_unique_category_name( $term ) {
        $similar_terms = $this->wordpress->get_terms(
            'awpcp_listing_category',
            array(
                'name__like' => "{$term->name} (Copy ",
                'hide_empty' => false,
            )
        );

        $category_name = "{$term->name} (Copy 1)";
        $numbers       = array();

        foreach ( $similar_terms as $similar_term ) {
            $numbers[] = absint( str_replace( "{$term->name} (Copy ", '', $similar_term->name ) );
        }

        if ( ! empty( $numbers ) ) {
            $category_name = sprintf( "{$term->name} (Copy %d)", max( $numbers ) + 1 );
        }

        return $category_name;
    }

    /**
     * Inserts a new term to replace the pre-4.0.0 category represented by
     * $category, making sure that the ID of the new term is greater than the
     * largest ID of all pre-4.0.0 categories, to avoid conflicts.
     *
     * @since 4.0.0
     */
    private function insert_term( $category_name, $category_slug, $category ) {
        $max_legacy_category_id = $this->get_max_category_id();
        $wanted_term_id         = $max_legacy_category_id + 1;

        $term_data = [
            'slug'        => $category_slug,
            'name'        => $category_name,
            'description' => '',
            'parent'      => $this->get_item_parent_id( $category ),
            'taxonomy'    => 'awpcp_listing_category',
        ];

        return $this->maybe_insert_term_with_id( $wanted_term_id, $term_data );
    }

    /**
     * Returns the greatest category_id stored on the awpcp_categories table.
     *
     * @since 4.0.0
     */
    private function get_max_category_id() {
        $table = AWPCP_TABLE_CATEGORIES;

        return intval( $this->db->get_var( "SELECT MAX(category_id) FROM {$table}" ) );
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
