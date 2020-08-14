<?php

class AWPCP_TestCreditPlansAdminPage extends AWPCP_UnitTestCase {

    public function test_index() {
        $page = new AWPCP_CreditPlansAdminPage();
        $page->index();

        ob_clean();

        $this->markTestIncomplete( 'Test not yet implement.' );
    }
}
