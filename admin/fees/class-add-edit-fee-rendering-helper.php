<?php

function awpcp_add_edit_fee_rendering_helper() {
    $page = awpcp_fees_admin_page();

    return new AWPCP_Add_Edit_Fee_Rendering_Helper(
        $page,
        awpcp_add_edit_table_entry_rendering_helper( $page ),
        awpcp_html_renderer()
    );
}

class AWPCP_Add_Edit_Fee_Rendering_Helper {

    private $page;
    private $table_rendering_helper;
    private $html_renderer;

    public function __construct( $page, $table_rendering_helper, $html_renderer ) {
        $this->page = $page;
        $this->table_rendering_helper = $table_rendering_helper;
        $this->html_renderer = $html_renderer;
    }

    public function render_entry_row( $entry ) {
        return $this->table_rendering_helper->render_entry_row( $entry );
    }

    public function render_entry_form( $entry, $form ) {
        $params = array(
            'entry' => $entry,
            'columns' => count( $this->page->get_table()->get_columns() ),
        );

        return $this->html_renderer->render( $form->build( $params ) );
    }
}
