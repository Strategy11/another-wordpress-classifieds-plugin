<?php

if ( ! class_exists( 'Walker' ) ) {
  require_once( ABSPATH . '/wp-includes/class-wp-walker.php' );
}

if ( class_exists( 'Walker' ) ) {

class AWPCP_CategoriesListWalker extends Walker {

    protected $options = array();
    protected $all_elements_count = 0;
    protected $top_level_elements_count = 0;
    protected $elements_count = 0;

    public function __construct() {
        $this->db_fields = array( 'id' => 'id', 'parent' => 'parent' );
    }

    public function configure( $options = array() ) {
        $this->options = wp_parse_args( $options, array(
            'show_in_columns' => 1,
            'show_listings_count' => true,
            'collapsible_categories' => get_awpcp_option( 'collapse-categories-columns' ),
        ) );

        return true;
    }

    public function walk( $elements, $max_depth = 0 ) {
        $this->all_elements_count = count( $elements );

        $container = '<div id="awpcpcatlayout" class="awpcp-categories-list">[categories-list]</div><div class="fixfloat"></div>';
        $container = apply_filters( 'awpcp-categories-list-container', $container, $this->options );

        return str_replace( '[categories-list]', parent::walk( $elements, $max_depth ), $container );
    }

    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        if ( $this->options['collapsible_categories'] ) {
            $output .= '<ul class="sub-categories showcategoriessublist clearfix" data-collapsible="true">';
        } else {
            $output .= '<ul class="sub-categories showcategoriessublist clearfix">';
        }
    }

    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        $output .= '</ul>';
    }

    public function start_el( &$output, $category, $depth = 0, $args = array(), $current_object_id = 0 ) {
        if ( $this->is_first_element_in_row( $depth ) ) {
            $output .= '<ul class="top-level-categories showcategoriesmainlist clearfix">';
        }

        if ( $depth == 0 ) {
            $output .= sprintf( '<li class="columns-%d">', $this->options['show_in_columns'] );
            $output .= '<p class="top-level-category maincategoryclass">';
        } else {
            $output .= '<li>';
        }

        $element = '[category-icon]<a class="[category-class]" href="[category-url]">[category-name]</a> [listings-count][js-handler]';
        $element = str_replace( '[category-icon]', $this->render_category_icon( $category ), $element );
        $element = str_replace( '[category-class]', $depth == 0 ? 'toplevelitem' : '', $element );
        $element = str_replace( '[category-url]', esc_attr( url_browsecategory( $category->id ) ), $element );
        $element = str_replace( '[category-name]', esc_attr( $category->name ), $element );
        $element = str_replace( '[listings-count]', $this->render_listings_count( $category ), $element );
        $element = str_replace( '[js-handler]', $this->render_js_handler( $depth ), $element );

        $output .= $element;

        if ( $depth == 0 ) {
            $output .= '</p>';
        }

        $this->update_elements_count( $depth );
    }

    private function is_first_element_in_row( $depth ) {
        if ( $depth != 0 ) {
            return false;
        }

        if ( $this->top_level_elements_count == 0 ) {
            return true;
        }

        if ( $this->options['show_in_columns'] > 1 && $this->top_level_elements_count % $this->options['show_in_columns'] == 0 ) {
            return true;
        }

        return false;
    }

    private function render_category_icon( $category ) {
        if ( ! function_exists( 'get_category_icon' ) || ! function_exists( 'awpcp_category_icon_url' ) ) {
            return '';
        }

        $category_icon_filename = get_category_icon( $category->id );

        if ( empty( $category_icon_filename ) ) {
            return '';
        }

        $category_icon_url = awpcp_category_icon_url( $category_icon_filename );

        $category_icon = '<a href="[category-url]"><img class="categoryicon" src="[category-icon-url]" alt="[category-name]" border="0" /></a>';
        $category_icon = str_replace( '[category-icon-url]', $category_icon_url, $category_icon );

        return $category_icon;
    }

    private function render_listings_count( $category ) {
        return $this->options['show_listings_count'] ? '(' . $category->listings_count . ')' : '';
    }

    private function render_js_handler( $depth ) {
        if ( $this->options['collapsible_categories'] && $depth == 0 ) {
            return '<a class="js-handler" href="#"><span></span></a>';
        } else {
            return '';
        }
    }

    private function update_elements_count( $depth ) {
        if ( $depth == 0 ) {
            $this->top_level_elements_count = $this->top_level_elements_count + 1;
        }
        $this->elements_count = $this->elements_count + 1;
    }

    public function end_el( &$output, $object, $depth = 0, $args = array() ) {
        $output .= '</li>';

        if ( $this->is_last_element_in_row( $depth ) ) {
            $output .= '</ul>';
        }
    }

    private function is_last_element_in_row( $depth ) {
        if ( $depth != 0 ) {
            return false;
        }

        if ( $this->options['show_in_columns'] > 1 && $this->top_level_elements_count % $this->options['show_in_columns'] == 0 ) {
            return true;
        }

        if ( $this->elements_count == $this->all_elements_count ) {
            return true;
        }

        return false;
    }
}

}
