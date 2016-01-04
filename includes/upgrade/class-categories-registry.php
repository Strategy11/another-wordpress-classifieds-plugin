<?php

function awpcp_categories_registry() {
    return new AWPCP_Categories_Registry( awpcp_wordpress() );
}

class AWPCP_Categories_Registry {

    private $wordpress;

    public function __construct( $wordpress ) {
        $this->wordpress = $wordpress;
    }

    public function get_categories_registry() {
        return $this->wordpress->get_option( 'awpcp-legacy-categories' );
    }

    public function update_categories_registry( $category_id, $term_id ) {
        $categories_registry = $this->get_categories_registry();

        if ( is_array( $categories_registry ) ) {
            $categories_registry[ $category_id ] = $term_id;
        } else {
            $categories_registry = array( $category_id => $term_id );
        }

        $this->wordpress->update_option( 'awpcp-legacy-categories', $categories_registry, false );
    }
}
