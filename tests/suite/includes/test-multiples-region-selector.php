<?php

/**
 * @group core
 */
class AWPCP_MultipleRegionSelectorTestCase extends AWPCP_UnitTestCase {

    public function test_render_returns_an_empty_string_if_no_fields_are_enabled() {
        $awpcp = awpcp();

        $awpcp->settings->update_option( 'displaycountryfield', false );
        $awpcp->settings->update_option( 'displaystatefield', false );
        $awpcp->settings->update_option( 'displaycountyvillagefield', false );
        $awpcp->settings->update_option( 'displaycityfield', false );

        $selected_regions = array();
        $options = array();

        $selector = new AWPCP_MultipleRegionSelector( $selected_regions, $options );

        $this->assertEquals( '', $selector->render( 'any context' ) );
    }
}
