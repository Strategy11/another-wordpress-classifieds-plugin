<?php

function awpcp_html_admin_form_checkbox_textfield_renderer() {
    return new AWPCP_HTML_Admin_Form_Checkbox_Textfield_Renderer();
}

class AWPCP_HTML_Admin_Form_Checkbox_Textfield_Renderer implements AWPCP_HTML_Element_Renderer {

    public function render_element( $html_renderer, $element_definition ) {
        $form_field_id = "awpcp-admin-form-checkbox-textfield-{$element_definition['#name']}";

        if ( $element_definition['#checkbox_value'] ) {
            $checbox_attributes = array( 'checked' => 'checked' );
        } else {
            $checbox_attributes = array();
        }

        $form_field_definition = array(
            '#type' => 'div',
            '#attributes' => $this->get_form_field_attributes( $element_definition ),
            '#content' => array(
                array(
                    '#type' => 'label',
                    '#attributes' => array( 'for' => $form_field_id ),
                    '#content' => array(
                        array(
                            '#type' => 'input',
                            '#attributes' => array(
                                'type' => 'hidden',
                                'value' => false,
                                'name' => "{$element_definition['#name']}_enabled",
                            ),
                        ),
                        array(
                            '#type' => 'input',
                            '#attributes' => array_merge( $checbox_attributes, array(
                                'id' => $form_field_id,
                                'type' => 'checkbox',
                                'value' => true,
                                'name' => "{$element_definition['#name']}_enabled",
                            ) ),
                        ),
                        array(
                            '#type' => 'text',
                            '#content' => $element_definition['#label'],
                        ),
                    ),
                ),
                array(
                    '#type' => 'input',
                    '#attributes' => array(
                        'id' => $form_field_id,
                        'type' => 'text',
                        'value' => $element_definition['#textfield_value'],
                        'name' => $element_definition['#name'],
                        'data-usableform' => "enable-if:{$element_definition['#name']}_enabled",
                    ),
                ),
            ),
        );

        return $html_renderer->render_element( $form_field_definition );
    }

    private function get_form_field_attributes( $element_definition ) {
        $form_field_attributes = awpcp_parse_html_attributes( $element_definition['#attributes'] );

        $form_field_attributes['class'][] = 'awpcp-admin-form-text-field-with-left-label';
        $form_field_attributes['class'][] = 'awpcp-admin-form-textfield';

        return $form_field_attributes;
    }
}
