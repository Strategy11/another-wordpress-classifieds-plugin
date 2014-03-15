<?php

if ( ! class_exists( 'AWPCP_AjaxHandler' ) ) {

/**
 * @since next-release
 */
abstract class AWPCP_AjaxHandler {

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
        _deprecated_function( __FUNCTION__, 'next-release', 'AWPCP_AjaxHandler::progress' );
        return $this->progress( $records_count, $records_left );
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
     * TODO: use wp_die instead of die()
     */
    protected function flush( $response ) {
        header( "Content-Type: application/json" );
        echo json_encode($response);
        die();
    }
}

}
