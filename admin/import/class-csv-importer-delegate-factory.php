<?php
/**
 * @package AWPCP\Admin\Import
 */

/**
 * Constructor function.
 */
function awpcp_csv_importer_delegate_factory() {
    return new AWPCP_CSV_Importer_Delegate_Factory(
        awpcp()->container['CSVImporterColumns'],
        awpcp_mime_types(),
        awpcp_categories_logic(),
        awpcp_categories_collection(),
        awpcp_listings_api(),
        awpcp_new_media_manager()
    );
}

/**
 * TODO: Remove this factory. We are already instantiating all the delegate's
 *       dependencies when the factory is created, so we might simply create the
 *       delegate directly.
 */
class AWPCP_CSV_Importer_Delegate_Factory {

    /**
     * @param object $columns           An instance of CSV Importer Columns.
     * @param object $mime_types        An instance of Mime Types.
     * @param object $categories_logic  An instance of Categories Logic.
     * @param object $categories        An instance of Categories.
     * @param object $listings_logic    An instance of Listings API.
     * @param object $media_manager     An instance of Media Manager.
     * @since 4.0.0     Updated to use Media Manager.
     */
    public function __construct( $columns, $mime_types, $categories_logic, $categories, $listings_logic, $media_manager ) {
        $this->columns          = $columns;
        $this->mime_types       = $mime_types;
        $this->categories_logic = $categories_logic;
        $this->categories       = $categories;
        $this->listings_logic   = $listings_logic;
        $this->media_manager    = $media_manager;
    }

    /**
     * Creates an instance of CSV Importer Delegate.
     *
     * @param object $import_session    An instance of CSV Import Session.
     */
    public function create_importer_delegate( $import_session ) {
        return new AWPCP_CSV_Importer_Delegate(
            $import_session,
            $this->columns,
            $this->mime_types,
            $this->categories_logic,
            $this->categories,
            $this->listings_logic,
            $this->media_manager
        );
    }
}
