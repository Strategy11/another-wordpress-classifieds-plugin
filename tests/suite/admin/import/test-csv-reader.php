<?php

class AWPCP_Test_CSV_Reader extends AWPCP_UnitTestCase {

    public function setup() {
        parent::setup();

        $this->auto_detect_line_endings = ini_get( 'auto_detect_line_endings' );
        ini_set( 'auto_detect_line_endings', true );
    }

    public function teardown() {
        parent::teardown();

        ini_set( 'auto_detect_line_endings', $this->auto_detect_line_endings );
    }

    public function test_get_header() {
        $file_handler = new AWPCP_CSV_Reader( WP_TESTS_DATA_DIR . '/csv-importer/headers-include-unicode-replacement-characters.csv' );

        $header = array( 'title', 'details', 'contact_name', 'contact_email', 'category_name', 'item_price', 'images' );

        $this->assertEquals( $header, $file_handler->get_header() );
    }

    public function test_get_row() {
        $reader = new AWPCP_CSV_Reader( WP_TESTS_DATA_DIR . '/csv-importer/headers-include-unicode-replacement-characters.csv' );

        $row = array(
            'title' => 'Didobridal1',
            'details' => 'Price is <span style="color: #ff0000;">USD</span> not EURO. Chic Beaded A-line One-shoulder Brush Train Elastic Satin Evening / Prom Dress <em><strong><a href="http://www.shareasale.com/m-pr.cfm?merchantID=43164&userID=xxxxx&productID=501122772" target="_blank">Click Here for Full Details</a></strong></em>',
            'contact_name' => 'Deb',
            'contact_email' => 'deb@debsdresses.com',
            'category_name' => 'Evening Dresses',
            'item_price' => '185.9',
            'images' => 'ED037.jpg'
        );

        $this->assertEquals( $row, $reader->get_row( 1 ) );
    }

    public function test_get_number_of_rows() {
        $reader = new AWPCP_CSV_Reader( WP_TESTS_DATA_DIR . '/csv-importer/huge-file-that-contains-newline-characters-within-column-values.csv' );

        // TODO: If Patchwork is loaded, it returns 40468; the number of rows with CSV data
        // TODO: If Patchwork is not loaed, it returns 40469; the number of rows with CSV data plus one (the header?)
        $this->assertGreaterThan( 40467, $reader->get_number_of_rows() );
        $this->assertLessThan( 40470, $reader->get_number_of_rows() );

        $reader = new AWPCP_CSV_Reader( WP_TESTS_DATA_DIR . '/csv-importer/zip-code-search-import-from-extra-field.csv' );

        $this->assertEquals( 10, $reader->get_number_of_rows() );

        $reader = new AWPCP_CSV_Reader( WP_TESTS_DATA_DIR . '/csv-importer/small-file-with-two-rows.csv' );

        $this->assertEquals( 2, $reader->get_number_of_rows() );
    }

    public function test_it_reads_files_encoded_with_windows_1252() {
        $reader = new AWPCP_CSV_Reader( WP_TESTS_DATA_DIR . '/csv-importer/encoded-with-windows-1252.csv' );

        $this->assertEquals( array(
            'contact_name',
            'username',
            'contact_email',
            'title',
            'category_name',
            'details',
            'item_price',
            'country',
            'state',
            'contact_phone',
            'website_url',
        ), $reader->get_header() );

        $this->assertEquals( array(
            'contact_name' => 'ibc',
            'username' => 'ibc-gg',
            'contact_email' => 'infoibcga@gmail.com',
            'title' => "IBC.COM, votre Afficheur de proximité !",
            'category_name' => 'Tout le Reste',
            'details' => "Comptez sur nous, pour vous accompagner dans vos projets, pour matérialiser vos ambitions en 2013 ! Notre Collaboration : IBC vous affiche le plus proche de vos clients,IBC vous conseille en Marketing, Communication & en Développement de votre affaire, IBC vous apporte un appui dans le suivi et la gestion de votre portefeuille client, IBC vous accompagne dans la réalisation de vos études de marché, quantitative et qualitative, IBC vous accompagne dans le référencement de vos produits et services,IBC organise et anime votre prospection commerciale, IBC vous apporte une identification visuelle forte, par la création de votre charte graphique et vos supports publicitaires. Nous contacter :  IBC  Marketing-Communication & Publicité (+241) 04 06 04 50 / 05 04 90 32 / 06 16 46 62 Emails : infoibcga@gmail.com / ibcgabon@live.fr Site web: www.ibcgabon.webs.com Facebook: www.facebook.com/ibc.ga Twitter: www.twitter.com/ibcgabon Skype: wimafryd. ",
            'item_price' => '',
            'country' => 'GABON',
            'state' => 'ESTUAIRE',
            'contact_phone' => '04 06 04 50',
            'website_url' => 'www.ibcgabon.webs.com'
        ), $reader->get_row( 1 ) );
    }

