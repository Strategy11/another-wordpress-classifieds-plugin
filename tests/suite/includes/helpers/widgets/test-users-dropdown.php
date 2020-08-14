<?php

class AWPCP_TestUsersDropdown extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->test_user = $this->get_test_user();

        $this->users = Phake::mock( 'AWPCP_UsersCollection' );
        Phake::when( $this->users )->get_users_with_full_information()->thenReturn( array( $this->test_user ) );
        Phake::when( $this->users )->get_users_with_basic_information()->thenReturn( array( $this->test_user ) );
    }

    private function get_test_user() {
        $user_id = rand() + 1;

        return (object) array(
            'ID' => $user_id,
            'public_name' => 'Test User ' . $user_id,
        );
    }

    public function test_render() {
        $args = array(
            'required' => true,
            'selected' => $this->test_user->ID,
            'label' => 'Test Label',
            'default' => 'Default option',
            'id' => 'id-' . rand(),
            'name' => 'name-' . rand(),
            'class' => array( 'class-' . rand() ),
        );

        $widget = new AWPCP_UsersDropdown( $this->users, null );
        $output = $widget->render( $args );

        $this->assertContains( 'dropdown-field', $output );
        $this->assertContains( (string) $args['selected'], $output );
        $this->assertContains( 'selected="selected"', $output );
        $this->assertContains( 'required', $output );

        $this->assertContains( $args['label'], $output );
        $this->assertContains( $args['default'], $output );
        $this->assertContains( $args['id'], $output );
        $this->assertContains( $args['name'], $output );
        $this->assertContains( implode( ' ', $args['class'] ), $output );
    }

    public function test_render_required_field_without_label() {
        $args = array(
            'required' => true,
            'label' => false,
        );

        $widget = new AWPCP_UsersDropdown( $this->users, null );
        $output = $widget->render( $args );

        $this->assertContains( 'required', $output );
    }

    public function test_render_not_required_field() {
        $args = array(
            'required' => false,
            'label' => 'Test Label',
        );

        $widget = new AWPCP_UsersDropdown( $this->users, null );
        $output = $widget->render( $args );

        $this->assertNotContains( 'required', $output );
    }

    public function test_render_using_basic_user_information() {
        $args = array(
            'include-full-user-information' => false,
        );

        $widget = new AWPCP_UsersDropdown( $this->users, null );
        $output = $widget->render( $args );

        Phake::verify( $this->users )->get_users_with_basic_information();

        $this->assertNotContains( 'data-user-information', $output );
    }
}
