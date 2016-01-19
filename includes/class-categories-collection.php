<?php

function awpcp_categories_collection() {
    return new AWPCP_Categories_Collection( awpcp_wordpress() );
}

class AWPCP_Categories_Collection {

    private $taxonomy = 'awpcp_listing_category';

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

        if ( is_null( $category ) ) {
            $message = __( 'No category was found with ID: %d', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( sprintf( $message, $category_id ) );
        }

        return $category;
    }

    /**
     * @since feature/1112
     */
    public function get_all() {
        $args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false
        );

        return $this->wordpress->get_terms( $this->taxonomy, $args );
    }

    public function get_hierarchy() {
        return $this->wordpress->get_term_hierarchy( $this->taxonomy );
    }
}
