<?php

function awpcp_categories_switcher() {
    return new AWPCP_Categories_Switcher(
        awpcp_query(),
        awpcp_request()
    );
}

class AWPCP_Categories_Switcher {

    private $query;
    private $request;

    public function __construct( $query, $request ) {
        $this->query = $query;
        $this->request = $request = $request;
    }

    public function render( $params = array() ) {
        if ( $this->query->is_browse_listings_page() || $this->query->is_browse_categories_page() ) {
            $action_url = awpcp_current_url();
        } else {
            $action_url = awpcp_get_browse_categories_page_url();
        }

        $category_id = $this->request->get_category_id();

        $category_dropdown_params = wp_parse_args( $params, array(
            'context' => 'search',
            'name' => 'category_id',
            'selected' => $category_id,
        ) );

        $hidden = array_filter( array(
            'results' => $this->request->param( 'results' ),
            'offset' => 0,
        ), 'strlen' );

        ob_start();
        include( AWPCP_DIR . '/templates/frontend/category-selector.tpl.php' );
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
