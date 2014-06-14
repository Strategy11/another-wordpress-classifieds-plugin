<?php

function awpcp_categories_list_renderer() {
    return new AWPCP_CategoriesRenderer( awpcp_categories_collection(), new AWPCP_CategoriesListWalker() );
}

function awpcp_categories_plain_list_renderer() {
    return new AWPCP_CategoriesRenderer( awpcp_categories_collection(), new AWPCP_CategoriesPlainListWalker() );
}

class AWPCP_CategoriesRenderer {

    private $categories;
    private $walker;

    public function __construct( $categories, $walker ) {
        $this->categories = $categories;
        $this->walker = $walker;
    }

    public function render( $params = array() ) {
        $params = $this->merge_params( $params );
        $transient_key = $this->generate_transient_key( $params );

        try {
            return $this->render_from_cache( $transient_key );
        } catch ( AWPCP_Exception $e ) {
            $output = $this->render_categories_and_update_cache( $params, $transient_key );
            return $output;
        }
    }

    private function merge_params( $params ) {
        return wp_parse_args( $params, array(
            'parent_category_id' => null,
            'show_empty_categories' => true,
            'show_children_categories' => true,
            'show_listings_count' => true,
        ) );
    }

    private function generate_transient_key( $params ) {
        $transient_key_params = apply_filters( 'awpcp-categories-list-transient-key-params', $params );
        $transient_key = md5( maybe_serialize( $transient_key_params ) );
        return $transient_key;
    }

    private function render_from_cache( $transient_key ) {
        // $output = get_transient( $transient_key );
        $output = false;

        if ( $output === false ) {
            throw new AWPCP_Exception( 'No cache entry was found.' );
        }

        return $output;
    }

    private function render_categories_and_update_cache( $params, $transient_key ) {
        if ( is_null( $params['parent_category_id'] ) ) {
            $categories = $this->categories->get_all();
        } else {
            $categories = $this->categories->find( array( 'parent' => $params['parent_category_id'] ) );
        }

        if ( $this->walker->configure( $params ) ) {
            $output = $this->walker->walk( $categories, $params );
            set_transient( $transient_key, $output, YEAR_IN_SECONDS );
        } else {
            $output = '';
        }

        return $output;
    }
}
