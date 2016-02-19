<?php

function awpcp_browse_categories_page() {
    return new AWPCP_BrowseCategoriesPage(
        'awpcp-browse-categories',
        __( 'Browse Categories', 'another-wordpress-classifieds-plugin' ),
        awpcp_template_renderer()
    );
}

class AWPCP_BrowseCategoriesPage extends AWPCP_BrowseAdsPage {

    public function get_current_action($default='browsecat') {
        return awpcp_request_param('a', $default);
    }

    public function url($params=array()) {
        $url = awpcp_get_page_url('browse-categories-page-name');
        return add_query_arg( urlencode_deep( $params ), $url );
    }
}
