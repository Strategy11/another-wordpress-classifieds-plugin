<?php
/**
 * @package AWPCP\Listings
 */

// phpcs:disable

function awpcp_categories_collection() {
    return new AWPCP_Categories_Collection( 'awpcp_listing_category', awpcp_wordpress() );
}

class AWPCP_Categories_Collection {

    /**
     * @var string
     */
    private $taxonomy;

    private $wordpress;

    /**
     * @param string $taxonomy  The name of the listings category taxonomy.
     */
    public function __construct( $taxonomy, $wordpress ) {
        $this->taxonomy  = $taxonomy;
        $this->wordpress = $wordpress;
    }

    /**
     * @since 4.0.0
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
     * @since 4.0.0
     */
    public function get_all() {
        return $this->find_categories();
    }

    public function find_categories( $args = array() ) {
        $results = $this->wordpress->get_terms( $this->prepare_categories_args( $args ) );

        if ( is_wp_error( $results ) ) {
            return array();
        }

        return $results;
    }

    /**
     * @since 4.0.0
     */
    private function prepare_categories_args( $args = array() ) {
        $args = wp_parse_args( $args, array(
            'taxonomy' => $this->taxonomy,
            'hide_empty' => false,
            'meta_query' => [],
        ) );

        if ( ! isset( $args['orderby'] ) ) {
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_awpcp_order';
        }

        return $args;
    }

    public function count_categories( $args = array() ) {
        $args = array_merge( $this->prepare_categories_args( $args ), array( 'fields' => 'count' ) );

        if ( 'meta_value_num' === $args['orderby'] && '_awpcp_order' === $args['meta_key'] ) {
            unset( $args['meta_key'] );
        }

        unset( $args['orderby'], $args['order'] );

        return intval( $this->wordpress->get_terms( $args ) );
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
