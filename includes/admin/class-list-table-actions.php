<?php
/**
 * @package AWPCP\Admin
 */

/**
 * Array-like list of row actions handlers for WP_List_Table.
 */
class AWPCP_ListTableActions implements IteratorAggregate {

    /**
     * @var string
     */
    private $table;

    /**
     * @var mixed
     */
    private $actions;

    /**
     * @param string $table     The identifier of the table whoose actions are
     *                          going to be stored in this instance.
     * @since 4.0.0
     */
    public function __construct( $table ) {
        $this->table = $table;
    }

    /**
     * @since 4.0.0
     */
    public function get_actions() {
        if ( is_null( $this->actions ) ) {
            $this->actions = apply_filters( "awpcp_list_table_actions_{$this->table}", array() );
        }

        return $this->actions;
    }

    /**
     * @since 4.0.0
     */
    public function getIterator() {
        return new ArrayIterator( $this->get_actions() );
    }
}
