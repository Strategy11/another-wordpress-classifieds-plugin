<?php
/**
 * @package AWPCP\Upgrade
 */

/**
 * Upgrade routine to replace listing category taxonomy terms that have an ID
 * matching the ID from one of the pre-4.0.0 categories, causing that listing
 * category taxonomy term to be inaccessible because, in order to maintain
 * backwards compatiblity, the plugin always assumes the user is trying to see
 * the pre-4.0.0 category.
 *
 * @since 4.0.0beta2
 */
class AWPCP_FixIDCollisionForListingCategoriesUpgradeTaskHandler implements AWPCP_Upgrade_Task_Runner {

    /**
     * @var string
     */
    private $listing_category_taxonomy;

    /**
     * @var CategoriesRegistry
     */
    private $categories_registry;

    /**
     * @var object
     */
    private $wordpress;

    /**
     * @var object
     */
    private $db;

    /**
     * Constructor.
     */
    public function __construct( $listing_category_taxonomy, $categories_registry, $wordpress, $db ) {
        $this->listing_category_taxonomy = $listing_category_taxonomy;
        $this->categories_registry       = $categories_registry;
        $this->wordpress                 = $wordpress;
        $this->db                        = $db;
    }

    /**
     * We will continue processing items until there are no more collisions on
     * the categories registry, so we don't need to keep track of the last
     * processed item. Processed an item will result in that collision being
     * removed.
     *
     * @since 4.0.0
     */
    public function get_last_item_id() {
        return 1;
    }

    /**
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update_last_item_id( $last_item_id ) {
        // These aren't the droids you're looking for. See get_last_item_id().
    }

    /**
     * Count number items that need to be processed.
     *
     * @param int $last_item_id     The ID of the last item processed by the routine.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function count_pending_items( $last_item_id ) {
        $collisions = $this->categories_registry->get_id_collisions();

        return count( $collisions );
    }

    /**
     * Get items that need to be processed.
     *
     * @param int $last_item_id     The ID of the last item processed by the routine.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_pending_items( $last_item_id ) {
        $collisions = $this->categories_registry->get_id_collisions();

        return array_slice( $collisions, 0, 50 );
    }

    /**
     * @param object $item          An item to process.
     * @param int    $last_item_id  The ID of the last item processed by the routine.
     * @throws AWPCP_Exception  If the necessary terms cannot be created.
     */
    public function process_item( $item, $last_item_id ) {
        $categories_registry = $this->categories_registry->get_categories_registry();
        $current_category_id = intval( $item );
        $legacy_category_id  = array_search( $current_category_id, $categories_registry, true );
        $current_category    = get_term_by( 'id', $current_category_id, $this->listing_category_taxonomy );

        if ( ! $current_category ) {
            $this->categories_registry->delete_category_from_registry( $legacy_category_id );

            return $last_item_id;
        }

        $new_term = $this->create_replacement_term( $current_category, $categories_registry );

        if ( is_wp_error( $new_term ) ) {
            $message = 'An error occurred trying to create additional terms to replace category with ID = {category_id}: {error_message}';
            $message = str_replace( '{category_id}', $current_category_id, $message );
            $message = str_replace( '{error_message}', $new_term->get_error_message(), $message );

            throw new AWPCP_Exception( $message );
        }

        $this->replace_term( $current_category, $new_term['term_id'] );

        $this->categories_registry->update_categories_registry( $legacy_category_id, $new_term['term_id'] );
        $this->categories_registry->update_categories_replacements( $current_category_id, $new_term['term_id'] );

        return $last_item_id;
    }

    /**
     * Creates a new category term making sure its ID is greater than any of the IDs
     * used by the categories that were stored on a custom table before 4.0.0.
     *
     * @since 4.0.0
     */
    private function create_replacement_term( $current_category, $categories_registry ) {
        $max_term_id            = $this->get_max_term_id();
        $max_legacy_category_id = max( array_keys( $categories_registry ) );

        if ( $max_term_id <= $max_legacy_category_id ) {
            return $this->wordpress->insert_term_with_id(
                $max_legacy_category_id + 1,
                $current_category->name . ' (' . wp_rand() . ')',
                $this->listing_category_taxonomy,
                [
                    'parent'      => $current_category->parent,
                    'description' => $current_category->term_id,
                ]
            );
        }

        return $this->wordpress->insert_term(
            $current_category->name . ' (' . wp_rand() . ')',
            $this->listing_category_taxonomy,
            [
                'parent'      => $current_category->parent,
                'description' => $current_category->term_id,
            ]
        );
    }

    /**
     * Returns the greatest term_id currently stored in the terms table.
     *
     * @since 4.0.0
     */
    private function get_max_term_id() {
        return intval( $this->db->get_var( "SELECT MAX(term_id) FROM {$this->db->terms}" ) );
    }

    /**
     * @since 4.0.0
     * @throws AWPCP_Exception  If the necessary terms cannot be created.
     */
    private function replace_term( $current_category, $new_category_id ) {
        // When the existing term is deleted, WordPress will set the parent of all
        // the children terms to the parent of the deleted term.
        //
        // We make the existing term a child of the new term so that after the existing
        // term is deleted and the new term is modified the hierarchy is not changed.
        $result = $this->wordpress->update_term(
            $current_category->term_id,
            $this->listing_category_taxonomy,
            [
                'parent' => $new_category_id,
            ]
        );

        if ( is_wp_error( $result ) ) {
            $message = 'An error occurred trying to update the parent of existing category with ID = {category_id}: {error_message}';
            $message = str_replace( '{category_id}', $current_category->term_id, $message );
            $message = str_replace( '{error_message}', $result->get_error_message(), $message );

            throw new AWPCP_Exception( $message );
        }

        $result = $this->wordpress->delete_term(
            $current_category->term_id,
            $this->listing_category_taxonomy,
            [
                'default'       => $new_category_id,
                'force_default' => true,
            ]
        );

        if ( is_wp_error( $result ) ) {
            $message = 'An error occurred trying to delete current category with ID = {category_id}: {error_message}';
            $message = str_replace( '{category_id}', $current_category->term_id, $message );
            $message = str_replace( '{error_message}', $result->get_error_message(), $message );

            throw new AWPCP_Exception( $message );
        }

        $result = $this->wordpress->update_term(
            $new_category_id,
            $this->listing_category_taxonomy,
            [
                'name' => $current_category->name,
                'slug' => $current_category->slug,
            ]
        );

        if ( is_wp_error( $result ) ) {
            $message = 'An error occurred trying to update the replacement term with ID {category_id}: {error_message}';
            $message = str_replace( '{category_id}', $new_category_id, $message );
            $message = str_replace( '{error_message}', $result->get_error_message(), $message );

            throw new AWPCP_Exception( $message );
        }
    }
}
