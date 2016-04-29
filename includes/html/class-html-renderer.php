<?php

function awpcp_html_renderer() {
    return new AWPCP_HTML_Renderer();
}

class AWPCP_HTML_Renderer {

    public function render( $element ) {
        return $this->render_element( $element );
    }

    public function render_element( $element_definition ) {
        $element_definition = $this->normalize_element_definition( $element_definition );
        $element_renderer = $this->get_element_renderer( $element_definition );
        return $element_renderer->render_element( $this, $element_definition );
    }

    private function normalize_element_definition( $element_definition ) {
        return wp_parse_args( $element_definition, array(
            '#content_prefix' => '',
            '#content' => '',
            '#content_suffix' => '',
            '#attributes' => array(),
            '#escape' => false,
        ) );
    }

    private function get_element_renderer( $element_definition ) {
        $type_name = str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $element_definition['#type'] ) ) );
        $class_name = 'AWPCP_HTML_' . $type_name . '_Renderer';
        $constructor_function = strtolower( $class_name );

        if ( isset( $this->element_renderers[ $class_name ] ) ) {
            return $this->element_renderers[ $class_name ];
        } else if ( function_exists( $constructor_function ) ) {
            $this->element_renderers[ $class_name ] = call_user_func( $constructor_function );
        } else {
            $this->element_renderers[ $class_name ] = awpcp_html_default_element_renderer();
        }

        return $this->element_renderers[ $class_name ];
    }

    public function render_content( $element_definition, $content ) {
        $output = array();

        if ( $element_definition['#content_prefix'] ) {
            $output[] = $element_definition['#content_prefix'];
        }

        if ( is_array( $content ) ) {
            $output[] = $this->render_elements( $content );
        } else {
            $output[] = $content;
        }

        if ( $element_definition['#content_suffix'] ) {
            $output[] = $element_definition['#content_suffix'];
        }

        return implode( '', $output );
    }

    private function render_elements( $element_definitions ) {
        $output = array();

        foreach ( $element_definitions as $element_definition ) {
            $output[] = $this->render_element( $element_definition );
        }

        return implode( '', $output );
    }
}

interface AWPCP_HTML_Element_Renderer {

    public function render_element( $html_renderer, $element_definition );
}

function awpcp_html_default_element_renderer() {
    return new AWPCP_HTML_Default_Element_Renderer();
}

class AWPCP_HTML_Default_Element_Renderer implements AWPCP_HTML_Element_Renderer {

    public function render_element( $html_renderer, $element_definition ) {
        $element = '<<tag><attributes>><content></<tag>>';
        $element = str_replace( '<tag>', $element_definition['#type'], $element );

        if ( ! empty( $element_definition['#attributes'] ) ) {
            $element = str_replace(
                '<attributes>',
                ' ' . awpcp_html_attributes( $element_definition['#attributes'] ),
                $element
            );
        } else {
            $element = str_replace( '<attributes>', '', $element );
        }

        $element = str_replace(
            '<content>',
            $html_renderer->render_content( $element_definition, $element_definition['#content'] ),
            $element
        );

        return $element;
    }
}

function awpcp_html_text_renderer() {
    return new AWPCP_HTML_Text_Renderer();
}

class AWPCP_HTML_Text_Renderer implements AWPCP_HTML_Element_Renderer {

    public function render_element( $html_renderer, $element_definition ) {
        if ( $element_definition['#escape'] ) {
            $content = esc_html( $element_definition['#content'] );
        } else {
            $content = $element_definition['#content'];
        }

        return $html_renderer->render_content( $element_definition, $content );
    }
}

function awpcp_html_inline_form_textfield_renderer() {
    return new AWPCP_HTML_Inline_Form_Textfield_Renderer();
}

class AWPCP_HTML_Inline_Form_Textfield_Renderer implements AWPCP_HTML_Element_Renderer {

