<?php

class AWPCP_CSV_Importer_Factory {

    private $importer_delegate_factory;
    private $csv_reader_factory;

    public function __construct() {
        $this->importer_delegate_factory = awpcp()->container['ImporterDelegateFactory'];
        $this->csv_reader_factory        = new AWPCP_CSV_Reader_Factory();
    }

    public function create_importer( $import_session ) {
        $importer_delegate = $this->importer_delegate_factory->create_importer_delegate( $import_session );

        $csv_file_path = $import_session->get_working_directory() . DIRECTORY_SEPARATOR . 'source.csv';
        $csv_reader = $this->csv_reader_factory->create_reader( $csv_file_path );

        return new AWPCP_CSV_Importer( $importer_delegate, $import_session, $csv_reader );
    }
}
