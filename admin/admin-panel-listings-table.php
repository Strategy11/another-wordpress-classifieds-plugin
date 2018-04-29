<?php
/**
 * @package AWPCP\Admin
 */

// phpcs:disable

function awpcp_listings_table( $page, $params = array() ) {
    return new AWPCP_Listings_Table(
        $page,
        $params,
        awpcp_categories_collection(),
        awpcp_listing_renderer(),
        awpcp_listings_collection()
    );
}

/**
 * @SuppressWarnings(PHPMD)
 */
class AWPCP_Listings_Table extends WP_List_Table {

    private $params;
    private $selected_category_id;
    private $items_per_page;
    private $total_items;

    private $page;

    private $categories;
    private $listing_renderer;
    private $listings;

    public function __construct( $page, $params, $categories, $listing_renderer, $listings ) {
        parent::__construct( array_merge( array( 'plural' => 'awpcp-listings' ), $params ) );

        $this->page = $page;
        $this->categories = $categories;
        $this->listing_renderer = $listing_renderer;
        $this->listings = $listings;
    }

    public function prepare_items() {
        $search_params = $this->get_listing_search_params();

        $this->load_items_from_query( $search_params['query'], $search_params['filters'] );

        if ( isset( $search_params['query']['category_id'] ) && $search_params['query']['category_id'] ) {
            try {
                $category = $this->categories->get( $search_params['query']['category_id'] );
                $message = __( 'Showing Ads from %s category.', 'another-wordpress-classifieds-plugin' );

                awpcp_flash( sprintf( $message, "<strong>{$category->name}</strong>" ) );
            } catch ( AWPCP_Exception $e ) {
                // nothing
            }
        }

        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
    }

    private function get_listing_search_params() {
        $user = wp_get_current_user();

        $items_per_page = (int) get_user_meta($user->ID, 'listings-items-per-page', true);
        $this->items_per_page = awpcp_request_param('items-per-page', $items_per_page === 0 ? 10 : $items_per_page);
        update_user_meta($user->ID, 'listings-items-per-page', $this->items_per_page);

        $default_category = absint( get_user_meta( $user->ID, 'listings-category', true ) );
        $this->selected_category_id = isset( $_POST['category'] ) ? awpcp_request_param( 'category' ) : $default_category;
        update_user_meta( $user->ID, 'listings-category', $this->selected_category_id );

        $params = $this->params = shortcode_atts(array(
            's' => '',
            'filterby' => '',
            'search-by' => '',
            'orderby' => '',
            'order' => 'desc',
            'paged' => 1,
        ), $_REQUEST);

        $query = array();

        if ( ! awpcp_current_user_is_moderator() ) {
            $query['author'] = wp_get_current_user()->ID;
        }

        try {
            $query = awpcp_listings_table_search_by_condition_parser()->parse( $params['search-by'], $params['s'], $query );
        } catch (Exception $e) {
            // ignore
        }

        if ( ! empty( $this->selected_category_id ) ) {
            $query['tax_query'][] = array(
                'taxonomy' => AWPCP_CATEGORY_TAXONOMY,
                'field' => 'term_id',
                'terms' => (int) $this->selected_category_id,
                'include_children' => true,
            );
        }

        $show_incomplete = false;
        $show_non_verified = false;
        $show_expired = false;
        $show_awaiting_approval = false;

        // TODO: update listings collection to support order and orderby parameters
        switch($params['orderby']) {
            case 'title':
            case 'start-date':
            case 'end-date':
            case 'renewed-date':
            case 'status':
            case 'payment-term':
            case 'payment-status':
            case 'featured-ad':
            case 'owner':
                $query['orderby'] = $params['orderby'];
                break;

            default:
                $query['orderby'] = 'renewed-date';
                break;
        }

        $query['order'] = $params['order'];
        $query['paged'] = $params['paged'];
        $query['posts_per_page'] = $this->items_per_page;

        return array(
            'query' => $query,
            'filters' => compact( 'show_incomplete', 'show_expired', 'show_non_verified', 'show_awaiting_approval' ),
        );
    }

    private function load_items_from_query( $query, $filters ) {
        if ( $filters['show_expired'] ) {
            $this->total_items = $this->listings->count_expired_listings( $query );
            $this->items = $this->listings->find_expired_listings( $query );
        } else if ( $filters['show_awaiting_approval'] ) {
            $this->total_items = $this->listings->count_listings_awaiting_approval( $query );
            $this->items = $this->listings->find_listings_awaiting_approval( $query );
        } else if ( $filters['show_non_verified'] ) {
            $this->total_items = $this->listings->count_successfully_paid_listings( $query );
            $this->items = $this->listings->find_successfully_paid_listings( $query );
        } else if ( $filters['show_incomplete'] ) {
            $this->total_items = $this->listings->count_listings( $query );
            $this->items = $this->listings->find_listings( $query );
        } else {
            $this->total_items = $this->listings->count_valid_listings( $query );
            $this->items = $this->listings->find_valid_listings( $query );
        }

        $this->set_pagination_args( array( 'total_items' => $this->total_items, 'per_page' => $this->items_per_page ) );
    }

