<?php

function awpcp_search_listings_page() {
    return new AWPCP_SearchAdsPage(
        'awpcp-search-ads',
        __('Search Ads', 'another-wordpress-classifieds-plugin'),
        awpcp_template_renderer(),
        awpcp_request()
    );
}

/**
 * @since  2.1.4
 */
class AWPCP_SearchAdsPage extends AWPCP_Page {

    private $request;

    public function __construct( $slug, $title, $template_renderer, $request ) {
        parent::__construct( $slug, $title, $template_renderer );
        $this->request = $request;
    }

    public function get_current_action($default='searchads') {
        return awpcp_request_param('a', $default);
    }

    public function url($params=array()) {
        $page_url = awpcp_get_page_url( 'search-ads-page-name', true );
        return add_query_arg( urlencode_deep( $params ), $page_url );
    }

    public function dispatch() {
        wp_enqueue_style('awpcp-jquery-ui');
        wp_enqueue_script('awpcp-page-search-listings');
        wp_enqueue_script('awpcp-extra-fields');

        $awpcp = awpcp();
        $awpcp->js->localize( 'page-search-ads', array(
            'keywordphrase' => __( 'You did not enter a keyword or phrase to search for. You must at the very least provide a keyword or phrase to search for.', 'another-wordpress-classifieds-plugin' )
        ) );

        return $this->_dispatch();
    }

    protected function _dispatch($default=null) {
        $action = $this->get_current_action();

        switch ($action) {
            case 'dosearch':
                return $this->do_search_step();
            case 'searchads':
            default:
                return $this->search_step();
        }
    }

    protected function get_posted_data() {
        $data = stripslashes_deep( array(
            'query' => $this->request->param('keywordphrase'),
            'category' => $this->request->param('searchcategory'),
            'name' => $this->request->param('searchname'),
            'min_price' => awpcp_parse_money( $this->request->param( 'searchpricemin' ) ),
            'max_price' => awpcp_parse_money( $this->request->param( 'searchpricemax' ) ),
            'regions' => $this->request->param('regions'),
        ) );

        $data = apply_filters( 'awpcp-get-posted-data', $data, 'search', array() );

        return $data;
    }

    protected function validate_posted_data($data, &$errors=array()) {
        $filtered = array_filter($data);

        if (empty($filtered)) {
            $errors[] = __("You did not enter a keyword or phrase to search for. You must at the very least provide a keyword or phrase to search for.", 'another-wordpress-classifieds-plugin');
        }

        if (!empty($data['query']) && strlen($data['query']) < 3) {
            $errors['query'] = __("You have entered a keyword that is too short to search on. Search keywords must be at least 3 letters in length. Please try another term.", 'another-wordpress-classifieds-plugin');
        }

        if (!empty($data['min_price']) && !is_numeric($data['min_price'])) {
            $errors['min_price'] = __("You have entered an invalid minimum price. Make sure your price contains numbers only. Please do not include currency symbols.", 'another-wordpress-classifieds-plugin');
        }

        if (!empty($data['max_price']) && !is_numeric($data['max_price'])) {
            $errors['max_price'] = __("You have entered an invalid maximum price. Make sure your price contains numbers only. Please do not include currency symbols.", 'another-wordpress-classifieds-plugin');
        }

        return empty($errors);
    }

    protected function search_step() {
        return $this->search_form($this->get_posted_data());
    }

    protected function search_form($form, $errors=array()) {
        global $hasregionsmodule, $hasextrafieldsmodule;

        $ui['module-extra-fields'] = $hasextrafieldsmodule;
        $ui['posted-by-field'] = get_awpcp_option('displaypostedbyfield');
        $ui['price-field'] = get_awpcp_option('displaypricefield');
        $ui['allow-user-to-search-in-multiple-regions'] = get_awpcp_option('allow-user-to-search-in-multiple-regions');

        $messages = array( __( 'Use the form below to select the fields on which you want to search. Adding more fields makes for a more specific search. Using fewer fields will make for a broader search.', 'another-wordpress-classifieds-plugin' ) );

        $url_params = wp_parse_args( parse_url( awpcp_current_url(), PHP_URL_QUERY ) );

        foreach ( $form as $name => $value ) {
            if ( isset( $url_params[ $name ] ) ) {
                unset( $url_params[ $name ] );
            }
        }

        $action_url = awpcp_current_url();
        $hidden = array_merge( $url_params, array( 'a' => 'dosearch' ) );

        $params = compact( 'action_url', 'ui', 'form', 'hidden', 'messages', 'errors' );

        $template = AWPCP_DIR . '/frontend/templates/page-search-ads.tpl.php';

        return $this->render($template, $params);
    }

    protected function do_search_step() {
        $form = $this->get_posted_data();

        $errors = array();
        if (!$this->validate_posted_data($form, $errors)) {
            return $this->search_form($form, $errors);
        }

        $output = apply_filters( 'awpcp-search-listings-content-replacement', null, $form );

        if ( is_null( $output ) ) {
            return $this->search_listings( $form );
        } else {
            return $output;
        }
    }

    private function search_listings( $form ) {
        $query = array(
            'context' => 'public-listings',
            's' => $form['query'],
            'category_id' => absint( $form['category'] ),
            'contact_name' => $form['name'],
            'min_price' => $form['min_price'],
            'max_price' => $form['max_price'],
            'regions' => $form['regions'],
            'disabled' => false,
            'posts_per_page' => absint( awpcp_request_param( 'results', get_awpcp_option( 'adresultsperpage', 10 ) ) ),
            'offset' => absint( awpcp_request_param( 'offset', 0 ) ),
            'orderby' => get_awpcp_option( 'search-results-order' ),
        );

        $query = apply_filters( 'awpcp-search-listings-query', $query, $form );

        return awpcp_display_listings( $query, 'search', array(
            'show_intro_message' => true,
            'show_menu_items' => true,
            'show_category_selector' => false,
            'show_pagination' => true,

            'before_list' => $this->build_return_link(),
        ) );
    }

    public function build_return_link() {
        $params = array_merge(stripslashes_deep($_REQUEST), array('a' => 'searchads'));
        $href = add_query_arg(urlencode_deep($params), awpcp_current_url());

        $return_link = '<div class="awpcp-return-to-search-link awpcp-clearboth"><a href="<link-url>"><link-text></a></div>';
        $return_link = str_replace( '<link-url>', esc_url( $href ), $return_link );
        $return_link = str_replace( '<link-text>', __( 'Return to Search', 'another-wordpress-classifieds-plugin' ), $return_link );

        return $return_link;
    }
}
