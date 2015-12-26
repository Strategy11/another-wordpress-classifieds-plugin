<?php

function awpcp_category_shortcode() {
    return new AWPCP_CategoryShortcode( $GLOBALS['wpdb'], awpcp_request() );
}

class AWPCP_CategoryShortcode {

    private $db;
    private $request;

    public function __construct( $db, $request ) {
        $this->db = $db;
        $this->request = $request;
    }

    public function render( $attrs ) {
        $attrs = $this->get_shortcode_attrs( $attrs );

        $output = apply_filters( 'awpcp-category-shortcode-content-replacement', null, $attrs );

        if ( is_null( $output ) ) {
            return $this->render_shortcode_content( $attrs );
        } else {
            return $output;
        }
    }

    private function get_shortcode_attrs( $attrs ) {
        if ( ! isset( $attrs['show_categories_list'] ) && isset( $attrs['children'] ) ) {
            $attrs['show_categories_list'] = $attrs['children'];
        }

        $attrs = shortcode_atts( array(
            'id' => 0,
            'children' => true,
            'items_per_page' => 10,
            'show_categories_list' => true,
        ), $attrs );

        $attrs['children'] = awpcp_parse_bool( $attrs['children'] );
        $attrs['show_categories_list'] = awpcp_parse_bool( $attrs['show_categories_list'] );

        return $attrs;
    }

    private function render_shortcode_content( $attrs ) {
        extract( $attrs );

        $category = $id > 0 ? AWPCP_Category::find_by_id( $id ) : null;
        $children = awpcp_parse_bool( $children );

        if ( is_null( $category ) ) {
            return __('Category ID must be valid for Ads to display.', 'category shortcode', 'another-wordpress-classifieds-plugin');
        }

        if ( $attrs['show_categories_list'] ) {
            $categories_list = awpcp_categories_list_renderer()->render( array(
                'parent_category_id' => $category->id,
                'show_listings_count' => true,
            ) );

            $options = array(
                'before_pagination' => array(
                    10 => array(
                        'categories-list' => $categories_list,
                    ),
                ),
            );
        } else {
            $options = array();
        }

        $query = array(
            'context' => 'public-listings',
            'category_id' => $category->id,
            'include_listings_in_children_categories' => $children,
            'limit' => absint( $this->request->param( 'results', $items_per_page ) ),
            'offset' => absint( $this->request->param( 'offset', 0 ) ),
            'orderby' => get_awpcp_option( 'groupbrowseadsby' ),
        );

        // required so awpcp_display_ads shows the name of the current category
        $_REQUEST['category_id'] = $category->id;

        return awpcp_display_listings_in_page( $query, 'category-shortcode', $options );
    }
}