    public function has_items() {
        return count($this->items) > 0;
    }

    public function get_columns() {
        $columns = array();

        $columns['cb'] = '<input type="checkbox" />';
        $columns['title'] = __( 'Title', 'another-wordpress-classifieds-plugin' );

        if ( awpcp_current_user_is_admin() ) {
            $columns['access_key'] = __( 'Access Key', 'another-wordpress-classifieds-plugin' );
        }

        $columns['start_date'] = __('Start Date', 'another-wordpress-classifieds-plugin');
        $columns['end_date'] = __('End Date', 'another-wordpress-classifieds-plugin');
        $columns['renewed_date'] = __('Renewed Date', 'another-wordpress-classifieds-plugin');
        $columns['status'] = __('Status', 'another-wordpress-classifieds-plugin');
        $columns['payment_term'] = __('Payment Term', 'another-wordpress-classifieds-plugin');
        $columns['payment_status'] = __('Payment Status', 'another-wordpress-classifieds-plugin');

        if ( defined( 'AWPCP_FEATURED_ADS_MODULE' ) ) {
            $columns['featured'] = __( 'Featured', 'another-wordpress-classifieds-plugin' );
        }

        if ( awpcp_current_user_is_moderator() ) {
            $columns['owner'] = __('Owner', 'another-wordpress-classifieds-plugin');
        }

        return $columns;
    }

    public function get_sortable_columns() {
        return array(
            'title' => array('title', true),
            'start_date' => array('start-date', true),
            'end_date' => array('end-date', true),
            'renewed_date' => array('renewed-date', true),
            'status' => array('status', true),
            'payment_term' => array('payment-term', true),
            'payment_status' => array('payment-status', true),
            'featured_ad' => array('featured-ad', true),
            'owner' => array('owner', true)
        );
    }

    public function get_bulk_actions() {
        $actions = array();
        if ( awpcp_current_user_is_admin() ) {
            $actions = array(
                'bulk-enable' => __( 'Enable', 'another-wordpress-classifieds-plugin' ),
                'bulk-disable' => __( 'Disable', 'another-wordpress-classifieds-plugin' ),
                'bulk-renew' => __( 'Renew', 'another-wordpress-classifieds-plugin' ),
                'bulk-spam' => __( 'Mark as SPAM', 'another-wordpress-classifieds-plugin' )
            );

            if ( function_exists( 'awpcp_featured_ads' ) ) {
                $actions['bulk-make-featured'] = __( 'Make Featured', 'another-wordpress-classifieds-plugin' );
                $actions['bulk-remove-featured'] = __( 'Make Non Featured', 'another-wordpress-classifieds-plugin' );
            }

            $fb = AWPCP_Facebook::instance();
            if ( $fb->get( 'page_token' ) )
                $actions['bulk-send-to-facebook'] = __( 'Send to Facebook', 'another-wordpress-classifieds-plugin' );
        }

        $actions['bulk-delete'] = __( 'Delete', 'another-wordpress-classifieds-plugin' );

        return $actions;
    }

    public function get_search_by_box() {
        if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
            return;

        $id = 'search-by';
        $label = __('Search by', 'another-wordpress-classifieds-plugin');

        $options['id'] = __('Ad ID', 'another-wordpress-classifieds-plugin');
        $options['title'] = __('Ad Title', 'another-wordpress-classifieds-plugin');
        $options['keyword'] = __('Keyword', 'another-wordpress-classifieds-plugin');
        $options['location'] = __('Location', 'another-wordpress-classifieds-plugin');
        $options['phone'] = __( 'Phone', 'another-wordpress-classifieds-plugin' );

        if ( awpcp_current_user_is_admin() ) {
            $options['payer-email'] = __('Payer Email', 'another-wordpress-classifieds-plugin');
        }

        $options['user'] = __('User', 'another-wordpress-classifieds-plugin');

        $search_by = awpcp_request_param('search-by', 'title');

        $html = '<p class="search-by-box">';
        $html.= '<label>' . $label . ':</label>&nbsp;&nbsp;';
        $html.= '<select name="search-by">';

        foreach ($options as $value => $text) {
            $selected = $search_by == $value ? 'selected="selected"' : '';
            $html.= '<option id="search-by-' . $value . '" ' . $selected . ' value="' . $value . '" />';
            $html.= $text;
            $html.= '</option>';
        }

        $html.= '</select>';
        $html.= '</p>';

        echo $html;
    }

    public function extra_tablenav( $which ) {
        if ( $which == 'top' ) {
            echo $this->render_category_filter();
        }
        echo $this->render_items_per_page_selector();
    }

