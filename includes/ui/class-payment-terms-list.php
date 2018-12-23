<?php
/**
 * @package AWPCP\UI
 */

// phpcs:disable

function awpcp_payment_terms_list() {
    return new AWPCP_Payment_Terms_List(
        awpcp_payments_api(),
        awpcp_template_renderer()
    );
}

/**
 * @SuppressWarnings(PHPMD)
 */
class AWPCP_Payment_Terms_List {

    private $payments;
    private $template_renderer;

    private $submitted_data;

    public function __construct( $payments, $template_renderer ) {
        $this->payments = $payments;
        $this->template_renderer = $template_renderer;
    }

    public function get_data() {
        return $this->from_view_to_model( $this->submitted_data );
    }

    private function from_view_to_model( $selected_value ) {
        if ( ! preg_match( '/(.+)-(.+)-(money|credits)/', $selected_value, $matches ) ) {
            return null;
        }

        $payment_term_type = $matches[1];
        $payment_term_id   = $matches[2];
        $payment_type      = $matches[3];

        return array(
            'payment_term' => $this->payments->get_payment_term( $payment_term_id, $payment_term_type ),
            'payment_type' => $payment_type,
        );
    }

    public function render( $model_data, $options = array() ) {
        $options = wp_parse_args( $options, array(
            'payment_terms' => null,
            'transaction'   => null,
        ) );

        if ( is_null( $options['payment_terms'] ) ) {
            $options['payment_terms'] = $this->payments->get_payment_terms();
        }

        $params = array(
            'payment_terms' => $this->get_payment_terms_definitions( $options['payment_terms'], $options['transaction'] ),
            'show_payment_terms' => $this->should_show_payment_terms( $options['payment_terms'] ),
            'selected_payment_option' => $this->from_model_to_view( $model_data ),
            'show_currency_payment_option' => $this->payments->is_currency_accepted(),
            'show_credits_payment_option' => $this->payments->is_credit_accepted(),
        );

        $template = AWPCP_DIR . '/templates/components/payment-terms-list.tpl.php';

        return $this->template_renderer->render_template( $template, $params );
    }

    private function get_payment_terms_definitions( $available_payment_terms, $transaction ) {
        $payment_terms_definitions = array();

        foreach ( $available_payment_terms as $payment_term_type => $payment_terms ) {
            foreach ( $payment_terms as $payment_term ) {
                $payment_terms_definitions[] = $this->get_payment_term_definition( $payment_term, $transaction );
            }
        }

        return $payment_terms_definitions;
    }

    private function get_payment_term_definition( $payment_term, $transaction ) {
        $payment_term_price   = $this->payments->calculate_payment_term_price( $payment_term, 'currency', $transaction );
        $payment_term_credits = $this->payments->calculate_payment_term_price( $payment_term, 'credits', $transaction );

        $summary_currency = __( '{payment-term-name} &ndash; {payment-term-duration} ({payment-term-price})', 'another-wordpress-classifieds-plugin' );
        $summary_currency = str_replace( '{payment-term-name}', $payment_term->name, $summary_currency );
        $summary_currency = str_replace( '{payment-term-duration}', $payment_term->duration_amount . ' ' . $payment_term->get_duration_interval(), $summary_currency );
        $summary_currency = str_replace( '{payment-term-price}', awpcp_format_money( $payment_term_price ), $summary_currency );

        $summary_credits = __( '{payment-term-name} &ndash; {payment-term-duration} ({payment-term-price} credits)', 'another-wordpress-classifieds-plugin' );
        $summary_credits = str_replace( '{payment-term-name}', $payment_term->name, $summary_credits );
        $summary_credits = str_replace( '{payment-term-duration}', $payment_term->duration_amount . ' ' . $payment_term->get_duration_interval(), $summary_credits );
        $summary_credits = str_replace( '{payment-term-price}', awpcp_format_integer( $payment_term_credits ), $summary_credits );

        return array(
            'id'                => $payment_term->id,
            'type'              => $payment_term->type,
            'attributes' => $this->get_payment_term_attributes( $payment_term ),
            'name' => $payment_term->name,
            'description' => $payment_term->description,
            'duration_amount' => $payment_term->duration_amount,
            'duration_interval' => $payment_term->get_duration_interval(),
            'features' => $this->get_payment_term_features_definition( $payment_term ),
            'price' => $this->get_payment_term_price_definition( $payment_term, $payment_term_price, $payment_term_credits ),
            'extra' => array(),
            'summary-currency'  => $summary_currency,
            'summary-credits'   => $summary_credits,
        );
    }

    private function get_payment_term_attributes( $payment_term ) {
        $attributes = array(
            'data-id' => "{$payment_term->type}-{$payment_term->id}",
            'data-number-of-categories-allowed' => esc_attr( absint( $payment_term->number_of_categories_allowed ) ),
            'data-categories' => esc_attr( json_encode( array_map( 'absint', $payment_term->categories ) ) ),
        );

        return apply_filters( 'awpcp-payment-terms-list-payment-term-attributes', $attributes, $payment_term );
    }

