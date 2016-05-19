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
                'action' => admin_url( 'admin-ajax.php' ),
                'method' => 'post',
            ),
            '#content' => array(
                array(
                    '#type' => 'first-level-admin-heading',
                    '#content' => $params['form_title'],
                ),
                array(
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
                array(
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
                            '#checkbox_value' => false,
                            '#textfield_value' => awpcp_get_property( $params['fee'], 'title_characters', 100 ),
                        ),
                        array(
                            '#type' => 'admin-form-checkbox-textfield',
                            '#label' => __( 'Limit number of characters in description', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'characters_allowed_in_description',
                            '#checkbox_value' => false,
                            '#textfield_value' => awpcp_get_property( $params['fee'], 'characters', 750 ),
                        ),
                    ),
                ),
                array(
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
                array(
                    '#type' => 'fieldset',
                    '#attributes' => array( 'class' => 'awpcp-admin-form-fieldset' ),
                    '#content' => array(
                        array(
                            '#type' => 'admin-form-checkbox',
                            '#label' => __( 'Offer this plan for certain categories only', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'offer_for_certain_categories_only',
                            '#value' => awpcp_get_property( $params['fee'], 'offer_for_certain_categories_only', false ),
                        ),
                        array(
                            '#type' => 'div',
                            '#attributes' => array(
                                'class' => array( 'awpcp-admin-form-field', 'awpcp-admin-categories-selector' ),
                            ),
                            '#content' => array(
                                array(
                                    '#type' => 'span',
                                    '#content' => array(
                                        array(
                                            '#type' => 'text',
                                            '#content' => __( 'Categories', 'another-wordpress-classifieds-plugin' ),
                                        ),
                                        array(
                                            '#type' => 'a',
                                            '#attributes' => array(
                                                'href' => '#',
                                                'data-categories' => 'all',
                                            ),
                                            '#content_prefix' => '&nbsp;',
                                            '#content' => _x( 'Select All', 'all categories', 'another-wordpress-classifieds-plugin' ),
                                        ),
                                        array(
                                            '#type' => 'text',
                                            '#content' => '&nbsp;|&nbsp;',
                                        ),
                                        array(
                                            '#type' => 'a',
                                            '#attributes' => array(
                                                'href' => '#',
                                                'data-categories' => 'none',
                                            ),
                                            '#content' => _x( 'Deselect All', 'no categories', 'another-wordpress-classifieds-plugin' ),
                                        ),
                                    ),
                                ),
                                array(
                                    '#type' => 'hidden',
                                    '#name' => 'categories[]',
                                    '#value' => 0,
                                ),
                                array(
                                    '#type' => 'div',
                                    '#attributes' => array(
                                        'class' => array( 'cat-checklist', 'category-checklist' ),
                                    ),
                                    '#content' => $this->render_categories_checkbox_list( $params ),
                                ),
                            ),
                        ),
                        array(
                            '#type' => 'admin-form-checkbox-textfield',
                            '#label' => __( 'Limit the number of categories to', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'number_of_categories_allowed',
                            '#checkbox_value' => false,
                            '#textfield_value' => awpcp_get_property( $params['fee'], 'number_of_categories_allowed', 1 ),
                        ),
                    ),
                ),
                array(
                    '#type' => 'fieldset',
                    '#content' => array(
                        array(
                            '#type' => 'admin-form-radio-buttons',
                            '#label' => __( 'How do you want to charge for this plan?', 'another-wordpress-classifieds-plugin' ),
                            '#name' => 'price_model',
                            '#value' => 'flat-price',
                            '#options' => array(
                                'flat-price' => _x( 'Flat price', 'Fee Plan price model option', 'another-wordpress-classifieds-plugin' ),
                                'price-per-category' => _x( 'Different price for different categories', 'Fee Plan price model option', 'another-wordpress-classifieds-plugin' ),
                                'flat-price-plus-price-per-category' => _x( 'A flat price plus an extra amount for each category selected', 'Fee Plan price model option', 'another-wordpress-classifieds-plugin' ),
                            ),
                        ),
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
                        array(
                            '#type' => 'p',
                            '#content' => __( "You'll be able to define a price for each category in the next screen. Click Save & Continue to save the plan details and go to the Pricing section.", 'another-wordpress-classifieds-plugin' ),
                        ),
                    ),
                ),
                array(
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

        return apply_filters( 'awpcp-fee-entry-form-definition', $form_definition, $params );
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

    private function render_categories_checkbox_list( $params ) {
        if ( isset( $params['fee'] ) && isset( $params['fee']->id ) && $params['fee']->id ) {
            $payment_term = $params['fee'];
        } else {
            $payment_term = null;
        }

        $renderer_params = array(
            'payment_term' => $payment_term,
            'selected' => awpcp_get_property( $params['fee'], 'categories', array() ),
        );

        return awpcp_fee_per_category_checkbox_list_renderer()->render( $renderer_params );
    }
}