    /**
     * @since 3.3
     */
    private function render_category_filter() {
        $category_selector = awpcp_categories_dropdown()->render( array(
            'context' => 'search',
            'name' => 'category',
            'label' => false,
            'selected' => $this->selected_category_id,
            'required' => false,
        ) );

        $submit_button = '<input class="button" type="submit" value="%s">';
        $submit_button = sprintf( $submit_button, esc_attr( _x( 'Filter', 'admin listings table', 'another-wordpress-classifieds-plugin' ) ) );

        $template = '<div class="alignleft actions awpcp-category-filter"><category-selector><submit-button></div>';
        $template = str_replace( '<category-selector>', $category_selector, $template );
        $template = str_replace( '<submit-button>', $submit_button, $template );

        return $template;
    }

    /**
     * TODO: use a single pagination form, check create_pager function in dcfunctions.php
     */
    private function render_items_per_page_selector() {
        $template = '<option %3$s value="%1$s">%2$s</option>';
        $selected = 'selected="selected"';

        foreach ( awpcp_default_pagination_options( $this->items_per_page ) as $value ) {
            $attributes = $value == $this->items_per_page ? $selected : '';
            $options[] = sprintf( $template, esc_attr( $value ), esc_html( $value ), $attributes );
        }

        $output = '<div class="tablenav-pages"><select name="items-per-page"><options></select></div>';
        $output = str_replace( '<options>', implode( '', $options ), $output );

        return $output;
    }

    private function get_row_actions($item) {
        $actions = $this->page->actions($item);
        return $this->page->links($actions);
    }

    public function column_default($item, $column_name) {
        return '...';
    }

    public function column_cb($item) {
        return '<input type="checkbox" value="' . $item->ID . '" name="selected[]" />';
    }

    public function column_title($item) {
        $content = sprintf(
            '<a class="awpcp-admin-listings-table-listing-title" title="%3$s" href="%2$s">%1$s</a>',
            $this->listing_renderer->get_listing_title( $item ),
            $this->page->url( array( 'action' => 'view', 'id' => $item->ID ) ),
            __( 'View Ad.', 'another-wordpress-classifieds-plugin' )
        );

        return $content . $this->row_actions( $this->get_row_actions( $item ), true );
    }

    public function column_access_key($item) {
        return $this->listing_renderer->get_access_key( $item );
    }

    public function column_start_date($item) {
        return $this->listing_renderer->get_start_date( $item );
    }

    public function column_end_date($item) {
        return $this->listing_renderer->get_end_date( $item );
    }

    public function column_renewed_date($item) {
        $renewed_date = $this->listing_renderer->get_renewed_date_formatted( $item );
        return empty( $renewed_date ) ? '--' : $renewed_date;
    }

    public function column_status($item) {
        $actions = array();

        if ( ! $this->listing_renderer->is_verified( $item ) ) {
            $url = $this->page->url( array( 'action' => 'mark-verified', 'id' => $item->ID ) );
            $actions['mark-verified'] = array( __( 'Mark as Verified', 'another-wordpress-classifieds-plugin' ), $url );
        }

        if ( ! empty( $actions ) ) {
            $actions = $this->row_actions( $this->page->links( $actions ), true );
        } else {
            $actions = '';
        }

        if ( $this->listing_renderer->is_disabled( $item ) ) {
            $status = __( 'Disabled', 'another-wordpress-classifieds-plugin' );
        } else {
            $status = __( 'Enabled', 'another-wordpress-classifieds-plugin' );
        }

        return $status . $actions;
    }

    public function column_payment_term($item) {
        $payment_term = $this->listing_renderer->get_payment_term( $item );
        return $payment_term ? $payment_term->name : __( 'N/A', 'another-wordpress-classifieds-plugin' );
    }

    public function column_payment_status($item) {
        $actions = array();

        if ( $this->listing_renderer->get_payment_status( $item ) == 'Unpaid' ) {
            $url = $this->page->url( array( 'action' => 'mark-paid', 'id' => $item->ID ) );
            $actions['mark-paid'] = array( __( 'Mark as Paid', 'another-wordpress-classifieds-plugin' ), $url );
        }

        if ( ! empty( $actions ) ) {
            $actions = $this->row_actions( $this->page->links( $actions ), true );
        } else {
            $actions = '';
        }

        return $this->listing_renderer->get_payment_status_formatted( $item ) . $actions;
    }

    public function column_featured($item) {
        if ( get_post_meta( $item->ID, '_awpcp_is_featured', true ) ) {
            return __( 'Featured', 'another-wordpress-classifieds-plugin' );
        } else {
            return __( 'Not Featured', 'another-wordpress-classifieds-plugin' );
        }
    }

    public function column_owner($item) {
        $user = $this->listing_renderer->get_user( $item );
        return is_object($user) ? $user->user_login : '&mdash;';
    }

    public function single_row($item) {
        static $row_class = '';
        $row_class = ( $row_class == '' ? ' class="alternate"' : '' );

        echo '<tr id="awpcp-ad-' . $item->ID . '" data-id="' . $item->ID . '"' . $row_class . '>';
        echo $this->single_row_columns( $item );
        echo '</tr>';
    }
}

// phpcs:enable
