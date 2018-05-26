<?php
/**
 * @package AWPCP\Admin
 */


/**
 * Interface for Data Exporter implementations.
 */
interface AWPCP_PersonalDataExporterInterface {

    /**
     * @since 3.8.6
     */
    public function get_page_size();

    /**
     * @since 3.8.6
     */
    public function get_objects( $user, $email_address, $page );

    /**
     * @since 3.8.6
     */
    public function export_objects( $objects );
}
