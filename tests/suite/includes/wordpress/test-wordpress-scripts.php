<?php

class AWPCP_Test_WordPress_Scripts extends AWPCP_UnitTestCase {

    /**
     * Test for https://github.com/drodenbaugh/awpcp/issues/1306.
     *
     * In WP 3.9.8, wp_script_is function wasn't able to detect scripts that would be
     * enqueued because they were declared as dependency of other, already enqueued,
     * scripts.
     *
     * We added code to backport the fixes introduced by the WordPress Team in WP 4.0.
     *
     * @since 3.6
     */
    public function test_query_detects_scripts_enqueued_as_a_dependency() {
        $enqueued_script = 'awpcp-admin';
        $dependency_script = 'awpcp';

        wp_enqueue_script( $enqueued_script );

        $scripts = awpcp_wordpress_scripts();

        $this->assertTrue( $scripts->query( $dependency_script, 'enqueued' ), sprintf( "Script with handle '%s' was found in the queue list because is a dependency of a script that was already enqueued.", $dependency_script ) );
    }
}
