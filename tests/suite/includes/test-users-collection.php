<?php

class AWPCP_TestUsersCollection extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->payments = Phake::mock( 'AWPCP_PaymentsAPI' );
        $this->settings = Phake::mock( 'AWPCP_Settings_API' );
        $this->db = $GLOBALS['wpdb'];

        Phake::when( $this->payments )->get_user_payment_terms( Phake::anyParameters() )->thenReturn( array() );
    }

    public function test_find_by_id() {
        $test_user = $this->create_random_test_user();

        $users = new AWPCP_UsersCollection( $this->payments, $this->settings, $this->db );
        $returned_user = $users->find_by_id( $test_user->ID, array( 'ID', 'display_name', 'first_name' ) );;

        $this->check_returned_user_has_full_information( $test_user, $returned_user );
    }

    private function create_random_test_user() {
        $user_number = rand() + 1;

        return $this->create_test_user( array(
            'user_login' => 'user_' . $user_number,
            'user_pass' => 'user_password_' . $user_number,
            'user_email' => 'user_' . $user_number . '@example.com',
            'user_url' => 'http://example.com/user/' . $user_number,
            'display_name' => 'Test User ' . $user_number,
            'first_name' => 'Test ' . $user_number,
            'last_name' => 'User ' . $user_number,
            'nickname' => 'user_nickname_' . $user_number,
        ) );
    }

    private function create_test_user( $user_data ) {
        $user_id = wp_insert_user( $user_data );
        return get_user_by( 'id', $user_id );
    }

    private function check_returned_user_has_full_information( $test_user, $returned_user ) {
        $this->assertEquals( $test_user->ID, $returned_user->ID );
        $this->assertEquals( $test_user->display_name, $returned_user->display_name );
        $this->assertEquals( get_user_meta( $test_user->ID, 'first_name', true ), $returned_user->first_name );
    }

    /**
     * @medium
     */
    public function test_find_by_search_string() {
        $test_user = $this->create_random_test_user();

        $other_user = $this->create_random_test_user();
        $other_user = $this->create_random_test_user();
        $other_user = $this->create_random_test_user();
        $other_user = $this->create_random_test_user();

        $search_string = str_replace( 'user_', '', $test_user->user_login );

        $users = new AWPCP_UsersCollection( $this->payments, $this->settings, $this->db );

        $returned_users = $users->find( array(
            'fields' => array( 'user_login', 'display_name', 'first_name', 'last_name', 'nickname' ),
            'like' => $search_string,
        ) );

        foreach ( $returned_users as $returned_user ) {
            $conatains_string = strpos( $returned_user->user_login, $search_string );
            $conatains_string = $conatains_string || strpos( $returned_user->display_name, $search_string );
            $conatains_string = $conatains_string || strpos( $returned_user->first_name, $search_string );
            $conatains_string = $conatains_string || strpos( $returned_user->last_name, $search_string );
            $conatains_string = $conatains_string || strpos( $returned_user->nickname, $search_string );
            $this->assertTrue( $conatains_string );
        }

        $this->assertGreaterThanOrEqual( 1, count( $returned_users ) );
    }

    public function test_user_name_generation() {
        $test_user = array(
            'user_login' => 'john',
            'user_pass' => 'whatever',
            'user_email' => 'john@example.org',
            'user_url' => 'http://example.org/user/john',
            'display_name' => 'The Big John',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'nickname' => 'johnny',
        );

        $user_instance = $this->create_test_user( $test_user );

        $formats = array(
            'user_login' => $test_user['user_login'],
            'firstname_first' => $test_user['first_name'] . ' ' .  $test_user['last_name'],
            'lastname_first' => $test_user['last_name'] . ' ' .  $test_user['first_name'],
            'firstname' => $test_user['first_name'],
            'lastname' => $test_user['last_name'],
            'display_name' => $test_user['display_name'],
        );

        foreach ( $formats as $format => $expected_result ) {
            $settings = Phake::mock( 'AWPCP_Settings_API' );

            Phake::when( $settings )->get_option( 'user-name-format' )->thenReturn( $format );

            $users = new AWPCP_UsersCollection( $this->payments, $settings, $this->db );
            $found_users = $users->find( array( 'fields' => array( 'public_name' ) ) );

            $this->assertEquals( $expected_result, $found_users[ $user_instance->ID ]->public_name, "Name generation for format '$format' failed." );
        }
    }
}
