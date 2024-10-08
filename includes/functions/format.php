<?php

/**
 * @since 3.4
 */
function awpcp_maybe_add_http_to_url( $url ) {
    if ( empty( $url ) || preg_match( '#^(https?|s?ftp)://#', $url ) ) {
        return $url;
    }

    $new_url = sprintf( 'http://%s', $url );

    if ( isValidURL( $new_url ) ) {
        return $new_url;
    } else {
        return $url;
    }
}

/**
 * Copied from http://gistpages.com/2013/06/30/generate_ordinal_numbers_1st_2nd_3rd_in_php
 *
 * @since 3.3.2
 */
function awpcp_ordinalize($num) {
    $suff = 'th';
    if ( ! in_array( ( $num % 100 ), array( 11, 12, 13 ) ) ) {
        switch ( $num % 10 ) {
            case 1:
                $suff = 'st';
                break;
            case 2:
                $suff = 'nd';
                break;
            case 3:
                $suff = 'rd';
                break;
        }
        return "{$num}{$suff}";
    }
    return "{$num}{$suff}";
}

function awpcp_render_template( $template, $params ) {
    $template_renderer = awpcp()->container['TemplateRenderer'];
    return $template_renderer->render_template( $template, $params );
}

function awpcp_admin_page_title() {
    $sections = array_merge( func_get_args(), array( __( 'Classifieds Management System', 'another-wordpress-classifieds-plugin' ) ) );
    return implode( ' &ndash; ', $sections );
}

function awpcp_replace_names_in_message( $message, $names ) {
    $placeholder = count( $names ) === 1 ? '<name>' : '<names>';
    return str_replace( $placeholder, awpcp_string_with_names( $names ), $message );
}

function awpcp_string_with_names( $names ) {
    if ( count( $names ) === 1 ) {
        $string = '<strong>' . $names[0] . '</strong>';
    } else {
        $n_first_names = '<strong>' . implode( '</strong>, <strong>', array_slice( $names, 0, -1 ) ) . '</strong>';
        $last_name = '<strong>' . end( $names ) . '</strong>';

        /* translators: example: <First Name, Second Name, ...> and <Last Name> */
        $string = __( '<comma-separated-names> and <single-name>', 'another-wordpress-classifieds-plugin' );
        $string = str_replace( '<comma-separated-names>', $n_first_names, $string );
        $string = str_replace( '<single-name>', $last_name, $string );
    }

    return $string;
}

function awpcp_get_digits_from_string( $string ) {
    if ( ! preg_match_all( '/\d+/', $string, $matches ) ) {
        return '';
    }

    return implode( '', $matches[0] );
}

/**
 * Based on code found at https://wordpress.stackexchange.com/a/141136/52.
 */
function awpcp_trim_html_content( $content, $word_count ) {
    $allowed_tags = array_keys( wp_kses_allowed_html( 'post' ) );
    $allowed_tags = '<' . implode( '>,<', $allowed_tags ) . '>';

    $content = strip_shortcodes( $content );
    $content = strip_tags( $content, $allowed_tags );

    $tokens = array();
    $output = '';
    $words = 0;

    // Divide the string into tokens; HTML tags, or words, followed by any whitespace
    preg_match_all( '/(<[^>]+>|[^<>\s]+)\s*/u', $content, $tokens );

    foreach ( $tokens[0] as $token ) {
        // Limit reached, continue until , ; ? . or ! occur at the end
        if ( $words >= $word_count && preg_match( '/[\,\;\?\.\!]\s*$/uS', $token ) ) {
            $output .= trim( $token );
            break;
        }

        $output .= $token;
        ++$words;
    }

    return trim( force_balance_tags( $output ) );
}
