<?php

/**
 * TODO: move this into a different class and pass an instance of it to EditCreditPlan and
 *        CreateCreditPlan ajax handlers.
 */
class AWPCP_AddEditTableEntryAjaxHandler extends AWPCP_TableEntryActionAjaxHandler {

    protected function render_entry_row( $plan ) {
        ob_start();
            $this->page->get_table()->single_row( $plan );
            $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    protected function render_entry_form( $template, $entry ) {
        $params = array(
            'entry' => $entry,
            'columns' => count( $this->page->get_table()->get_columns() ),
        );

        return awpcp_render_template( $template, $params );
    }
}
