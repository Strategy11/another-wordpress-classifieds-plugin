<?php

function awpcp_categories_data_mapper() {
    return new AWPCP_Categories_Data_Mapper(
        AWPCP_CATEGORY_TAXONOMY,
        awpcp_wordpress()
    );
}

class AWPCP_Categories_Data_Mapper {

    private $taxonomy;
    private $wordpress;

    public function __construct( $taxonomy, $wordpress ) {
        $this->taxonomy = $taxonomy;
        $this->wordpress = $wordpress;
    }

    public function create_category( $category ) {
        if ( is_array( $category ) ) {
            $category = (object) $category;
        }

        $data = $this->get_category_data( $category );
        $term_info = $this->wordpress->insert_term( $data['name'], $this->taxonomy, $data );

        if ( is_wp_error( $term_info ) ) {
            $message = __( 'There was an error trying to create a category: <error-message>.', 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '<error-message>', $term_info->get_error_message(), $message );
            throw new AWPCP_Exception( $message );
        }

        /**
         * @since 3.3
         * TODO: fix handlers now that we pass more parameters
         */
        do_action( 'awpcp-category-added', $term_info['term_id'], $category );

        return $term_info['term_id'];
    }

    /**
     * TODO: Add suport for category order...
     */
    private function get_category_data( $category ) {
        $category_data = array();

        if ( isset( $category->name ) && ! empty( $category->name ) ) {
            $category_data['name'] = $category->name;
        } else if ( ! isset( $category->name ) || empty( $category->name ) ) {
            throw new AWPCP_Exception( __( 'The name of the Category is required.', 'another-wordpress-classifieds-plugin' ) );
        }

        if ( isset( $category->parent ) && isset( $category->term_id ) && $category->parent == $category->term_id ) {
            throw new AWPCP_Exception( __( 'The ID of the parent category and the ID of the category must be different.' ) );
        } else if ( isset( $category->parent ) ) {
            $category_data['parent'] = $category->parent;
        }

        /**
         * TODO: fix now that we pass a different array
         */
        return apply_filters( 'awpcp-category-data', $category_data, $category );
    }

    public function update_category( $category ) {
        if ( ! isset( $category->term_id ) ) {
            throw new AWPCP_Exception( __( 'The ID of the category is required.' ) );
        }

        $data = $this->get_category_data( $category );
        $term_info = $this->wordpress->update_term( $category->term_id, $this->taxonomy, $data );

        if ( is_wp_error( $term_info ) ) {
            $message = __( 'There was an error trying to update a category: <error-message>.', 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '<error-message>', $term_info->get_error_message(), $message );
            throw new AWPCP_Exception( $message );
        }

        /**
         * @since 3.3
         * TODO: fix handlers now that we pass more parameters
         */
        do_action( 'awpcp-category-edited', $term_info['term_id'], $category );

        return $term_info['term_id'];
    }
}