    private function get_payment_term_features_definition( $payment_term ) {
        return array(
            'listings' => $this->get_number_of_listings_allowed_feature_description( $payment_term ),
            'images' => $this->get_number_of_images_allowed_feature_description( $payment_term ),
            'characters-in-title' => $this->get_number_of_characters_allowed_in_title_feature_description( $payment_term ),
            'characters-in-content' => $this->get_number_of_characters_allowed_in_content_feature_description( $payment_term ),
        );
    }

    private function get_number_of_listings_allowed_feature_description( $payment_term ) {
        $description = _n( '<number-of-listings> listing allowed.', '<number-of-listings> listings allowed.', $payment_term->ads, 'another-wordpress-classifieds-plugin' );
        return str_replace( '<number-of-listings>', '<strong>' . $payment_term->ads . '</strong>', $description );
    }

    private function get_number_of_images_allowed_feature_description( $payment_term ) {
        $number_of_images_allowed = $payment_term->images;

        if ( $number_of_images_allowed == 0 ) {
            return __( 'No images allowed.', 'another-wordpress-classifieds-plugin' );
        }

        $description = _n( '<number-of-images> image allowed.', '<number-of-images> images allowed.', $number_of_images_allowed, 'another-wordpress-classifieds-plugin' );

        return str_replace( '<number-of-images>', '<strong>' . $number_of_images_allowed . '</strong>', $description );
    }

    private function get_number_of_characters_allowed_in_title_feature_description( $payment_term ) {
        if ( $payment_term->title_characters == 0 ) {
            return '<strong>' . __( "No characters limit in listing's title.", 'another-wordpress-classifieds-plugin' ) . '</strong>';
        }

        $description = __( "Up to <characters-count> characters in listing's title.", 'another-wordpress-classifieds-plugin' );

        return str_replace( '<characters-count>', '<strong>' . $payment_term->title_characters . '</strong>', $description );
    }

    private function get_number_of_characters_allowed_in_content_feature_description( $payment_term ) {
        if ( $payment_term->characters == 0 ) {
            return '<strong>' . __( "No characters limit in listing's content.", 'another-wordpress-classifieds-plugin' ) . '</strong>';
        }

        $description = __( "Up to <characters-count> characters in listing's content.", 'another-wordpress-classifieds-plugin' );

        return str_replace( '<characters-count>', '<strong>' . $payment_term->characters . '</strong>', $description );
    }

    private function get_payment_term_price_definition( $payment_term, $payment_term_price, $payment_term_credits ) {
        $currency_button_label = __( 'Purchase this plan', 'another-wordpress-classifieds-plugin' );
        $credits_button_label = __( 'Pay for this plan with credits', 'another-wordpress-classifieds-plugin' );

        if ( $payment_term->price == 0 ) {
            $currency_button_label = __( 'Use this plan for free!', 'another-wordpress-classifieds-plugin' );
        }

        if ( $payment_term->credits == 0 ) {
            $credits_button_label = __( 'Use this plan for free!', 'another-wordpress-classifieds-plugin' );
        }

        return array(
            'currency_amount'       => awpcp_format_money( $payment_term_price ),
            'currency_button_label' => __( 'Select', 'another-wordpress-classifieds-plugin' ),//$currency_button_label,
            'currency_option'       => $this->get_payment_option( $payment_term, 'money' ),

            'credits_amount'        => $payment_term_credits,
            'credits_label'         => __( 'credits', 'another-wordpress-classifieds-plugin' ),
            'credits_button_label'  => __( 'Select', 'another-wordpress-classifieds-plugin' ),//$credits_button_label,
            'credits_option'        => $this->get_payment_option( $payment_term, 'credits' ),
        );
    }

    private function get_payment_option( $payment_term, $payment_type ) {
        return "{$payment_term->type}-{$payment_term->id}-{$payment_type}";
    }

    private function should_show_payment_terms( $payment_terms ) {
        if ( $this->payments->payments_enabled() ) {
            return true;
        }

        if ( count( $payment_terms ) > 1 ) {
            return true;
        }

        return true;
    }

    private function from_model_to_view( $payment_options ) {
        if ( is_array( $payment_options ) ) {
            $payment_term = $payment_options['payment_term'];
            $payment_type = $payment_options['payment_type'];
        } else {
            $payment_term = null;
        }

        if ( is_object( $payment_term ) ) {
            return $this->get_payment_option( $payment_term, $payment_type );
        } else {
            return '';
        }
    }

    public function handle_request( $request ) {
        $this->submitted_data = $request->post( 'payment_term' );
    }
}
