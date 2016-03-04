<?php

function awpcp_categories_logic() {
    return new AWPCP_Categories_Logic(
        AWPCP_CATEGORY_TAXONOMY,
        awpcp_listings_api(),
        awpcp_listings_collection(),
        awpcp_wordpress()
    );
}

class AWPCP_Categories_Logic {

    private $taxonomy;

    private $listings;
    private $listings_logic;
    private $wordpress;

    public function __construct( $taxonomy, $listings_logic, $listings, $wordpress ) {
        $this->taxonomy = $taxonomy;
        $this->listings_logic = $listings_logic;
        $this->listings = $listings;
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
            throw new AWPCP_Exception( __( 'There was an error trying to update a category. The ID of the category is required.' ) );
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

    public function move_category( $category, $target_category ) {
        if ( $category->term_id == $target_category->term_id ) {
            $message = __( 'The category to be moved and the target category can not be the same.', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( $message );
        }

        $category->parent = $target_category->term_id;

        $this->update_category( $category );
    }

    public function delete_category_moving_listings_to( $category, $target_category ) {
        if ( $category->term_id == $target_category->term_id ) {
            throw new AWPCP_Exception( __( 'The move-to category and the category that is going to be deleted must be different.', 'another-wordpress-classifieds-plugin' ) );
        }

        // wp_delete_term() moves children terms to the parent of the
        // deleted term. Here we move the category that is going to be deleted
        // to the target category before deleting it, to take advantage
        // of that behaviour.
        $category->parent = $target_category->term_id;
        $this->update_category( $category );

        return $this->wordpress->delete_term(
            $category->term_id,
            $this->taxonomy,
            array( 'default' => $target_category->term_id )
        );
    }

    public function delete_category_and_associated_listings( $category, $target_category = null ) {
        if ( is_object( $target_category ) && $category->term_id == $target_category->term_id ) {
            throw new AWPCP_Exception( __( 'The move-to category and the category that is going to be deleted must be different.', 'another-wordpress-classifieds-plugin' ) );
        }

        $listings = $this->listings->find_listings( array(
            'tax_query' => array(
                array(
                    'taxonomy' => $this->taxonomy,
                    'field' => 'term_id',
                    'terms' => $category->term_id,
                    'include_children' => false,
                )
            )
        ) );

        try {
            foreach ( $listings as $listing ) {
                $this->listings_logic->delete_listing( $listing );
            }
        } catch ( AWPCP_Exception $e ) {
            $message = __( "The category couldn't be deleted because there was an error trying to delete one of the associated listings: <error-message>", 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '<error-message>', $e->getMessage(), $message );

            throw new AWPCP_Exception( $message );
        }

        if ( ! is_null( $target_category ) ) {
            // wp_delete_term() moves children terms to the parent of the
            // deleted term. Here we move the category that is going to be deleted
            // to the target category before deleting it, to take advantage
            // of that behaviour.
            $category->parent = $target_category->term_id;
            $this->update_category( $category );
        }

        return $this->wordpress->delete_term( $category->term_id, $this->taxonomy );
    }
}
