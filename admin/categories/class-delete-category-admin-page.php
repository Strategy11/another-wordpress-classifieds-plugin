<?php

function awpcp_delete_category_admin_page() {
    return new AWPCP_Delete_Category_Admin_Page(
        awpcp_categories_logic(),
        awpcp_categories_collection(),
        awpcp_template_renderer(),
        awpcp_router(),
        awpcp_request()
    );
}

class AWPCP_Delete_Category_Admin_Page {

    private $categories_logic;
    private $categories;
    private $template_renderer;
    private $router;
    private $request;

    public function __construct( $categories_logic, $categories, $template_renderer, $router, $request ) {
        $this->categories_logic = $categories_logic;
        $this->categories = $categories;
        $this->template_renderer = $template_renderer;
        $this->router = $router;
        $this->request = $request;
    }

    public function dispatch() {
        $category_id = $this->request->param( 'cat_ID' );
        $operation_confirmed = $this->request->post( 'awpcp-confirm-delete-category' );

        try {
            $category = $this->categories->get( $category_id );
        } catch ( AWPCP_Exception $e ) {
            $message = __( "The category you are trying to delte doesn't exist.", 'another-wordpress-classifieds-plugin' );
            awpcp_flash( $message, 'error' );

            return $this->redirect_to_main_page();
        }

        if ( $operation_confirmed ) {
            try {
                $this->try_to_delete_category( $category );
            } catch ( AWPCP_Exception $e ) {
                $message = __( 'There was an error trying to delete the category. <error-message>', 'another-wordpress-classifieds-plugin' );
                $message = str_replace( '<error-message>', $e->getMessage(), $message );

                awpcp_flash( $message, 'error' );
            }
        } else {
            return $this->show_delete_category_form( $category );
        }

        return $this->redirect_to_main_page();
    }

    private function try_to_delete_category( $category ) {
        $target_category_id = $this->request->post( 'target_category_id' );
        $should_move_listings = ads_exist_cat( $category->term_id );

        try {
            $target_category = $this->categories->get( $target_category_id );
        } catch ( AWPCP_Exception $e ) {
            if ( $should_move_listings ) {
                $message = __( 'There was an error trying to load the selected category. <error-message>', 'another-wordpress-classifieds-plugin' );
                $message = str_replace( '<error-message>', $target_category_error, $message );

                throw new AWPCP_Exception( $message );
            }

            $target_category = null;
        }

        if ( $should_move_listings ) {
            $this->categories_logic->delete_category_moving_listings_to( $category, $target_category );
        } else {
            $this->categories_logic->delete_category_and_associated_listings( $category, $target_category );
        }

        awpcp_flash( __( 'The category was deleted successfully', 'another-wordpress-classifieds-plugin' ) );
    }

    private function show_delete_category_form( $category ) {
        $template = AWPCP_DIR . '/templates/admin/delete-category-admin-page.tpl.php';

        $form_title = __( 'Are you sure you want to delete "<category-name>" category?', 'another-wordpress-classifieds-plugin' );
        $form_title = str_replace( '<category-name>', $category->name, $form_title );

        $params = array(
            'category_has_listings' => ads_exist_cat( $category->term_id ),
            'category_has_children' => category_has_children( $category->term_id ),
            'form_title' => $form_title,
            'form_values' => array(
                'category_id' => $category->term_id,
                'action' => 'delete-category',
            ),
            'form_submit' => __( 'Delete category', 'another-wordpress-classifieds-plugin' ),
            'form_cancel' => __( 'Cancel', 'another-wordpress-classifieds-plugin' ),
            'offset' => (int) $this->request->param( 'offset' ),
            'results' => max( (int) $this->request->param( 'results', 10 ), 1 ),
        );

        return $this->template_renderer->render_template( $template, $params );
    }

    private function redirect_to_main_page() {
        $this->router->serve_admin_page( array( 'parent' => 'awpcp.php', 'page' => 'awpcp-admin-categories' ) );

        return false; // halt rendering process. Ugh!
    }
}
