<?php

/**
 * TODO: merge with AWPCP_TestUsersDropdown
 */
class AWPCP_TestUsersAutocomplete extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->test_user = $this->get_test_user();

        $this->users = Phake::mock( 'AWPCP_UsersCollection' );
        Phake::when( $this->users )->find_by_id()->thenReturn( $this->test_user );

        $this->javascript = Phake::mock( 'AWPCP_JavaScript' );
    }

    private function get_test_user() {
        $user = new stdClass();
        $user->ID = rand() + 1;
        $user->display_name = 'Test User ' . $user->ID;

        return $user;
    }

    public function test_render() {
        $args = array(
            'required' => true,
            'selected' => $this->test_user->ID,
            'label' => 'Test Label',
            'id' => 'id-' . rand(),
            'name' => 'name-' . rand(),
            'class' => array( 'class-' . rand() ),
        );

        $widget = new AWPCP_UsersAutocomplete( $this->users, null, $this->javascript );
        $output = $widget->render( $args );

        Phake::verify( $this->javascript )->set( 'users-autocomplete-default-user', null );

        $this->assertContains( 'autocomplete-field', $output );
        $this->assertContains( 'required', $output );

        $this->assertContains( $args['label'], $output );
        $this->assertContains( $args['id'], $output );
        $this->assertContains( $args['name'], $output );
        $this->assertContains( implode( ' ', $args['class'] ), $output );
    }

    public function test_render_required_field_without_label() {
        $args = array(
            'required' => true,
            'label' => false,
        );

        $widget = new AWPCP_UsersAutocomplete( $this->users, null, $this->javascript );
        $output = $widget->render( $args );

        $this->assertContains( 'required', $output );
    }

    public function test_render_not_required_field() {
        $args = array(
            'required' => false,
            'label' => 'Test Label',
        );

        $widget = new AWPCP_UsersAutocomplete( $this->users, null, $this->javascript );
        $output = $widget->render( $args );

        $this->assertNotContains( 'required', $output );
    }
}
