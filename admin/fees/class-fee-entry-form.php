<?php

function awpcp_fee_entry_form() {
    return new AWPCP_Fee_Entry_Form();
}

class AWPCP_Fee_Entry_Form implements AWPCP_HTML_Element {

    public function build( $params = array() ) {
        $form_definition = array(
            '#type' => 'tr',
            '#attributes' => array(
                'id' => 'edit-1',
                'class' => 'inline-edit-row quick-edit-row alternate inline-editor',
            ),
            '#content' => array(
                'column' => array(
                    '#type' => 'td',
                    '#attributes' => array(
                        'class' => 'colspanchange',
                        'colspan' => $params['columns'],
                    ),
                    '#content' => array(
                        'form' => $this->build_form( $params ),
                    ),
                ),
            ),
        );

        return apply_filters( 'awpcp-fee-entry-form-definition', $form_definition, $params );
    }

    private function build_form( $params ) {
        $id = awpcp_get_property( $params['entry'], 'id', false );

        return array(
            '#type' => 'form',
            '#attributes' => array(
                'class' => array( 'awpcp-admin-fee-entry-form' ),
                'action' => admin_url( 'admin-ajax.php' ),
                'method' => 'post',
            ),
            '#content' => array(
                'left-column' => $this->build_left_column( $id, $params ),
                'right-column' => $this->build_right_column( $id, $params ),
                'form-buttons' => $this->build_submit_buttons( $id ),
            ),
        );
    }

