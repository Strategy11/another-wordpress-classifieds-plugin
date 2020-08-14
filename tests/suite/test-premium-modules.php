<?php

class AWPCP_TestPremiumModules extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();
        $this->premium_modules = awpcp()->get_premium_modules_information();
    }

    public function test_category_icons_is_detected() {
        $this->assertEquals( defined( 'AWPCP_CATEGORY_ICONS_MODULE_DB_VERSION' ), $this->premium_modules['category-icons']['installed'] );
    }

    public function test_google_checkout_is_detected() {
        $this->assertEquals( defined( 'AWPCP_GOOGLE_CHECKOUT_MODULE' ), $this->premium_modules['google-checkout']['installed'] );
    }

    public function test_region_control_is_detected() {
        $this->assertEquals( defined( 'AWPCP_REGION_CONTROL_MODULE' ), $this->premium_modules['region-control']['installed'] );
    }

    public function test_rss_module_is_detected() {
        $this->assertEquals( defined( 'AWPCP_RSS_MODULE' ), $this->premium_modules['rss']['installed'] );
    }
}
