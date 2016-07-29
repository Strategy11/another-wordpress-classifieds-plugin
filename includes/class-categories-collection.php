<?php

function awpcp_categories_collection() {
    return new AWPCP_Categories_Collection( awpcp_wordpress() );
}

class AWPCP_Categories_Collection {

    private $taxonomy = AWPCP_CATEGORY_TAXONOMY;

    private $wordpress;

    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    /**
     * @since feature/1112
     */
    public function get( $category_id ) {
        if ( $category_id <= 0 ) {
            $message = __( 'The category ID must be a positive integer, %d was given.', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( sprintf( $message, $category_id ) );
        }

        $category = $this->wordpress->get_term_by( 'id', $category_id, $this->taxonomy );

        if ( $category === false || is_null( $category ) ) {
            $message = __( 'No category was found with ID: %d', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( sprintf( $message, $category_id ) );
        }

        return $category;
    }

    public function get_category_by_name( $name ) {
        if ( empty( $name ) ) {
            $message = __( 'The category name must be a non empty string, %s was given.', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( sprintf( $message, $name ) );
        }

        // See: https://core.trac.wordpress.org/ticket/11311#comment:14
        $sanitized_name = sanitize_term_field( 'name', $name, 0, $this->taxonomy, 'db' );
        $category = $this->wordpress->get_term_by( 'name', $sanitized_name, $this->taxonomy );

        if ( $category === false || is_null( $category ) ) {
            $message = __( 'No category was found with name: %s.', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( sprintf( $message, $name ) );
        }

        return $category;
    }

    /**
     * @since feature/1112
     */
    public function get_all() {
        return $this->find_categories();
    }

    public function find_categories( $args = array() ) {
        $results = $this->wordpress->get_terms( $this->taxonomy, $this->prepare_categories_args( $args ) );

        if ( is_wp_error( $results ) ) {
            return array();
        }

        return $results;
    }

    /**
     * @since feature/1112
     */
    private function prepare_categories_args( $args = array() ) {
        return wp_parse_args( $args, array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false
        ) );
    }

    public function count_categories( $args = array() ) {
        $args = array_merge( $this->prepare_categories_args( $args ), array( 'fields' => 'count' ) );
        return $this->wordpress->get_terms( $this->taxonomy, $args );
    }

    public function get_hierarchy() {
        return $this->wordpress->get_term_hierarchy( $this->taxonomy );
    }

    public function find_by_listing_id( $listing_id ) {
        return $this->wordpress->get_object_terms( $listing_id, $this->taxonomy );
    }

    public function find_top_level_categories() {
        $categories = $this->find_categories();

        foreach ( array_keys( $categories ) as $index ) {
            if ( $categories[ $index ]->parent != 0 ) {
                unset( $categories[ $index ] );
            }
        }

        return $categories;
    }
}
