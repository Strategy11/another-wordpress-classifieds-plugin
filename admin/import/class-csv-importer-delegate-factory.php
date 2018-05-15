<?php

function awpcp_csv_importer_delegate_factory() {
    return new AWPCP_CSV_Importer_Delegate_Factory(
        awpcp_mime_types(),
        awpcp_categories_logic(),
        awpcp_categories_collection(),
        awpcp_listings_api(),
        awpcp_new_media_manager()
    );
}

class AWPCP_CSV_Importer_Delegate_Factory {

    /**
     * @since 4.0.0     Updated to use Media Manager.
     */
    public function __construct( $mime_types, $categories_logic, $categories, $listings_logic, $media_manager ) {
        $this->mime_types = $mime_types;
        $this->categories_logic = $categories_logic;
        $this->categories = $categories;
        $this->listings_logic = $listings_logic;
        $this->media_manager    = $media_manager;
    }

    public function create_importer_delegate( $import_session ) {
        return new AWPCP_CSV_Importer_Delegate(
            $import_session,
            $this->mime_types,
            $this->categories_logic,
            $this->categories,
            $this->listings_logic,
            $this->media_manager
        );
    }
}
