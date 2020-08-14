<?php

/**
 * @group core
 */
class AWPCP_TestDatabaseColumnCreator extends AWPCP_UnitTestCase {

    private $table_name = 'awpcp_test_database_column_creator';

    public function setup() {
        parent::setup();

        global $wpdb;
        // TOOD: is there a helper function to create tables?
        $sql = 'CREATE TABLE %s ( `id` INT(10) NOT NULL PRIMARY KEY )';
        $wpdb->query( sprintf( $sql, $this->table_name ) );
    }

    public function test_create() {
        global $wpdb;

        $column_name = 'foo';
        $column_definition = "VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `id`";

        $column_creator = new AWPCP_DatabaseColumnCreator( $wpdb );
        $column_creator->create( $this->table_name, $column_name, $column_definition );

        $columns = $wpdb->get_results( sprintf( 'DESCRIBE %s', $this->table_name ) );

        $this->assertEquals( $column_name, $columns[1]->Field );
        $this->assertEquals( 'varchar(255)', $columns[1]->Type );
    }

    public function teardown() {
        parent::teardown();

        global $wpdb;
        $wpdb->query( sprintf( 'DROP TABLE IF EXISTS %s' , $this->table_name ) );
    }
}
