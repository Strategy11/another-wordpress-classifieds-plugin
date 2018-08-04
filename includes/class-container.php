<?php
/**
 * @package AWPCP
 */

/**
 * A container class greatly inspired by https://carlalexander.ca/dependency-injection-wordpress/
 *
 * @since 4.0.0
 */
class AWPCP_Container implements ArrayAccess {

    /**
     * Values stored inside the container.
     *
     * @var array
     */
    private $values = array();

    /**
     * @var array
     */
    private $definitions = array();

    /**
     * @var array
     */
    private $shared = array();

    /**
     * Constructor
     *
     * @param array $values     Initial set of values to store in the container.
     */
    public function __construct( array $values = array() ) {
        $this->values = $values;
    }

    /**
     * Checks if there's a value in the container for the given key.
     *
     * @param mixed $key    The identifier of a value in the container.
     *
     * @return bool
     */
    public function offsetExists( $key ) {
        return array_key_exists( $key, $this->values );
    }

    /**
     * Returns a value stored in the container for the given key.
     *
     * @param mixed $key    The identifier of a value in the container.
     * @throws Exception    If the container doesn't have a value assocaited with
     *                      the given $key.
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
     * @param mixed $key    The identifier for the new value.
     * @param mixed $value  The value to store in the container.
     */
    public function offsetSet( $key, $value ) {
        $this->values[ $key ] = $value;
    }

    /**
     * Unsets the value in the container for the given key.
     *
     * @param mixed $key    The identifier of a value in the container.
     */
    public function offsetUnset( $key ) {
        unset( $this->values[ $key ] );
    }

    /**
     * Configure the container using the given configuration objects.
     *
     * @param array $configurations     An array of instances of Container Configurations Interface.
     */
    public function configure( array $configurations ) {
        foreach ( $configurations as $configuration ) {
            $configuration->modify( $this );
        }
    }

    // phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint

    /**
     * Returns a closure that creates a service object using the given constructor
     * function.
     *
     * @param Closur $closure   A constructor function for the object.
     *
     * @return Closue   A constructor function for the service object.
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

    // phpcs:enable Squiz.Commenting.FunctionComment.IncorrectTypeHint

    /**
     * @deprecated 4.0.0    Register the constructor function as a service.
     */
    public function share( $name, $files, $constructor ) {
        $this->definitions[ $name ] = array(
            'name'          => $name,
            'files'         => $files,
            'constructor'   => $constructor,
            'instance-type' => 'shared',
        );
    }

    /**
     * @deprecated 4.0.0    Use the container as an array instead.
     */
    public function get( $name ) {
        if ( ! isset( $this->definitions[ $name ] ) ) {
            return null;
        }

        $definition = $this->definitions[ $name ];

        if ( 'shared' === $definition['instance-type'] ) {
            return $this->get_shared_instance( $definition );
        }

        return $this->get_instance( $definition );
    }

    /**
     * @deprecated 4.0.0    Register the constructor function as a service and
     *                      use the container as an array instead.
     */
    private function get_shared_instance( $definition ) {
        if ( isset( $this->shared[ $definition['name'] ] ) ) {
            return $this->shared[ $definition['name'] ];
        }

        $this->shared[ $definition['name'] ] = $this->get_instance( $definition );

        return $this->shared[ $definition['name'] ];
    }

    /**
     * @deprecated 4.0.0    Use the container as an array instead.
     */
    private function get_instance( $definition ) {
        foreach ( (array) $definition['files'] as $file ) {
            require_once $file;
        }

        if ( ! is_callable( $definition['constructor'] ) ) {
            return null;
        }

        return call_user_func( $definition['constructor'], $this );
    }
}
