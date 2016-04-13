<?php

function awpcp_template_renderer() {
    return new AWPCP_Template_Renderer();
}

class AWPCP_Template_Renderer {

    public function render_page_template( $page, $page_template, $content_template, $content_params = array() ) {
        if ( $content_template === 'content' ) {
            $content = $content_params;
        } else {
            $content = $this->render_template( $content_template, $content_params );
        }

        if ( method_exists( $page, 'should_show_title' ) ) {
            $should_show_title = $page->should_show_title();
        } else {
            $should_show_title = true;
        }

        if ( method_exists( $page, 'show_sidebar' ) ) {
            $show_sidebar = $page->show_sidebar();
        } else {
            $show_sidebar = false;
        }

        $params = array(
            'page' => $page,
            'page_slug' => $page->page,
            'page_title' => $page->title(),
            'should_show_title' => $should_show_title,
            'show_sidebar' => $show_sidebar,
            'content' => $content,
        );

        return $this->render_template( $page_template, $params );
    }

    public function render_template( $template, $params = array() ) {
        if ( file_exists( $template ) ) {
            $template_file = $template;
        } else if ( file_exists( AWPCP_DIR . '/templates/' . $template ) ) {
            $template_file = AWPCP_DIR . '/templates/' . $template;
        } else {
            $template_file = null;
        }

        if ( ! is_null( $template_file ) ) {
            ob_start();
            extract( $params );
            include( $template_file );
            $output = ob_get_contents();
            ob_end_clean();
        } else {
            $output = sprintf( 'Template %s not found!', str_replace( AWPCP_DIR, '', $template ) );
        }

        return $output;
    }
}
