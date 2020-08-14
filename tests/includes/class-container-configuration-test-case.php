<?php
/**
 * @package AWPCP\Tests
 */

/**
 * Tests for main Container Configuration class.
 */
abstract class AWPCP_ContainerConfigurationTestCase extends AWPCP_UnitTestCase implements ArrayAccess {

    /**
     * @var object
     */
    protected $definitions;

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->definitions = [];
    }

    /**
     * @dataProvider class_definitions_provider
     * @param string $class_name      The name of the class that should be registered
     *                                in the container.
     * @since 4.0.0
     */
    public function test_class_definition( $class_name ) {
        // Execution.
        $this->get_test_subject()->modify( $this );

        // Verification.
        $this->assertNotNull( $this->definitions[ $class_name ]( $this ) );
    }

    /**
     * @param callable $callback    A constructor function.
     * @since 4.0.0
     */
    public function service( $callback ) {
        return $callback;
    }

    /**
     * @param mixed $offset     The name of the offset to check.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetExists( $offset ) {
        return true;
    }

    /**
     * @param mixed $offset     The name of the offset to get.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetGet( $offset ) {
        return null;
    }

    /**
     * @param mixed $offset     The name of the offset to set.
     * @param mixed $value      The value to store.
     * @since 4.0.0
     */
    public function offsetSet( $offset, $value ) {
        $this->definitions[ $offset ] = $value;
    }

    /**
     * @param mixed $offset     The name of the offset to unset.
     * @since 4.0.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetUnset( $offset ) {
    }
}
