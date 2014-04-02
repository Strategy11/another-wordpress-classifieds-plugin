<?php

if ( ! class_exists( 'AWPCP_AjaxHandler' ) ) {

function awpcp_ajax_response() {
    return new AWPCP_AjaxResponse();
}

/**
 * @since next-release
 */
class AWPCP_AjaxResponse {

    /**
     * @since next-release
     */
    public function set_content_type( $content_type ) {
        header( sprintf( "Content-Type: %s", $content_type ) );
    }

    /**
     * @since next-release
     */
    public function write( $content ) {
        echo $content;
    }

    /**
     * TODO: use wp_die instead of die()
     * @since next-release
     */
    public function close() {
        die();
    }
}

/**
 * @since next-release
 */
abstract class AWPCP_AjaxHandler {

    private $response;

    public function __construct( $response ) {
        $this->response = $response;
    }

    /**
     * @since next-release
     */
    public abstract function ajax();

    /**
     * @since next-release
     */
    protected function success( $params = array() ) {
        return $this->flush( array_merge( array( 'status' => 'ok' ), $params ) );
    }

    /**
     * @since next-release
     */
    protected function error( $params = array() ) {
        return $this->flush( array_merge( array( 'status' => 'error' ), $params ) );
    }

    /**
     * @since next-release
     */
    protected function progress_response( $records_count, $records_left ) {
        return $this->success( array( 'recordsCount' => $records_count, 'recordsLeft' => $records_left ) );
    }

    /**
     * @since next-release
     */
    protected function response( $records_count, $records_left ) {
        _deprecated_function( __FUNCTION__, 'next-release', 'AWPCP_AjaxHandler::progress_response' );
        return $this->progress_response( $records_count, $records_left );
    }

    /**
     * @since next-release
     */
    protected function error_response( $error_message ) {
        return $this->error( array( 'error' => $error_message ) );
    }

    /**
     * @since next-release
     */
    protected function multiple_errors_response( $errors ) {
        return $this->error( array( 'errors' => $errors ) );
    }

    /**
     * @since next-release
     */
    protected function flush( $array_response ) {
        $this->response->set_content_type( 'application/json' );
        $this->response->write( json_encode( $array_response ) );
        $this->response->close();
    }
}

}
