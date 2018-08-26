<?php

function awpcp_categories_admin_page() {
    return new AWPCP_CategoriesAdminPage(
        awpcp_categories_collection(),
        awpcp_template_renderer(),
        awpcp_request()
    );
}

class AWPCP_CategoriesAdminPage {

    private $categories;
    private $template_renderer;
    private $request;

    public function __construct( $categories, $template_renderer, $request ) {
        $this->categories = $categories;
        $this->template_renderer = $template_renderer;
        $this->request = $request;
    }

    public function dispatch() {
        global $hascaticonsmodule; // Ugh!

        $icons = array(
            array(
                'label' => __( 'Edit Category', 'another-wordpress-classifieds-plugin' ),
                'class' => 'fas fa-pen-square',
                'image' => array(
                    'attributes' => array(
                        'alt' => __( 'Edit Category', 'another-wordpress-classifieds-plugin' ),
                        'src' => AWPCP_URL . "/resources/images/edit_ico.png",
                        'border' => 0,
                    ),
                ),
            ),
            array(
                'label' => __( 'Delete Category', 'another-wordpress-classifieds-plugin' ),
                'class' => 'fas fa-minus-square',
                'image' => array(
                    'attributes' => array(
                        'alt' => __( 'Delete Category', 'another-wordpress-classifieds-plugin' ),
                        'src' => AWPCP_URL . "/resources/images/delete_ico.png",
                        'border' => 0,
                    ),
                ),
            ),
        );

        if ( $hascaticonsmodule == 1 ) {
            $icons[] = array(
                'label' => __( 'Manage Category Icon', 'another-wordpress-classifieds-plugin' ),
                'class' => 'fas fa-shapes',
                'image' => array(
                    'attributes' => array(
                        'alt' => __( 'Manage Category Icon', 'another-wordpress-classifieds-plugin' ),
                        'src' => AWPCP_URL . "/resources/images/icon_manage_ico.png",
                        'border' => 0,
                    ),
                ),
            );
        }

        $children = $this->categories->get_hierarchy();
        // TODO: support order by category_order AS, category_name ASC
        $categories = $this->categories->get_all();

        $offset = (int) $this->request->param( 'offset' );
        $results = max( (int) $this->request->param( 'results', 10 ), 1 );
        $count = 0;

        $category_id = $this->request->param( 'cat_ID' );

        try {
            $category = $this->categories->get( $category_id );
        } catch ( AWPCP_Exception $e ) {
            $category = null;
        }

        $items = awpcp_admin_categories_render_category_items( $categories, $children, $offset, $results, $count );

        $template = AWPCP_DIR . '/templates/admin/manage-categories-admin-page.tpl.php';
        $params = array(
            'icons' => $icons,
            'pager1' => create_pager( count( $categories ), $offset, $results, $tpname='' ),
            'pager2' => create_pager( count( $categories ), $offset, $results, $tpname='' ),
            'form_title' => __( 'Add new category', 'another-wordpress-classifieds-plugin' ),
            'form_values' => array(
                'category_id' => $category_id,
                'category_name' => $category ? $category->name : null,
                'category_parent_id' => $category ? $category->parent : null,
                'category_order' => null,
                'action' => $category ? 'update-category' : 'create-category',
            ),
            'form_submit' => $category ? __( 'Update category', 'another-wordpress-classifieds-plugin' ) : __( 'Add new category', 'another-wordpress-classifieds-plugin' ),
            'items' => $items,
            'offset' => $offset,
            'results' => $results,
        );

        return $this->template_renderer->render_template( $template, $params );
    }
}
