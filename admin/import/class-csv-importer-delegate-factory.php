<?php

function awpcp_csv_importer_delegate_factory() {
    return new AWPCP_CSV_Importer_Delegate_Factory(
        awpcp_image_attachment_creator(),
        awpcp_mime_types(),
        awpcp_uploaded_file_logic_factory(),
        awpcp_categories_logic(),
        awpcp_categories_collection(),
        awpcp_listings_api()
    );
}

class AWPCP_CSV_Importer_Delegate_Factory {

    private $db;

    public function __construct( $image_attachment_creator, $mime_types, $file_logic_factory, $categories_logic, $categories, $listings_logic ) {
        $this->image_attachment_creator = $image_attachment_creator;
        $this->mime_types = $mime_types;
        $this->file_logic_factory = $file_logic_factory;
        $this->categories_logic = $categories_logic;
        $this->categories = $categories;
        $this->listings_logic = $listings_logic;
    }

    public function create_importer_delegate( $import_session ) {
        return new AWPCP_CSV_Importer_Delegate(
            $import_session,
            $this->image_attachment_creator,
            $this->mime_types,
            $this->file_logic_factory,
            $this->categories_logic,
            $this->categories,
            $this->listings_logic
        );
    }
}
