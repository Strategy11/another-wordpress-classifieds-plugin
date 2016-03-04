<?php

function awpcp_create_category_admin_page() {
    return new AWPCP_Create_Category_Admin_Page(
        awpcp_categories_logic(),
        awpcp_router(),
        awpcp_request()
    );
}

class AWPCP_Create_Category_Admin_Page {

    private $categories_data_mapper;
    private $router;
    private $request;

    public function __construct( $categories_data_mapper, $router, $request ) {
        $this->categories_data_mapper = $categories_data_mapper;
        $this->router = $router;
        $this->request = $request;
    }

    public function dispatch() {
        // TODO: store category order somewhere...
        $category_order = absint( $this->request->param( 'category_order' ) );
        $category_data = array(
            'name' => stripcslashes( $this->request->param( 'category_name' ) ),
            'parent' => absint( $this->request->param( 'category_parent_id' ) ),
        );

        try {
            $this->categories_data_mapper->create_category( $category_data );
            awpcp_flash( __( 'The new category was successfully added.', 'another-wordpress-classifieds-plugin' ) );
        } catch ( AWPCP_Exception $e ) {
            awpcp_flash( $e->getMessage(), 'error' );
        }

        $this->router->serve_admin_page( array( 'parent' => 'awpcp.php', 'page' => 'awpcp-admin-categories' ) );

        return false; // halt rendering process. Ugh!
    }
}
