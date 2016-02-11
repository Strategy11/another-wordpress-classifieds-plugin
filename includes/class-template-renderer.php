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

        $params = array( 'page' => $page, 'content' => $content );

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
