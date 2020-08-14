<?php

class Test_User_Listings_Admin_Page extends AWPCP_UnitTestCase {

    public function test_page_title_is_properly_generated() {
        $blog_name = get_bloginfo( 'name' );

        $page = awpcp_manage_listings_user_panel_page();

        $this->assertContains( $blog_name, $page->title );
        $this->assertNotContains( 'AWPCP', $page->title );
    }
}