    private function build_left_column( $id, $params ) {
        return array(
            '#type' => 'fieldset',
            '#attributes' => array(
                'class' => 'inline-edit-col-left',
            ),
            '#content_prefix' => '<div class="inline-edit-col">',
            '#content' => array(
                array(
                    '#type' => 'h4',
                    '#attributes' => array(
                        'class' => 'awpcp-inline-form-title',
                    ),
                    '#content' => $id ? __( 'Edit Fee Plan Details', 'another-wordpress-classifieds-plugin' ) : __( 'New Fee Plan Details', 'another-wordpress-classifieds-plugin' ),
                ),
                array(
                    '#type' => 'inline-form-textfield',
                    '#label' => __( 'Name', 'another-wordpress-classifieds-plugin' ),
                    '#name' => 'name',
                    '#value' => awpcp_get_property( $params['entry'], 'name' ),
                ),
                array(
                    '#type' => 'inline-form-textarea',
                    '#label' => __( 'Description', 'awpcp-subscriptions' ),
                    '#name' => 'description',
                    '#value' => awpcp_get_property( $params['entry'], 'name' ),
                    '#cols' => 54,
                    '#rows' => 6,
                ),
                array(
                    '#type' => 'inline-form-textfield',
                    '#label' => __( 'Price', 'another-wordpress-classifieds-plugin' ),
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-half-width-field',
                            'awpcp-inline-form-half-width-field-left',
                            'awpcp-admin-fee-entry-form-price-field',
                        ),
                    ),
                    '#name' => 'price',
                    '#value' => number_format( awpcp_get_property( $params['entry'], 'price', 0 ), 2 ),
                    '#wrap_class' => array( 'input-text-wrap', 'formatted-price' ),
                ),
                array(
                    '#type' => 'inline-form-textfield',
                    '#label' => __( 'Credits', 'another-wordpress-classifieds-plugin' ),
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-half-width-field',
                            'awpcp-inline-form-half-width-field-right',
                            'awpcp-admin-fee-entry-form-credits-field',
                        ),
                    ),
                    '#name' => 'credits',
                    '#value' => number_format( awpcp_get_property( $params['entry'], 'credits', 0 ), 0 ),
                    '#wrap_class' => array( 'input-text-wrap', 'formatted-price' ),
                ),
                array(
                    '#type' => 'inline-form-textfield',
                    '#label' => __( 'Duration', 'another-wordpress-classifieds-plugin' ),
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-half-width-field',
                            'awpcp-inline-form-half-width-field-left',
                            'awpcp-admin-fee-entry-form-duration-field',
                        ),
                    ),
                    '#name' => 'duration_amount',
                    '#value' => esc_attr( awpcp_get_property( $params['entry'], 'duration_amount', 30 ) ),
                    '#wrap_class' => array( 'input-text-wrap', 'formatted-price' ),
                ),
                array(
                    '#type' => 'inline-form-select',
                    '#label' => __( 'Units', 'another-wordpress-classifieds-plugin' ),
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-half-width-field',
                            'awpcp-inline-form-half-width-field-right',
                            'awpcp-admin-fee-entry-form-units-field',
                        ),
                    ),
                    '#name' => 'duration_interval',
                    '#value' => esc_attr( awpcp_get_property( $params['entry'], 'duration_interval' ) ),
                    '#options' => $this->get_duration_interval_options(),
                ),
            ),
            '#content_suffix' => '</div>',
        );
    }

    private function get_duration_interval_options() {
        $values = AWPCP_Fee::get_duration_intervals();
        $labels = array_map( array( 'AWPCP_Fee', 'get_duration_interval_label' ), $values );

        return array_combine( $values, $labels );
    }

    private function build_right_column( $id, $params ) {
        if ( $id ) {
            $characters_allowed_in_title = $params['entry']->get_characters_allowed_in_title();
            $characters_allowed_in_description = $params['entry']->get_characters_allowed();
            $is_featured = $params['entry']->featured;
            $is_private = $params['entry']->private;
        } else {
            $characters_allowed_in_title = get_awpcp_option( 'characters-allowed-in-title', 0 );
            $characters_allowed_in_description = get_awpcp_option( 'maxcharactersallowed', 0 );
            $is_featured = false;
            $is_private = false;
        }

        return array(
            '#type' => 'fieldset',
            '#attributes' => array(
                'class' => array( 'inline-edit-col-right', 'inline-edit-categories' ),
            ),
            '#content_prefix' => '<div class="inline-edit-col">',
            '#content' => array(
                array(
                    '#type' => 'label',
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-section-header',
                        ),
                    ),
                    '#content_prefix' => '<span class="title">',
                    '#content' => __( 'Images &amp; Regions Limits', 'another-wordpress-classifieds-plugin' ),
                    '#content_suffix' => '</span>',
                ),
                array(
                    '#type' => 'inline-form-textfield',
                    '#label' => __( 'Images', 'another-wordpress-classifieds-plugin' ),
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-half-width-field',
                            'awpcp-inline-form-half-width-field-left',
                            'awpcp-admin-fee-entry-form-images-allowed-field',
                        ),
                    ),
                    '#name' => 'images_allowed',
                    '#value' => esc_attr( awpcp_get_property( $params['entry'], 'images', 1 ) ),
                ),
                array(
                    '#type' => 'inline-form-textfield',
                    '#label' => __( 'Regions', 'another-wordpress-classifieds-plugin' ),
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-half-width-field',
                            'awpcp-inline-form-half-width-field-right',
                            'awpcp-admin-fee-entry-form-regions-allowed-field',
                        ),
                    ),
                    '#name' => 'regions_allowed',
                    '#value' => esc_attr( awpcp_get_property( $params['entry'], 'regions', 1 ) ),
                ),
                array(
                    '#type' => 'label',
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-section-header',
                        ),
                    ),
                    '#content_prefix' => '<span class="title">',
                    '#content' => __( 'Number of characters allowed (0 means no limit)', 'another-wordpress-classifieds-plugin' ),
                    '#content_suffix' => '</span>',
                ),
                array(
                    '#type' => 'inline-form-textfield',
                    '#label' => __( 'Title', 'another-wordpress-classifieds-plugin' ),
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-half-width-field',
                            'awpcp-inline-form-half-width-field-left',
                            'awpcp-admin-fee-entry-form-title-characters-field',
                        ),
                    ),
                    '#name' => 'characters_allowed_in_title',
                    '#value' => esc_attr( $characters_allowed_in_title ),
                ),
                array(
                    '#type' => 'inline-form-textfield',
                    '#label' => __( 'Description', 'another-wordpress-classifieds-plugin' ),
                    '#attributes' => array(
                        'class' => array(
                            'awpcp-inline-form-half-width-field',
                            'awpcp-inline-form-half-width-field-right',
                            'awpcp-admin-fee-entry-form-description-characters-field',
                        ),
                    ),
                    '#name' => 'characters_allowed_in_description',
                    '#value' => esc_attr( $characters_allowed_in_description ),
                ),
                array(
                    '#type' => 'inline-form-checkbox',
                    '#label' => __( 'This Fee is for Featured Listings', 'another-wordpress-classifieds-plugin' ),
                    '#name' => 'featured',
                    '#value' => esc_attr( $is_featured ),
                    '#helptext' => '',
                ),
                array(
                    '#type' => 'inline-form-checkbox',
                    '#label' => __( 'Hide Fee from public?', 'another-wordpress-classifieds-plugin' ),
                    '#name' => 'private',
                    '#value' => esc_attr( $is_private ),
                ),
            ),
            '#content_suffix' => '</div>',
        );
    }

    private function build_submit_buttons( $id ) {
        return array(
            '#type' => 'p',
            '#attributes' => array(
                'class' => array(
                    'submit',
                    'inline-edit-save',
                    'awpcp-clearboth',
                    'awpcp-clearfix',
                )
            ),
            '#content' => array(
                array(
                    '#type' => 'inline-form-button',
                    '#attributes' => array(
                        'class' => 'button-secondary cancel alignleft',
                    ),
                    '#label' => __( 'Cancel', 'another-wordpress-classifieds-plugin' ),
                ),
                array(
                    '#type' => 'inline-form-button',
                    '#attributes' => array(
                        'class' => 'button-primary save alignright',
                    ),
                    '#label' => $id ? __('Update', 'another-wordpress-classifieds-plugin') : __('Add', 'another-wordpress-classifieds-plugin'),
                ),
                array(
                    '#type' => 'input',
                    '#attributes' => array(
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $id,
                    ),
                ),
                array(
                    '#type' => 'input',
                    '#attributes' => array(
                        'type' => 'hidden',
                        'name' => 'action',
                        'value' => $_REQUEST['action'],
                    ),
                ),
            ),
        );
    }
}
