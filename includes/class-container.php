<?php

function awpcp_container() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_Container();
    }

    return $instance;
}

class AWPCP_Container {

    private $definitions = array();
    private $shared = array();

    public function share( $name, $files, $constructor ) {
        $this->definitions[ $name ] = array(
            'name' => $name,
            'files' => $files,
            'constructor' => $constructor,
            'instance-type' => 'shared',
        );
    }

    public function get( $name ) {
        if ( ! isset( $this->definitions[ $name ] ) ) {
            return null;
        }

        $definition = $this->definitions[ $name ];

        if ( $definition['instance-type'] == 'shared' ) {
            return $this->get_shared_instance( $definition );
        } else {
            return $this->get_instance( $definition );
        }
    }

    private function get_shared_instance( $definition ) {
        if ( isset( $this->shared[ $definition['name'] ] ) ) {
            return $this->shared[ $definition['name'] ];
        }

        return $this->shared[ $definition['name'] ] = $this->get_instance( $definition );
    }

    private function get_instance( $definition ) {
        foreach ( (array) $definition['files'] as $file ) {
            require_once( $file );
        }

        if ( ! is_callable( $definition['constructor'] ) ) {
            return null;
        }

        return call_user_func( $definition['constructor'], $this );
    }
}
