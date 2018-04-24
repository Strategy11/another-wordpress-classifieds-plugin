<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Clases used to handle custom views for WP List Table.
 */
class AWPCP_ListTableViewsHandler {

    /**
     * @var array
     */
    private $views;

    /**
     * @var object
     */
    private $request;

    /**
     * @param array $views      A list of views handlers.
     * @param array $request    An instance of Request.
     * @since 4.0.0
     */
    public function __construct( $views, $request ) {
        $this->views   = $views;
        $this->request = $request;
    }

    /**
     * @param object $query     An instance of WP_Query.
     * @since 4.0.0
     */
    public function pre_get_posts( $query ) {
        $current_view = $this->get_current_view();

        if ( ! $current_view || ! isset( $this->views[ $current_view ] ) ) {
            return;
        }

        $this->views[ $current_view ]->pre_get_posts( $query );
    }

    /**
     * @since 4.0.0
     */
    private function get_current_view() {
        return $this->request->param( 'awpcp_filter', false );
    }

    /**
     * @param array $views  An array of already defined views for the table.
     */
    public function views( $views ) {
        $current_view = $this->get_current_view();
        $current_url  = remove_query_arg( 'post_status' );

        foreach ( $this->views as $name => $view ) {
            $views[ $name ] = $this->create_view_link(
                $view->get_label(),
                $view->get_url( $current_url ),
                $view->get_count(),
                $current_view === $name ? 'current' : ''
            );
        }

        return $views;
    }

    /**
     * @param string $label     The label for the action.
     * @param mixed  $url       The URL for the action.
     * @param int    $count     The number of posts on this view.
     * @param string $class     The CSS class for the A tag.
     * @since 4.0.0
     */
    private function create_view_link( $label, $url, $count, $class ) {
        return sprintf(
            '<a class="%s" href="%s">%s <span class="count">(%s)</span></a>',
            $class,
            esc_url( $url ),
            esc_html( $label ),
            esc_html( $count )
        );
    }
}