    public function render_element( $html_renderer, $element_definition ) {
        $element = '<label<attributes>>';
        $element.= '    <span class="title"><label-text></span>';
        $element.= '    <span class="input-text-wrap"><input type="text" value="<value>" name="<name>"></span>';
        $element.= '</label>';

        $element = str_replace(
            '<attributes>',
            ' ' . awpcp_html_attributes( $element_definition['#attributes'] ),
            $element
        );

        $element = str_replace( '<label-text>', $element_definition['#label'], $element );
        $element = str_replace( '<value>', esc_attr( $element_definition['#value'] ), $element );
        $element = str_replace( '<name>', esc_attr( $element_definition['#name'] ), $element );

        return $element;
    }
}

function awpcp_html_inline_form_textarea_renderer() {
    return new AWPCP_HTML_Inline_Form_Textarea_Renderer();
}

class AWPCP_HTML_Inline_Form_Textarea_Renderer implements AWPCP_HTML_Element_Renderer {

    public function render_element( $html_renderer, $element_definition ) {
        $attributes = array(
            'name' => $element_definition['#name'],
            'rows' => $element_definition['#rows'],
            'cols' => $element_definition['#cols'],
        );

        $element = '<label><span class="title"><label-text></span></label>';
        $element.= '<textarea <attributes>><value></textarea>';

        $element = str_replace( '<label-text>', $element_definition['#label'], $element );
        $element = str_replace( '<attributes>', awpcp_html_attributes( $attributes ), $element );
        $element = str_replace( '<value>', esc_html( $element_definition['#value'] ), $element );

        return $element;
    }
}

function awpcp_html_inline_form_select_renderer() {
    return new AWPCP_HTML_Inline_Form_Select_Renderer();
}

class AWPCP_HTML_Inline_Form_Select_Renderer implements AWPCP_HTML_Element_Renderer {

    public function render_element( $html_renderer, $element_definition ) {
        $element = '<label<attributes>>';
        $element.= '    <span class="title"><label-text></span>';
        $element.= '    <select name="<name>"><options></select>';
        $element.= '</label>';

        $element = str_replace( '<label-text>', $element_definition['#label'], $element );
        $element = str_replace( '<name>', $element_definition['#name'], $element );

        $element = str_replace(
            '<attributes>',
            ' ' . awpcp_html_attributes( $element_definition['#attributes'] ),
            $element
        );

        $element = str_replace(
            '<options>',
            awpcp_html_options(array(
                'options' => $element_definition['#options'],
                'current-value' => $element_definition['#value'],
            )),
            $element
        );

        return $element;
    }
}

function awpcp_html_inline_form_checkbox_renderer() {
    return new AWPCP_HTML_Inline_Form_Checkbox_Renderer();
}

class AWPCP_HTML_Inline_Form_Checkbox_Renderer implements AWPCP_HTML_Element_Renderer {

    public function render_element( $html_renderer, $element_definition ) {
        $attributes = array(
            'type' => 'checkbox',
            'value' => 1,
            'name' => $element_definition['#name'],
        );

        if ( $element_definition['#value'] ) {
            $attributes['checked'] = 'checked';
        }

        $element = '<label>';
        $element.= '<input<attributes>>';
        $element.= '<span class="checkbox-title"><label-text></span>';
        $element.= '</label>';

        $element = str_replace( '<attributes>', ' ' . awpcp_html_attributes( $attributes ), $element );
        $element = str_replace( '<label-text>', $element_definition['#label'], $element );

        return $element;
    }
}

function awpcp_html_inline_form_button_renderer() {
    return new AWPCP_HTML_Inline_Form_Button_Renderer();
}

class AWPCP_HTML_Inline_Form_Button_Renderer implements AWPCP_HTML_Element_Renderer {

    public function render_element( $html_renderer, $element_definition ) {
        return $html_renderer->render_element(array(
            '#type' => 'a',
            '#attributes' => array_merge(
                $element_definition['#attributes'],
                array(
                    'title' => esc_attr( $element_definition['#label'] ),
                    'href' => '#inline-edit',
                )
            ),
            '#content' => $element_definition['#label']
        ));
    }
}
