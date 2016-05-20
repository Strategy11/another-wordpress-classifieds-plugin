<?php

function awpcp_fee_details_form() {
    return new AWPCP_Fee_Details_Form();
}

class AWPCP_Fee_Details_Form implements AWPCP_HTML_Element {

    public function build( $params = array() ) {
        $id = awpcp_get_property( $params['fee'], 'id', false );

        $form_definition = array(
            '#type' => 'form',
            '#attributes' => array(
                'class' => array( 'awpcp-fee-details-form', 'awpcp-admin-form' ),
                'method' => 'post',
            ),
            '#content' => array(
                'header' => array(
                    '#type' => 'first-level-admin-heading',
                    '#content' => $params['form_title'],
                ),
                'name-and-description' => array(
                    '#type' => 'fieldset',
                    '#attributes' => array( 'class' => 'awpcp-admin-form-fieldset' ),
                    '#content' => array(
                        array(
                            '#type' => 'admin-form-textfield',
                            '#label' => __( 'Name', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'name',
                            '#value' => awpcp_get_property( $params['fee'], 'name' ),
                        ),
                        array(
                            '#type' => 'admin-form-textarea',
                            '#label' => __( 'Description', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'description',
                            '#value' => awpcp_get_property( $params['fee'], 'description' ),
                            '#cols' => 54,
                            '#rows' => 6,
                        ),
                    ),
                ),
                'features' => $this->get_features_fields_definition( $params ),
                'featured-and-private' => array(
                    '#type' => 'fieldset',
                    '#attributes' => array( 'class' => 'awpcp-admin-form-fieldset' ),
                    '#content' => array(
                        array(
                            '#type' => 'admin-form-checkbox',
                            '#label' => __( 'This fee is for Featured Listings', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'use_for_featured_listings',
                            '#value' => awpcp_get_property( $params['fee'], 'featured', false ),
                        ),
                        array(
                            '#type' => 'admin-form-checkbox',
                            '#label' => __( 'This is a private fee plan', 'another-wordpress-classifieds-plugin' ),
                            '#description' => __( 'The plan will be hidden from public view. It will be used for existing listings or special listings that only admins can create.', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'is_private',
                            '#value' => awpcp_get_property( $params['fee'], 'private', false ),
                        ),
                    ),
                ),
                'price-model' => array(
                    '#type' => 'fieldset',
                    '#content' => array(
                        array(
                            '#type' => 'admin-form-textfield',
                            '#attributes' => array( 'class' => 'awpcp-admin-form-text-field-with-left-label' ),
                            '#label' => __( 'Price (currency)', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'price_in_currency',
                            '#value' => awpcp_format_money_without_currency_symbol( awpcp_get_property( $params['fee'], 'price', 0 ) ),
                        ),
                        array(
                            '#type' => 'admin-form-textfield',
                            '#attributes' => array( 'class' => 'awpcp-admin-form-text-field-with-left-label' ),
                            '#label' => __( 'Price (credits)', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'price_in_credits',
                            '#value' => intval( awpcp_get_property( $params['fee'], 'credits', 0 ) ),
                        ),
                    ),
                ),
                'submit-buttons' => array(
                    '#type' => 'div',
                    '#attributes' => array(
                        'class' => 'awpcp-admin-form-submit-buttons',
                    ),
                    '#content' => array(
                        array(
                            '#type' => 'input',
                            '#attributes' => array(
                                'class' => array( 'button', 'button-primary' ),
                                'type' => 'submit',
                                'name' => 'save',
                                'value' => __( 'Save', 'another-wordpress-classifieds-plugin' ),
                            )
                        ),
                        array(
                            '#type' => 'input',
                            '#attributes' => array(
                                'class' => array( 'button', 'button-primary' ),
                                'type' => 'submit',
                                'name' => 'save_and_continue',
                                'value' => __( 'Save & Continue', 'another-wordpress-classifieds-plugin' ),
                            )
                        ),
                        array(
                            '#type' => 'input',
                            '#attributes' => array(
                                'class' => array( 'button' ),
                                'type' => 'button',
                                'name' => 'cancel',
                                'value' => __( 'Cancel', 'another-wordpress-classifieds-plugin' ),
                            )
                        ),
                    ),
                ),
            ),
        );

        return apply_filters( 'awpcp-fee-details-form-definition', $form_definition, $params );
    }

    private function get_duration_field_definition( $params ) {
        return array(
            '#type' => 'div',
            '#attributes' => array(
                'class' => array( 'awpcp-fee-duration-field' ),
            ),
            '#content' => array(
                array(
                    '#type' => 'label',
                    '#attributes' => array(
                        'for' => 'awpcp-fee-duration-field'
                    ),
                    '#content' => __( 'Duration', 'another-wordpress-classifieds-plugin' ),
                ),
                array(
                    '#type' => 'div',
                    '#content' => array(
                        array(
                            '#type' => 'input',
                            '#attributes' => array(
                                'id' => 'awpcp-fee-duration-field',
                                'type' => 'text',
                                'name' => 'duration_amount',
                                'value' => awpcp_get_property( $params['fee'], 'duration_amount', 30 ),
                            ),
                        ),
                        array(
                            '#type' => 'select',
                            '#attributes' => array(
                                'name' => 'duration_interval',
                            ),
                            '#options' => $this->get_duration_interval_options(),
                            '#value' => awpcp_get_property( $params['fee'], 'duration_interval', AWPCP_Fee::INTERVAL_DAY ),
                        ),
                    ),
                ),
            ),
        );
    }

    private function get_duration_interval_options() {
        $values = AWPCP_Fee::get_duration_intervals();
        $labels = array_map( array( 'AWPCP_Fee', 'get_duration_interval_label' ), $values );

        return array_combine( $values, $labels );
    }

    private function get_features_fields_definition( $params ) {
        $characters_allowed_in_title = awpcp_get_property( $params['fee'], 'title_characters' );
        $characters_allowed_in_description = awpcp_get_property( $params['fee'], 'characters' );

        if ( $characters_allowed_in_title === 0 ) {
            $limit_number_of_characters_in_title = false;
            $characters_allowed_in_title = 100;
        } else {
            $limit_number_of_characters_in_title = true;
        }

        if ( $characters_allowed_in_description === 0 ) {
            $limit_number_of_characters_in_description = false;
            $characters_allowed_in_description = 750;
        } else {
            $limit_number_of_characters_in_description = true;
        }

        return array(
            '#type' => 'fieldset',
            '#attributes' => array( 'class' => 'awpcp-admin-form-fieldset' ),
            '#content' => array(
                'duration' => $this->get_duration_field_definition( $params ),
                array(
                    '#type' => 'admin-form-textfield',
                    '#attributes' => array( 'class' => 'awpcp-admin-form-text-field-with-left-label' ),
                    '#label' => __( 'Images allowed for this plan', 'another-wordpress-classifieds-plugin' ),
                    '#name' => 'images_allowed',
                    '#value' => awpcp_get_property( $params['fee'], 'images', 0 ),
                ),
                array(
                    '#type' => 'admin-form-textfield',
                    '#attributes' => array( 'class' => 'awpcp-admin-form-text-field-with-left-label' ),
                    '#label' => __( 'Regions allowed for this plan', 'another-wordpress-classifieds-plugin' ),
                    '#name' => 'regions_allowed',
                    '#value' => awpcp_get_property( $params['fee'], 'regions', 1 ),
                ),
                array(
                    '#type' => 'admin-form-checkbox-textfield',
                    '#label' => __( 'Limit number of characters in title', 'another-wordpress-classifieds-plugin' ),
                    '#name' => 'characters_allowed_in_title',
                    '#checkbox_value' => $limit_number_of_characters_in_title,
                    '#textfield_value' => $characters_allowed_in_title,
                ),
                array(
                    '#type' => 'admin-form-checkbox-textfield',
                    '#label' => __( 'Limit number of characters in description', 'another-wordpress-classifieds-plugin' ),
                    '#name' => 'characters_allowed_in_description',
                    '#checkbox_value' => $limit_number_of_characters_in_description,
                    '#textfield_value' => $characters_allowed_in_description,
                ),
            ),
        );
    }
}
