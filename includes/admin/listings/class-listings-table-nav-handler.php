<?php
/**
 * @package AWPCP\Tests
 */

/**
 * Handler for the tablenav section of the listings table.
 */
class AWPCP_ListingsTableNavHandler {

    /**
     * @var object
     */
    private $request;

    /**
     * @param object $request   An instance of Request.
     * @since 4.0.0
     */
    public function __construct( $request ) {
        $this->request = $request;
    }

    /**
     * @param object $query     An instance of WP_Query.
     * @since 4.0.0
     */
    public function pre_get_posts( $query ) {
        if ( ! $query->is_main_query() ) {
            return;
        }

        $selected_category = $this->get_selected_category();

        if ( ! $selected_category ) {
            return;
        }

        $query->query_vars['classifieds_query']['category'] = $selected_category;
    }

    /**
     * @since 4.0.0
     */
    private function get_selected_category() {
        return absint( $this->request->param( 'awpcp_category' ) );
    }

    /**
     * @since 4.0.0
     */
    public function restrict_listings() {
        $selected_category = $this->get_selected_category();

        $params = array(
            'label'       => false,
            'name'        => 'awpcp_category',
            'placeholder' => _x( 'All categories', 'category filter placeholder', 'another-wordpress-classifieds-plugin' ),
            'selected'    => $selected_category,
        );

        echo awpcp_categories_selector()->render( $params ); // XSS Ok.
    }
}
