<?php

function awpcp_listing_contact_name_form_field( $slug ) {
    return new AWPCP_ListingContactNameFormField( $slug );
}

/**
 * TODO: what if that field shouldn't be shown?
 */
class AWPCP_ListingContactNameFormField extends AWPCP_FormField {

    public function get_name() {
        return _x( 'Name of Person to Contact', 'ad details form', 'AWPCP' );
    }

    protected function is_required() {
        return true;
    }

    public function is_readonly( $value ) {
        if ( empty( $value ) ) {
            return false;
        }

        if ( awpcp_current_user_is_moderator() ) {
            return false;
        }

        return true;
    }

    public function render( $value, $errors, $listing, $context ) {
        if ( $this->is_required() ) {
            $validators = 'required';
        } else {
            $validators = '';
        }

        $params = array(
            'required' => $this->is_required(),
            'value' => $value,
            'errors' => $errors,

            'label' => $this->get_label(),
            'validators' => $validators,

            'html' => array(
                'id' => str_replace( '_', '-', $this->get_slug() ),
                'name' => $this->get_slug(),
                'readonly' => $this->is_readonly( $value ),
            ),
        );

        return awpcp_render_template( 'frontend/form-fields/listing-contact-name-form-field.tpl.php', $params );
    }
}
