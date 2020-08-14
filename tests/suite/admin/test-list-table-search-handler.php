<?php
/**
 * @package AWPCP\Tests\Plugin\Admin
 */

/**
 * Unit tests for List Table Search Handler.
 */
class AWPCP_ListTableSearchHandlerTest extends AWPCP_UnitTestCase {

    /**
     * @since 4.0.0
     */
    public function setup() {
        parent::setup();

        $this->search        = [];
        $this->html_renderer = Mockery::mock( 'AWPCP_HTMLRenderer' );
        $this->request       = Mockery::mock( 'AWPCP_Request' );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts() {
        $query = null;

        $this->search = [
            'title' => Mockery::mock( 'AWPCP_TitleListTableSearchMode' ),
        ];

        $this->search['title']->shouldReceive( 'pre_get_posts' )->once()->with( $query );

        $this->request->shouldReceive( 'param' )->with( 'awpcp_search_by' )->andReturn( 'title' );

        // Execution.
        $this->get_test_subject()->pre_get_posts( $query );
    }

    /**
     * @since 4.0.0
     */
    public function test_pre_get_posts_with_the_default_mode() {
        $query = null;

        $this->search = [
            'keyword' => Mockery::mock( 'AWPCP_KeywordListTableSearchMode' ),
        ];

        $this->search['keyword']->shouldReceive( 'pre_get_posts' )->once()->with( $query );

        $this->request->shouldReceive( 'param' )->with( 'awpcp_search_by' )->andReturn( false );

        // Execution.
        $this->get_test_subject()->pre_get_posts( $query );
    }

    /**
     * @since 4.0.0
     */
    private function get_test_subject() {
        return new AWPCP_ListTableSearchHandler(
            $this->search,
            $this->html_renderer,
            $this->request
        );
    }

    /**
     * @since 4.0.0
     */
    public function test_get_search_query() {
        $search_term = 'Something';

        $this->request->shouldReceive( 'param' )->with( 's' )->andReturn( $search_term );

        // Execution and Verification.
        $this->assertEquals( $search_term, $this->get_test_subject()->get_search_query( null ) );
    }

    /**
     * @since 4.0.0
     */
    public function test_render_search_mode_dropdown() {
        $search_mode_id   = 'search_mode_' . wp_rand();
        $search_mode_name = 'Search Mode';

        $selected_search_mode_id = 'selected_search_mode_' . wp_rand();

        $this->search = [
            $search_mode_id => Mockery::mock( 'AWPCP_TitleListTableSearchMode' ),
        ];

        $this->search[ $search_mode_id ]->shouldReceive( 'get_name' )->andReturn( $search_mode_name );

        $this->request->shouldReceive( 'param' )->with( 'awpcp_search_by' )->andReturn( $selected_search_mode_id );

        // Verification.
        $this->html_renderer->shouldReceive( 'render' )->once()->with(
            Mockery::on(
                function( $defintiion ) use ( $search_mode_id, $search_mode_name, $selected_search_mode_id ) {
                    if ( ! in_array( 'awpcp-hidden', $defintiion['#attributes']['class'], true ) ) {
                        return false;
                    }

                    if ( ! isset( $defintiion['#content']['dropdown'] ) ) {
                        return false;
                    }

                    $dropdown = $defintiion['#content']['dropdown'];

                    if ( $dropdown['#options'][ $search_mode_id ] !== $search_mode_name ) {
                        return false;
                    }

                    if ( ! in_array( 'awpcp-search-mode-dropdown', $dropdown['#attributes']['class'], true ) ) {
                        return false;
                    }

                    if ( 'awpcp_search_by' !== $dropdown['#attributes']['name'] ) {
                        return false;
                    }

                    return $dropdown['#value'] === $selected_search_mode_id;
                }
            )
        );

        // Execution.
        $this->get_test_subject()->render_search_mode_dropdown( 'top' );
    }

    /**
     * @since 4.0.0
     */
    public function test_render_search_mode_dropdown_prints_nothing_for_bottom_position() {
        $this->get_test_subject()->render_search_mode_dropdown( 'bottom' );

        $this->assertEmpty( $this->getActualOutput() );
    }
}
