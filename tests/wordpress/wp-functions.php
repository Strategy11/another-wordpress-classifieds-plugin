<?php

function __( $string ) { return $string; }
function _x( $string ) { return $string; }
function _n( $string ) { return $string; }
function absint( $number ) { return intval( $number ); }
function esc_url( $url ) { return $url; }
function esc_url_raw( $url ) { return $url; }
function esc_html() { return array_pop( func_get_args() ); }
function esc_attr() { return array_pop( func_get_args() ); }

function wp_rand() { return rand( 1, 1000 ); }
function wp_unslash( $data ) { return $data; }
function wp_json_encode() { return call_user_func( 'json_encode', func_get_args() ); }

function add_query_arg() { return array_pop( func_get_args() ); }
function remove_query_arg() { return array_pop( func_get_args() ); }
function admin_url( $url ) { return $url; }
function wp_nonce_url( $url ) { return $url; }

function shortcode_atts( $pairs, $atts ) { return array_merge( $pairs, $atts ); }
