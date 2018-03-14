<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Clases used to handle custom row actions for WP List Table.
 */
class AWPCP_ListTableActionsHandler {

    /**
     * @var string The identifier of a custom post type.
     */
    private $post_type;

    /**
     * @var array A list of actions handlers.
     */
    private $actions;

    /**
     * @param string $post_type     The identifier of a custom post type.
     * @param array  $actions       A list of actions handlers.
     * @since 4.0.0
     */
    public function __construct( $post_type, $actions ) {
        $this->post_type = $post_type;
        $this->actions   = $actions;
    }

    /**
     * @param array  $actions    An array of row actions.
     * @param object $post       The post associated with the current row. An instance
     *                           of WP_Post.
     * @since 4.0.0
     */
    public function row_actions( $actions, $post ) {
        $current_url = add_query_arg( array() );

        foreach ( $this->actions as $name => $action ) {
            if ( ! $action->should_show_action_for( $post ) ) {
                continue;
            }

            $actions[ $name ] = $this->create_action_link(
                $action->get_label( $post ),
                $action->get_url( $post, $current_url )
            );
        }

        return $actions;
    }

    /**
     * @param string $label     The label for the action.
     * @param mixed  $url       The URL for the action.
     * @since 4.0.0
     */
    private function create_action_link( $label, $url ) {
        return sprintf( '<a href="%1$s">%2$s</a>', wp_nonce_url( $url, 'bulk-posts' ), $label );
    }
}
