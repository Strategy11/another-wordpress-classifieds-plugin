<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Clases used to handle custom row actions for WP List Table.
 */
class AWPCP_ListTableActionsHandler {

    /**
     * @var array A list of actions handlers.
     */
    private $actions;

    /**
     * @var object
     */
    private $posts_finder;

    /**
     * @var object
     */
    private $request;

    /**
     * @param array  $actions       A list of actions handlers.
     * @param object $posts_finder  An instance of Listings Collection.
     * @param object $request       An instane of Request.
     * @since 4.0.0
     */
    public function __construct( $actions, $posts_finder, $request ) {
        $this->actions      = $actions;
        $this->posts_finder = $posts_finder;
        $this->request      = $request;
    }

    /**
     * @since 4.0.0
     */
    public function admin_head() {
        $action = $this->request->param( 'awpcp-action' );
        $result = $this->request->param( 'awpcp-result' );

        if ( isset( $_SERVER['REQUEST_URI'] ) ) { // Input var okay.
            $_SERVER['REQUEST_URI'] = remove_query_arg( array( 'awpcp-action', 'awpcp-result' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); // Input var okay.
        }

        if ( ! isset( $this->actions[ $action ] ) || empty( $result ) ) {
            return;
        }

        $result_codes = array();

        foreach ( explode( '.', $result ) as $code_count_pairs ) {
            $parts = explode( '~', $code_count_pairs );

            if ( 2 !== count( $parts ) ) {
                continue;
            }

            $result_codes[ $parts[0] ] = intval( $parts[1] );
        }

        $messages = $this->actions[ $action ]->get_messages( $result_codes );

        echo implode( "\n", $messages ); // XSS: Ok.
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

    /**
     * @param string $sendback      Redirect URL.
     * @param string $action        The name of the current action.
     * @param array  $posts_ids     An array of posts IDs that need to be processed.
     * @since 4.0.0
     */
    public function handle_action( $sendback, $action, $posts_ids ) {
        if ( ! isset( $this->actions[ $action ] ) ) {
            return $sendback;
        }

        $handler      = $this->actions[ $action ];
        $result_codes = array();

        foreach ( $posts_ids as $post_id ) {
            $result_code = $this->process_item( $handler, $post_id );

            if ( ! isset( $result_codes[ $result_code ] ) ) {
                $result_codes[ $result_code ] = 0;
            }

            $result_codes[ $result_code ] = $result_codes[ $result_code ] + 1;
        }

        $params = array(
            'awpcp-action' => $action,
            'awpcp-result' => $this->prepare_result_codes( $result_codes ),
        );

        return add_query_arg( $params, $sendback );
    }

    /**
     * @param object $handler   An instance of List Table Action.
     * @param int    $post_id   The ID of a post.
     * @since 4.0.0
     */
    private function process_item( $handler, $post_id ) {
        try {
            $post = $this->posts_finder->get( $post_id );
        } catch ( AWPCP_Exception $e ) {
            return 'not-found';
        }

        try {
            $result_code = $handler->process_item( $post );
        } catch ( AWPCP_Exception $e ) {
            return 'error';
        }

        return $result_code;
    }

    /**
     * @param array $result_codes   An array of result codes with post counts.
     * @since 4.0.0
     */
    private function prepare_result_codes( $result_codes ) {
        $result_codes_strings = array();

        foreach ( $result_codes as $code => $count ) {
            if ( 0 === $count ) {
                continue;
            }

            $result_codes_strings[] = "$code~$count";
        }

        return implode( '.', $result_codes_strings );
    }
}
