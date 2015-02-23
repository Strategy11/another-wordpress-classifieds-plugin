<?php

function awpcp_form_fields() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_FormFields();
    }

    return $instance;
}

class AWPCP_FormFields {

    private $fields = null;

    public function get_fields() {
        if ( is_null( $this->fields ) ) {
            $this->fields = $this->build_fields();
        }

        return $this->fields;
    }

    private function build_fields() {
        $fields = array();

        foreach ( apply_filters( 'awpcp-form-fields', array() ) as $field_slug => $field_constructor ) {
            if ( is_callable( $field_constructor ) ) {
                $fields[ $field_slug ] = call_user_func( $field_constructor, $field_slug );
            }
        }

        return $fields;
    }

    public function get_fields_order() {
        return get_option( 'awpcp-form-fields', $order );
    }

    public function update_fields_order( $order ) {
        update_option( 'awpcp-form-fields-order', $order );
    }

    public function render_fields( $form_values, $form_errors, $listing, $context ) {
        $output = array();

        foreach( $this->get_fields() as $field_slug => $field ) {
            $output[ $field_slug ] = $field->render(
                isset( $form_values[ $field_slug ] ) ? $form_values[ $field_slug ] : '',
                isset( $form_errors[ $field_slug ] ) ? $form_errors[ $field_slug ] : '',
                $listing,
                $context
            );
        }

        return implode( "\n", $output );
    }
}
