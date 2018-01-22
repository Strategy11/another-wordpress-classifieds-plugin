<?php
/**
 * @package AWPCP
 */

/**
 * TODO: Remove this function. We have autoload now.
 */
function awpcp_container() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_Container();
    }

    return $instance;
}

/**
 * A container class greatly inspired by https://carlalexander.ca/dependency-injection-wordpress/
 *
 * @since 4.0
 */
class AWPCP_Container implements ArrayAccess {

    /**
     * Values stored inside the container.
     *
     * @var array
     */
    private $values = array();

    private $definitions = array();
    private $shared = array();

    /**
     * Constructor
     *
     * @param array $values
     */
    public function __construct( array $values = array() ) {
        $this->values = $values;
    }

    /**
     * Checks if there's a value in the container for the given key.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists( $key ) {
        return array_key_exists( $key, $this->values );
    }

    /**
     * Returns a value stored in the container for the given key.
     *
     * @param mixed $key
     */
    public function offsetGet( $key ) {
        if ( ! array_key_exists( $key, $this->values ) ) {
            throw new Exception( sprintf( "Container doesn't have a value stored for key: %s", $key ) );
        }

        if ( $this->values[ $key ] instanceof Closure ) {
            return $this->values[ $key ]( $this );
        }

        return $this->values[ $key ];
    }

    /**
     * Sets a value inside the container.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet( $key, $value ) {
        $this->values[ $key ] = $value;
    }

    /**
     * Unsets the value in the container for the given key.
     *
     * @param mixed $key
     */
    public function offsetUnset( $key ) {
        unset( $this->values[ $key ] );
    }

    /**
     * Configure the container using the given configuration objects.
     *
     * @param array $configurations
     */
    public function configure( array $configurations ) {
        foreach( $configurations as $configuration ) {
            $configuration->modify( $this );
        }
    }

    /**
     * Returns a closure that creates a service object using the given constructor
     * function.
     *
     * @param Closure A constructor function for the object.
     *
     * @return Closue A constructor function for the service object.
     */
    public function service( Closure $closure ) {
        return function ( $container ) use ( $closure ) {
            static $object;

            if ( null === $object ) {
                $object = $closure( $container );
            }

            return $object;
        };
    }

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
