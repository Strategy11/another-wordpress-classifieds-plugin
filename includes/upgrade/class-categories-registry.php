<?php
/**
 * @package AWPCP\Upgrade
 */

/**
 * Constructor function for Categories Registry class.
 */
function awpcp_categories_registry() {
    return new AWPCP_Categories_Registry( awpcp_wordpress() );
}

/**
 * Provides access to translation arrays for pre-4.0.0 categories that were
 * converted into listing category taxonomy terms and terms that had to be
 * replaced by other terms with greater IDs to avoid ID collisions.
 */
class AWPCP_Categories_Registry {

    /**
     * @var WordPress
     */
    private $wordpress;

    /**
     * Constructor.
     */
    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    /**
     * Retuns a translation array with IDs of pre-4.0.0 categories as the keys
     * and the IDs of the corresponding listing category term as the values.
     */
    public function get_categories_registry() {
        return $this->get_array_option( 'awpcp-legacy-categories' );
    }

    /**
     * Gets the value of a WordPress option always returning an array.
     *
     * If the option does not exists or the current value is not an array, the
     * function returns an empty array.
     *
     * @since 4.0.0
     */
    private function get_array_option( $option_name ) {
        $data = $this->wordpress->get_option( $option_name );

        if ( ! is_array( $data ) ) {
            return [];
        }

        return $data;
    }

    /**
     * Adds information about a migrated category to the registry.
     *
     * @param int $category_id  The ID of a pre-4.0.0 category.
     * @param int $term_id      The ID of the term that replaced the old category.
     */
    public function update_categories_registry( $category_id, $term_id ) {
        $this->update_array_option( 'awpcp-legacy-categories', $category_id, $term_id );
    }

    /**
     * @since 4.0.0
     */
    private function update_array_option( $option_name, $key, $value ) {
        $data = $this->get_array_option( $option_name );

        $data[ $key ] = $value;

        $this->wordpress->update_option( $option_name, $data, false );
    }

    /**
     * @since 4.0.0
     */
    public function delete_category_from_registry( $category_id ) {
        $this->delete_entry_from_array_option( 'awpcp-legacy-categories', $category_id );
    }

    /**
     * @since 4.0.0
     */
    private function delete_entry_from_array_option( $option_name, $key ) {
        $data = $this->get_array_option( $option_name );

        unset( $data[ $key ] );

        $this->wordpress->update_option( $option_name, $data, false );
    }

    /**
     * Categories replacements is an indexed array where keys are the IDs of
     * listing category taxonomy terms that were each replaced with the term
     * with ID equal to the value associated with that key.
     *
     * [
     *   {old_term_id} => {new_term_id},
     *   ...
     * ]
     *
     * @since 4.0.0
     */
    public function get_categories_replacements() {
        return $this->get_array_option( 'awpcp_categories_replacements_for_id_collision_fix' );
    }

    /**
     * See get_categories_replacements() for a description of categories replacements.
     *
     * @since 4.0.0
     */
    public function update_categories_replacements( $old_term_id, $new_term_id ) {
        $this->update_array_option( 'awpcp_categories_replacements_for_id_collision_fix', $old_term_id, $new_term_id );
    }

    /**
     * Returns the ID of listing category terms that match the ID of pre-4.0.0 categories.
     *
     * @since 4.0.0
     */
    public function get_id_collisions() {
        $categories_registry = $this->get_categories_registry();

        return array_intersect( array_keys( $categories_registry ), array_values( $categories_registry ) );
    }
}
