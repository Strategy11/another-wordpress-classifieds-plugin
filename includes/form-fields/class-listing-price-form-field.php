<?php

function awpcp_listing_price_form_field( $slug ) {
    return new AWPCP_ListingPriceFormField( $slug, awpcp()->settings );
}

/**
 * TODO: what if that field shouldn't be shown?
 */
class AWPCP_ListingPriceFormField extends AWPCP_FormField {

    protected $settings;

    public function __construct( $slug, $settings ) {
        parent::__construct( $slug );
        $this->settings = $settings;
    }

    public function get_name() {
        return _x( 'Item Price', 'ad details form', 'AWPCP' );
    }

    protected function is_required() {
        return $this->settings->get_option( 'displaypricefieldreqop' );
    }

    protected function format_value( $value ) {
        return $value ? awpcp_format_money( $value, false ) : '';
    }

    public function render( $value, $errors, $listing, $context ) {
        if ( $this->is_required() ) {
            $validators = 'required money';
        } else {
            $validators = 'money';
        }

        $params = array(
            'required' => $this->is_required(),
            'value' => $this->format_value( $value ),
            'errors' => $errors,

            'label' => $this->get_label(),
            'help_text' => '',
            'validators' => $validators,

            'html' => array(
                'id' => str_replace( '_', '-', $this->get_slug() ),
                'name' => $this->get_slug(),
                'readonly' => false,
            ),
        );

        return awpcp_render_template( 'frontend/form-fields/listing-contact-phone-form-field.tpl.php', $params );
    }
}
