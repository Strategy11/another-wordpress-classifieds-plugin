<?php

class AWPCP_AkismetWrapper extends AWPCP_AkismetWrapperBase {

    public function get_user_data() {
        return array(
            'user_ip'      => Akismet::get_ip_address(),
            'user_agent'   => Akismet::get_user_agent(),
            'referrer'     => Akismet::get_referer(),
            'blog'         => get_option('home'),
            'blog_lang'    => get_locale(),
            'blog_charset' => get_option('blog_charset'),
        );
    }

    public function http_post( $request, $path, $ip=null ) {
        return Akismet::http_post( $request, $path, $ip );
    }
}
