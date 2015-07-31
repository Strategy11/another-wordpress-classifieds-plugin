<?php

function awpcp_main_classifieds_admin_page() {
    return new AWPCP_MainClassifiedsAdminPage();
}

class AWPCP_MainClassifiedsAdminPage {

    public function dispatch() {
        global $awpcp_db_version;
        global $message;
        global $hasextrafieldsmodule;
        global $extrafieldsversioncompatibility;

        $main_page_name = get_awpcp_option('main-page-name');

        $params = array(
            'awpcp_db_version' => $awpcp_db_version,
            'message' => $message,
            'main_page_name' => $main_page_name,
            'page_conflict' => checkforduplicate( add_slashes_recursive( sanitize_title( $main_page_name ) ) ),
            'hasextrafieldsmodule' => $hasextrafieldsmodule,
            'extrafieldsversioncompatibility' => $extrafieldsversioncompatibility,
        );

        $template = AWPCP_DIR . '/templates/admin/main-classifieds-admin-page.tpl.php';

        return awpcp_render_template( $template, $params );
    }
}