    public function test_it_read_files_that_contain_newline_characters_within_column_values() {
        $reader = new AWPCP_CSV_Reader( WP_TESTS_DATA_DIR . '/csv-importer/huge-file-that-contains-newline-characters-within-column-values.csv' );

        $this->assertEquals( array(
            'username',
            'category_name',
            'title',
            'details',
            'contact_name',
            'contact_email',
            'contact_phone',
            'start_date',
            'end_date',
            'images',
        ), $reader->get_header() );

        $this->assertEquals( array(
            'username' => "pdorst",
            'category_name' => "miscellaneous",
            'title' => 'Craft & ceramic supplies, 200 molds, exc. cond., all banded w/straps, 30" Duncan kiln, Studio Star pour table, Studio Star slip reclaimer, over 1000 bottles of acrylic paint & oil base paints, lots of brushes/wiring, bisque, lots of misc. supplies, $950 cash;',
            'details' => 'Craft & ceramic supplies, 200 molds, exc. cond., all banded w/straps, 30" Duncan kiln, Studio Star pour table, Studio Star slip reclaimer, over 1000 bottles of acrylic paint & oil base paints, lots of brushes/wiring, bisque, lots of misc. supplies, $950 cash;',
            'contact_name' => '',
            'contact_email' => "dorstpld@aol.com",
            'contact_phone' => "740-678-8111",
            'start_date' => "2016-04-13",
            'end_date' => "2016-04-20",
            'images' => 'NULL'
        ), $reader->get_row( 1 ) );

        $this->assertEquals( array(
            'username' => "pb9956",
            'category_name' => "miscellaneous",
            'title' => '32"x96" stainless steel table, $300;',
            'details' => '32"x96" stainless steel table, $300;',
            'contact_name' => '',
            'contact_email' => '',
            'contact_phone' => "740-554-2135",
            'start_date' => "2012-05-16",
            'end_date' => "2012-05-23",
            'images' => 'NULL'
        ), $reader->get_row( 13 ) );

        $this->assertEquals( array(
            'username' => "Jeff",
            'category_name' => "miscellaneous",
            'title' => "Royal Alpha 587CS cash register, used, w/manual, $85;",
            'details' => "Royal Alpha 587CS cash register, used, w/manual, $85;",
            'contact_name' => '',
            'contact_email' => "coupons6366@gmail.com",
            'contact_phone' => "740-992-0963",
            'start_date' => "2012-06-20",
            'end_date' => "2012-06-27",
            'images' => 'NULL'
        ), $reader->get_row( 45 ) );

        $this->assertEquals( array(
            'username' => "eener64",
            'category_name' => "miscellaneous",
            'title' => "Crystal 3-pc. liqueur set, never used, still in orig. box, $15 or make offer;",
            'details' => "Crystal 3-pc. liqueur set, never used, still in orig. box, $15 or make offer;",
            'contact_name' => '',
            'contact_email' => "eener64@gmail.com",
            'contact_phone' => "740-525-6308",
            'start_date' => "2012-06-27",
            'end_date' => "2012-07-04",
            'images' => 'NULL'
        ), $reader->get_row( 46 ) );

        $this->assertEquals( array(
            'username' => "bunquilted4u",
            'category_name' => "miscellaneous",
            'title' => 'Alpaca sheared fleece, ready to be washed, combed & carded for spinning, felting or weaving, white, fawn & black avail. for $25, ready to spin or felt "clouds" in white & fawn $30, these are premium fleece from champion bloodlines;',
            'details' => 'Alpaca sheared fleece, ready to be washed, combed & carded for spinning, felting or weaving, white, fawn & black avail. for $25, ready to spin or felt "clouds" in white & fawn $30, these are premium fleece from champion bloodlines;',
            'contact_name' => '',
            'contact_email' => "thiswonthurtabit44@yahoo.com",
            'contact_phone' => "304-771-8144",
            'start_date' => "2012-06-27",
            'end_date' => "2012-07-04",
            'images' => 'NULL'
        ), $reader->get_row( 47 ) );

        $this->assertEquals( array(
            'username' => "superlate",
            'category_name' => "chrysler/eagle",
            'title' => "2005 Sebring convertible, black w/beige top, heated leather p.seats, loaded w/options, 73,904 mi., $8000 OBO;",
            'details' => "2005 Sebring convertible, black w/beige top, heated leather p.seats, loaded w/options, 73,904 mi., $8000 OBO;",
            'contact_name' => '',
            'contact_email' => "dodge@frognet.net",
            'contact_phone' => "740 696-1163",
            'start_date' => "2013-09-18",
            'end_date' => "2013-09-25",
            'images' => 'NULL'
        ), $reader->get_row( 27375 ) );

        // // TODO: the last line can't be accessed if Patchwork is loaded :\
        // $this->assertEquals( array(
        //     'username' => "genglenn",
        //     'category_name' => "Denied",
        //     'title' => "NOT IN 06/01/16 * Montgomery AL is not in this Bulletin Board's region * YARD SALE, June 4, at Beacon of Hope Church on Coliseum Blvd, Montgomery AL, 6am-noon, incls. numerous household items, antiques, very lge selection of fishing lures & equip., jewelry, shoes, clothing & other estate items, antique entertainment console, lge curio cabinet, various furniture items, also mixture of electronic devices will be avail.;",
        //     'details' => "NOT IN 06/01/16 * Montgomery AL is not in this Bulletin Board's region * YARD SALE, June 4, at Beacon of Hope Church on Coliseum Blvd, Montgomery AL, 6am-noon, incls. numerous household items, antiques, very lge selection of fishing lures & equip., jewelry, shoes, clothing & other estate items, antique entertainment console, lge curio cabinet, various furniture items, also mixture of electronic devices will be avail.;",
        //     'contact_name' => '',
        //     'contact_email' => "gensie_glenn2003@yahoo.com",
        //     'contact_phone' => "334-567-3678",
        //     'start_date' => "2016-06-01",
        //     'end_date' => "2016-06-08",
        //     'images' => 'NULL'
        // ), $reader->get_row( 40468 ) );
    }

    public function test_it_removes_unicode_replacement_characters_from_column_names() {
        $expected_headers = array(
            'title',
            'details',
            'contact_name',
            'contact_email',
            'category_name',
            'item_price',
            'images',
        );

        $reader = new AWPCP_CSV_Reader( WP_TESTS_DATA_DIR . '/csv-importer/headers-include-unicode-replacement-characters.csv' );

        $this->assertEquals( $expected_headers, $reader->get_header() );
    }

    // TODO: test a username is provided, or a default one is picked, or throws an error
    // TODO: test that a category is provided, or that the importer creates the categories
    // TODO: test that start and end date are provided, or default values defined, or thorws an error
    // TODO: test regions are saved
    // TODO: test proper status and meta information is provided
}
