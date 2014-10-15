<?php

class AWPCP_BackgroundProccess {

    private $command;
    private $log_file;

    public function __construct( $command, $log_file ) {
        $this->command = $command;
        $this->log_file = $log_file;
    }

    public function start() {
        $command = sprintf( '%s > "%s" 2>&1 & printf "%%u" $!', escapeshellcmd( $this->command ), $this->log_file );

        if ( $this->is_windows() ) {
            // proc_close( proc_open( 'start /B ' . $command, array(), $pipes ) );
        } else {
            // exec( $command, $output );
        }
    }

    private function is_windows() {
        return strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN';
    }
}
