<?php

function awpcp_categories_admin_page() {
    return new AWPCP_CategoriesAdminPage();
}

class AWPCP_CategoriesAdminPage {

    public function dispatch() {
        return awpcp_opsconfig_categories();
    }
}
