<?php

class AWPCP_Test_Manage_Categories_Admin_Page extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->categories = Phake::mock( 'AWPCP_Categories_Collection' );
        $this->template_renderer = Phake::mock( 'AWPCP_Template_Renderer' );
        $this->request = Phake::mock( 'AWPCP_Request' );
    }

    public function test_template() {
        $form_title = 'Add Category';
        $form_values = array(
            'action' => null,
            'category_id' => null,
            'category_name' => 'A Category',
            'category_parent_id' => null,
            'category_order' => null,
        );
        $form_submit = null;
        $offset = null;
        $results = null;
        $pager1 = $pager2 = null;
        $items = array();
        $icons = array();

        ob_start();
        include( AWPCP_DIR . '/templates/admin/manage-categories-admin-page.tpl.php' );
        $output = ob_get_contents();
        ob_end_clean();
    }
}
