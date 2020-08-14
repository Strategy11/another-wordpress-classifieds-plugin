<?php

/**
 * @group core
 */
class AWPCP_TestListingsTableSearchConditionsParser extends AWPCP_UnitTestCase {

    public function test_search_by_for_admin_users() {
        $this->login_as_administrator();

        $parser = awpcp_listings_table_search_by_condition_parser();

        try {
            $parser->parse( 'id', 5, array() );
            $parser->parse( 'keyword', 'databases', array() );
            $parser->parse( 'location', 'Colombia', array() );
            $parser->parse( 'payer-email', 'wvega@wvvega.com', array() );
            $parser->parse( 'title', 'Test Ad', array() );
            $parser->parse( 'user', 'admin', array() );
            $parser->parse( 'phone', '316', array() );
        } catch (Exception $e) {
            $this->fail( 'An exception was thrown while trying to parse a Search By condition: ' . $e->getMessage() );
        }
    }

    public function test_search_by_for_regular_users() {
        $this->login_as_subscriber();

        $parser = awpcp_listings_table_search_by_condition_parser();

        try {
            $parser->parse( 'id', 5, array() );
            $parser->parse( 'keyword', 'databases', array() );
            $parser->parse( 'location', 'Colombia', array() );
            $parser->parse( 'title', 'Test Ad', array() );
            $parser->parse( 'user', 'admin', array() );
            $parser->parse( 'phone', '316', array() );
        } catch (Exception $e) {
            $this->fail( 'An exception was thrown while trying to parse a Search By condition: ' . $e->getMessage() );
        }

        try {
            $parser->parse( 'payer-email', 'wvega@wvvega.com' );
            $this->fail( "Regular users shouldn't be allowed to search by Payer Email" );
        } catch (Exception $e) {
            // good enough, regular users can't search by payer email
            // but we should use a custom exception we can associate with an action not being available
        }
    }
}
