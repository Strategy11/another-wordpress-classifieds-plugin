<?php

function awpcp_csv_importer( $options = array() ) {
	return new AWPCP_CSV_Importer(
		$options,
		awpcp_image_attachment_creator(),
		awpcp_mime_types(),
		awpcp_uploaded_file_logic_factory(),
		awpcp_categories_logic(),
		awpcp_categories_collection(),
		awpcp_listings_api(),
		awpcp_wordpress()
	);
}

class AWPCP_CSV_Importer {

	private $supported_columns = array(
		'post_fields' => array(
			'title' => 'post_title',
			'details' => 'post_content',
			'username' => 'post_author'
		),
		'terms' => array(
			'category_name' => 'term_id',
			// 'category_parent' => 'ad_category_parent_id',
		),
		'metadata' => array(
			'contact_name' => '_contact_name',
			'contact_email' => '_contact_email',
			'contact_phone' => '_contact_phone',
			'website_url' => '_website_url',
			'item_price' => '_price',
			'start_date' => '_start_date',
			'end_date' => '_end_date',
		),
		'region_fields' => array(
			'city' => 'ad_city',
			'state' => 'ad_state',
			'country' => 'ad_country',
			'county_village' => 'ad_county_village',
		),
	);

	private $required_columns = array(
		'title',
		'details',
		'contact_name',
		'contact_email',
		'category_name',
	);

	private $required_fields = array(
		'post_title',
		'post_content',
		'post_author',
		'_contact_name',
		'_contact_email',
		'_start_date',
		'_end_date',
		'term_id',
	);

	private $users_cache = array();

	private $messages = array();
	private $errors = array();

	private $images_created = array();

	private $image_attachment_creator;
	private $mime_types;
	private $file_logic_factory;
	private $categories_data_mapper;
	private $categories;
	private $listings_logic;
	private $wordpress;

	private $required = array(
		'title',
		"details",
		"contact_name",
		"contact_email",
		"category_name",
	);

	private $ignored = array('ad_id', 'id');

	// empty string to indicate integers :\
	private $types = array(
		"title" => "varchar",
		"details" => "varchar",
		"contact_name" => "varchar",
		"contact_email" => "varchar",
		"category_name" => "",
		"category_parent" => "",
		"contact_phone" => "varchar",
		"website_url" => "varchar",
		"city" => "varchar",
		'state' => 'varchar',
		"country" => "varchar",
		"county_village" => "varchar",
		"item_price" => "",
		"start_date" => "date",
		"end_date" => "date",
		'username' => '',
		"images" => "varchar"
	);

	private $extra_fields = array();

	private $rejected = array();

	private $defaults = array(
		'start-date' => '',
		'end-date' => '',
		'date-format' => '',
		'date-separator' => '',
		'time-separator' => '',
		'autocreate-categories' => false,
		'assign-user' => false,
		'default-user' => null,
		'test-import' => true
	);

	public $options = array();

	public $ads_imported = 0;
	public $images_imported = 0;
	public $ads_rejected = 0;

	private $zip_file = null;

	public function __construct( $options, $image_attachment_creator, $mime_types, $file_logic_factory, $categories_data_mapper, $categories, $listings_logic, $wordpress ) {
		$this->options = wp_parse_args($options, $this->defaults);

		// load Extra Fields definitions
		if (defined('AWPCPEXTRAFIELDSMOD')) {
			foreach (awpcp_get_extra_fields() as $field) {
				$this->extra_fields[$field->field_name] = $field;
			}
		}

		$this->image_attachment_creator = $image_attachment_creator;
		$this->mime_types = $mime_types;
		$this->file_logic_factory = $file_logic_factory;
		$this->categories_data_mapper = $categories_data_mapper;
		$this->categories = $categories;
		$this->listings_logic = $listings_logic;
		$this->wordpress = $wordpress;
	}

	/**
	 * @param $csv		filename of the CSV file
	 * @param $zip		filename of the ZIP file
	 */
	public function import( $csv, $zip = '', &$errors = array(), &$messages = array() ) {
		$parsed = $this->get_csv_data($csv);
		$header = $this->clean_up_csv_headers( $parsed[0] );

		if (empty($parsed)) {
			$errors[] = __( 'Invalid CSV file.', 'another-wordpress-classifieds-plugin' );
			return false;
		}

		if ( ! isset( $zip['tmp_name'] ) || empty( $zip['tmp_name'] ) ) {
			$import_dir = false;
		} else {
			$import_dir = $this->prepare_import_dir();
			$images = $this->unzip( $zip['tmp_name'], $import_dir, $errors, $messages );

			$this->zip_file = $zip['name'];

			if ( false === $images ) {
				return false;
			}
		}

		if ( in_array( 'images', $header ) && empty( $zip['tmp_name'] ) ) {
			$errors[] = __( 'Image file names were found but no ZIP was provided.', 'another-wordpress-classifieds-plugin' );
			return false;
		}

		$ncols = count($header);
		$nrows = count($parsed);

		// if we are assigned an user to the Ads, make sure that column
		// is being considered
		if ($this->options['assign-user'] && !in_array('username', $header)) {
			array_push($header, 'username');
		// if not, make that column optional
		} else if (!$this->options['assign-user']) {
			$this->required = array_diff($this->required, array('username'));
		}

		// per row column count can be handled here
		$data = array();
		for ($i = 1; $i < $nrows; $i++) {
			$column = $parsed[$i];
			$cols = count($column);

			if ($cols != $ncols) {
				// error message
				$errors[] = __( "Row number $i: input length mismatch", 'another-wordpress-classifieds-plugin' );
				$this->rejected[$i] = true;
				$this->ads_rejected++;
				continue;
			}

			$data[$i-1] = array('row_no' => $i);
			for ($j = 0; $j < $cols; $j++) {
				$key = trim($header[$j], "\n\r");
				$data[$i-1][$key] = $column[$j];
			}
		}

		if (!$this->validate($header, $data, $errors, $messages)) {
			return false;
		}

		$this->import_listings( $header, $data, $import_dir, $errors, $messages );

		$errors = array_merge( $errors, $this->errors );
		$messages = array_merge( $messages, $this->messages );
	}

	public function get_csv_data( $filename ) {
		$ini = ini_get('auto_detect_line_endings');
		ini_set('auto_detect_line_endings', true);

		$csv = $this->get_csv_file_contents( $filename );

		$data = array();
		while ($row = fgetcsv($csv)) {
			$data[] = $row;
		}

		ini_set('auto_detect_line_endings', $ini);

		return $data;
	}

	public function get_csv_file_contents( $filename ) {
		$content = file_get_contents( $filename );
		$encoding = awpcp_detect_encoding( $content );

		if ( 'UTF-8' != $encoding ) {
			$converted_content = iconv( $encoding, 'UTF-8', $content );
		} else {
			$converted_content = $content;
		}

		$handle = fopen( "php://memory", "rw" );
		fwrite( $handle, $converted_content );
		fseek( $handle, 0 );

		return $handle;
	}

	public function clean_up_csv_headers( $parsed_headers ) {
		foreach ( $parsed_headers as $i => $column_name ) {
			//remove EFBFBD (Replacement Character)
			$column_name = trim( str_replace( "\xEF\xBF\xBD", '', $column_name ) );
			// remove BOM character
			$column_name = trim( str_replace( "\xEF\xFF", '', $column_name ) );
			$column_name = trim( str_replace( "\xFF\xEF", '', $column_name ) );

			$headers[ $i ] = $column_name;
		}

		return $headers;
	}

	/**
	 * @param $header	array of columns in the CSV file
	 * @param $csv		two dimensional array of data extracted from CSV file
	 */
	private function validate($header, $csv, &$errors, &$messages) {
		foreach ($this->required as $required) {
			if (!in_array($required, $header)) {
				$msg = __( "The required column %s is missing. Import can't continue.", 'another-wordpress-classifieds-plugin' );
				$msg = sprintf($msg, $required);
				$errors[] = $msg;
				return false;
			}
		}

		// accepted columns are standard Ads columns + extra fields columns
		$accepted = array_merge(array_keys($this->types), array_keys($this->extra_fields), $this->ignored);
		$unknown = array_diff($header, $accepted);

		if (!empty($unknown)) {
			$msg = __( "Import can't continue. Unknown column(s) specified(s):", 'another-wordpress-classifieds-plugin' );
			$msg.= '<br/>' . join(', ', $unknown);
			$errors[] = $msg;
			return false;
		}

		return true;
	}

	/**
	 * @param $header	array of columns in the CSV file
	 * @param $csv		two dimensional array of data extracted from CSV file
	 */
	private function import_listings( $header, $csv, $import_dir, &$errors, &$messages ) {
		$listings_data = $this->parse_listings_data( $csv, $import_dir );

		$this->save_imported_listings( $listings_data );
		$this->remove_extracted_files( $import_dir, $this->images_created );

		if ( $this->ads_imported > 0 && ! $this->options['test-import'] ) {
			do_action( 'awpcp-listings-imported' );
		}
	}

	/**
     * @since feature/1112
     */
	private function parse_listings_data( $data, $import_dir ) {
		$listings_data = array();

		foreach ( $data as $row_number => $row_data ) {
			try {
				$listings_data[ $row_number ] = $this->import_listing( $row_number, $row_data, $import_dir );
			} catch ( AWPCP_Exception $e ) {
				$message = _x( 'Error in row <row-number>: <error-message>', 'csv importer', 'another-wordpress-classifieds-plugin' );
				$message = str_replace( '<row-number>', $row_number + 1, $message );
				$message = str_replace( '<error-message>', $e->getMessage(), $message );

				$this->rejected[ $row_number ] = true;
				$this->errors[] = $message;
			}
		}

		return $listings_data;
	}

	/**
     * @since feature/1112
     */
	private function import_listing( $row_number, $row_data, $import_dir ) {
		$listing_data = array();

		foreach ( $this->supported_columns as $column_type => $columns ) {
			foreach ( $columns as $column_name => $field_name ) {
				if ( ! isset( $row_data[ $column_name ] ) && in_array( $column_name, $this->required_columns ) ) {
					$message =_x( 'Required value for column "<column-name>" is missing.', 'csv importer', 'another-wordpress-classifieds-plugin' );
					$message = str_replace( $message, '<column-name>', $column_name );

					throw new AWPCP_Exception( $message );
				}

				try {
					$parsed_value = $this->parse_column_value( $row_number, $row_data, $column_name );
				} catch ( AWPCP_Exception $e ) {
					if ( ! in_array( $field_name, $this->required_fields ) ) {
						continue;
					}

					throw $e;
				}

				$listing_data[ $column_type ][ $field_name ] = $parsed_value;
			}
		}

		if ( $import_dir ) {
			$image_names = explode( ';', $row_data['images'] );
			$listing_data['attachments'] = $this->import_images( $image_names, $row_number, $import_dir, $errors );
			$this->images_imported += count( $listing_data['attachments'] );
			// save created images to be deleted later, if test mode is on
			array_splice( $this->images_created, 0, 0, $listing_data['attachments'] );
		} else {
			$listing_data['attachments'] = array();
		}

		// TODO: fix Extra Fields module to be able to import extra fields data
		return apply_filters( 'awpcp-imported-listing-data', $listing_data, $row_number, $row_data );
	}

	/**
     * @since feature/1112
     */
	public function parse_column_value( $row_number, $row_data, $column_name ) {
		// DO NOT USE awpcp_array_data BECAUSE IT WILL TREAT '0' AS AN EMPTY VALUE
		$raw_value = isset( $row_data[ $column_name ] ) ? $row_data[ $column_name ] : false;

		if ( isset( $this->parsed_data[ $row_number ][ $column_name ] ) ) {
			return $this->parsed_data[ $row_number ][ $column_name ];
		}

		switch ( $column_name ) {
			case 'username':
				$parsed_value = $this->parse_username_column( $raw_value, $row_number, $row_data );
				break;
			case 'category_name':
				$parsed_value = $this->parse_category_name_column( $raw_value, $row_number, $row_data );
				break;
			case 'item_price':
				$parsed_value = $this->parse_item_price_column( $raw_value, $row_number, $row_data );
				break;
			case 'start_date':
				$parsed_value = $this->parse_start_date_column( $raw_value, $row_number, $row_data );
				break;
			case 'end_date':
				$parsed_value = $this->parse_end_date_column( $raw_value, $row_number, $row_data );
				break;
			case 'ad_postdate':
				$parsed_value = $this->parse_post_date_column( $raw_value, $row_number, $row_data );
				break;
			case 'ad_last_updated':
				$parsed_value = $this->parse_post_modified_column( $raw_value, $row_number, $row_data );
				break;
			default:
				$parsed_value = $raw_value;
				break;
		}

		return $this->parsed_data[ $row_number ][ $column_name ] = $parsed_value;
	}

	/**
     * @since feature/1112
     */
	private function parse_username_column( $username, $row_number, $row_data ) {
		$contact_email = $this->parse_column_value( $row_number, $row_data, 'contact_email' );

		$user_info = $this->get_user_info( $username, $contact_email );

		if ( $user_info->created ) {
			$message = _x( "A new user '%s' with email address '%s' and password '%s' was created for row %d.", 'csv importer', 'another-wordpress-classifieds-plugin' );
			$message = sprintf( $message, $username, $contact_email, $user_info->password, $row_number );

			$this->add_message( $message );
		}

		return $user_info->ID;
	}

	/**
	 * Attempts to find a user by its username or email. If a user can't be
	 * found one will be created.
	 *
     * @since feature/1112
	 * @param $username string 	User's username.
	 * @param $contact_email 	string 	User's email address.
	 * @return User info object or false.
	 * @throws AWPCP_Exception
	 */
	private function get_user_info( $username, $contact_email ) {
		$user = $this->get_user( $username, $contact_email );

		if ( is_object( $user ) ) {
			return (object) array( 'ID' => $user->ID, 'created' => false );
		} else if ( ! empty( $this->options[ 'default-user' ] ) ) {
			return (object) array( 'ID' => $this->options[ 'default-user' ], 'created' => false );
		}

		list( $user, $password ) = $this->create_user( $username, $contact_email );

		if ( is_object( $user ) ) {
			return (object) array( 'ID' => $user->ID, 'created' => true, 'password' => $password );
		}

		return null;
	}

	/**
     * @since feature/1112
	 */
	private function get_user( $username, $contact_email ) {
		if ( isset( $this->users_cache[ $username ] ) ) {
			return $this->users_cache[ $username ];
		}

		if ( ! empty( $username ) ) {
			$user = get_user_by( 'login', $username );
		} else {
			$user = null;
		}

		if ( ! is_object( $user ) && ! empty( $contact_email ) ) {
			$user = get_user_by( 'email', $contact_email );
		}

		return $this->users_cache[ $username ] = $user;
	}

	/**
     * @since feature/1112
	 * @throws AWPCP_Exception
	 */
	private function create_user( $username, $contact_email ) {
		if ( empty( $username ) && empty( $contact_email ) ) {
			$message = _x( 'The username and contact_email columns are required. Both were empty.', 'csv importer', 'another-wordpress-classifieds-plugin' );
			throw new AWPCP_Exception( $message );
		} else if ( empty( $username ) ) {
			$message = _x( 'The username column is required. An empty value was found.', 'csv importer', 'another-wordpress-classifieds-plugin' );
			throw new AWPCP_Exception( $message );
		} else if ( empty( $contact_email ) ) {
			$message = _x( 'The contact_email column is required. An empty value was found.', 'csv importer', 'another-wordpress-classifieds-plugin' );
			throw new AWPCP_Exception( $message );
		}

		$password = wp_generate_password( 14, false, false );

		if ( $this->options['test-import'] ) {
			$result = 1; // fake it!
		} else {
			$result = wp_create_user( $username, $password, $contact_email );
		}

		if ( is_wp_error( $result ) ) {
			throw new AWPCP_Exception( $result->get_error_message() );
		}

		$this->users_cache[ $username ] = get_user_by( 'id', $result );

		return array( 'user' => $this->users_cache[ $username ], 'password' => $password );
	}

	/**
	 * @since feature/1112
	 */
	private function parse_category_name_column( $category_name, $row_number, $row_data ) {
		$category = $this->get_category( $category_name );

		return $category ? $category->term_id : null;
	}

	private function get_category( $name ) {
		$create_missing_categories = $this->options['autocreate-categories'];
		$test = $this->options['test-import'];

		try {
			$category = $this->categories->get_category_by_name( $name );
		} catch ( AWPCP_Exception $e ) {
			$category = null;
		}

		if ( is_null( $category ) && $create_missing_categories && $test ) {
			return (object) array( 'term_id' => rand() + 1, 'parent' => 0 );
		} else if ( is_null( $category ) && $create_missing_categories ) {
			return $this->create_category( $name );
 		} else if ( is_null( $category ) ) {
			$message = _x( 'No category with name "<category-name>" was found.', 'csv importer', 'another-wordpress-classifieds-plugin' );
			$message = str_replace( '<category-name>', $name, $message );

			throw new AWPCP_Exception( $message );
 		}

 		return $category;
	}

	/**
	 * @since feature/1112
	 */
	private function create_category( $name ) {
		try {
			$category_id = $this->categories_data_mapper->create_category( array( 'name' => $name ) );
		} catch ( AWPCP_Exception $e ) {
			$message = _x( 'There was an error trying to create category "<category-name>".', 'csv importer', 'another-wordpress-classifieds-plugin' );
			$message = str_replace( '<category-name>', $name, $message );

			throw new AWPCP_Exception( $message );
		}

		try {
			$category = $this->categories->get( $category_id );
		} catch ( AWPCP_Exception $e ) {
			$message = _x( 'A category with name "<category-name>" was created, but there was an error trying to retrieve its information from the database.', 'csv importer', 'another-wordpress-classifieds-plugin' );
			$message = str_replace( '<category-name>', $name, $message );

			throw new AWPCP_Exception( $message );
		}

		return $category;
	}

	private function parse_item_price_column( $price, $row_number, $row_data ) {
		// numeric validation
		if ( ! is_numeric( $price ) ) {
			$message = _x( "Item price must be a number.", 'csv importer', 'another-wordpress-classifieds-plugin' );
			throw new AWPCP_Exception( $message );
		}

		// AWPCP stores Ad prices using an INT column (WTF!) so we need to
		// store 99.95 as 9995 and 99 as 9900.
		return $price * 100;
	}

	private function parse_start_date_column( $start_date, $row_number, $row_data ) {
		return $this->parse_date_column( $start_date, $this->options['start-date'], array(
			'empty-date-with-no-default' => _x( 'The start date is missing and no default value was defined.', 'csv importer', 'another-wordpress-classifieds-plugin' ),
			'invalid-date' => _x( 'The start date is invalid and no default value was defined.', 'csv importer', 'another-wordpress-classifieds-plugin' ),
			'invalid-default-date' => _x( "Invalid default start date.", 'csv importer', 'another-wordpress-classifieds-plugin' ),
		) );
	}

	private function parse_date_column( $date, $default_date, $error_messages = array() ) {
		if ( empty( $date ) && empty( $default_date ) ) {
			$message = $error_messages['empty-date-with-no-default'];
			throw new AWPCP_Exception( $message );
		}

		$parsed_value = $this->parse_date(
			$date,
			$this->options['date-format'],
			$this->options['date-separator'],
			$this->options['time-separator']
		);

		// TODO: validation
		if ( empty( $parsed_value ) && ! empty( $date ) ) {
			$message = $error_messages['invalid-date'];
			throw new AWPCP_Exception( $message );
		}

		$parsed_value = $this->parse_date(
			$default_date,
			'us_date',
			$this->options['date-separator'],
			$this->options['time-separator']
		);

		if ( empty( $parsed_value ) && ! empty( $default_date ) ) {
			$message = $error_messages['invalid-default-date'];
			throw new AWPCP_Exception( $message );
		}

		return $parsed_value;
	}

	public function parse_date($val, $date_time_format, $date_separator, $time_separator, $format = "Y-m-d H:i:s") {
		$date_formats = array(
			'us_date' => array(
				array('%m', '%d', '%y'), // support both two and four digits years
				array('%m', '%d', '%Y'),
			),
			'uk_date' => array(
				array('%d', '%m', '%y'),
				array('%d', '%m', '%Y'),
			)
		);

		$date_formats['us_date_time'] = $date_formats['us_date'];
		$date_formats['uk_date_time'] = $date_formats['uk_date'];

		if (in_array($date_time_format, array('us_date_time', 'uk_date_time')))
			$suffix = join($time_separator, array('%H', '%M', '%S'));
		else
			$suffix = '';

		$date = null;
		foreach ($date_formats[$date_time_format] as $_format) {
			$_format = trim(sprintf("%s %s", join($date_separator, $_format), $suffix));
			$parsed = awpcp_strptime( $val, $_format );
			if ($parsed && empty($parsed['unparsed'])) {
				$date = $parsed;
				break;
			}
		}

		if (is_null($date))
			return null;

		$datetime = new DateTime();

		try {
			$datetime->setDate($parsed['tm_year'] + 1900, $parsed['tm_mon'] + 1, $parsed['tm_mday']);
			$datetime->setTime($parsed['tm_hour'], $parsed['tm_min'], $parsed['tm_sec']);
		} catch (Exception $ex) {
			echo "Exception: " . $ex->getMessage();
		}

	    return $datetime->format($format);
	}

	private function parse_end_date_column( $end_date, $row_number, $row_data ) {
		return $this->parse_date_column( $end_date, $this->options['end-date'], array(
			'empty-date-with-no-default' => _x( 'The end date is missing and no default value was defined.', 'csv importer', 'another-wordpress-classifieds-plugin' ),
			'invalid-date' => _x( 'The end date is missing and no default value was defined.', 'csv importer', 'another-wordpress-classifieds-plugin' ),
			'invalid-default-date' => _x( "Invalid default end date.", 'csv importer', 'another-wordpress-classifieds-plugin' ),
		) );
	}

	private function parse_post_date_column( $post_date, $row_number, $row_data ) {
		$default_start_date = $this->options['start-date'];

		if ( empty( $default_start_date ) ) {
			return current_time( 'mysql' );
		}

		$parsed_value = $this->parse_date(
			$default_start_date,
			'us_date',
			$this->options['date-separator'],
			$this->options['time-separator']
		);

		return $parsed_value;
	}

	private function parse_post_modified_column( $post_modified, $row_number, $row_data ) {
		return current_time( 'mysql' );
	}

	private function save_imported_listings( $listings_data ) {
		foreach ( $listings_data as $listing_data ) {
			if ( ! $this->options['test-import'] ) {
				$this->save_imported_listing( $listing_data );
			}
			$this->ads_imported = $this->ads_imported + 1;
		}
	}

	private function save_imported_listing( $listing_data ) {
		$listing_data['metadata']['_verified'] = true;
		$listing_data['metadata']['_payment_status'] = AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_REQUIRED;

		try {
			$listing = $this->listings_logic->create_listing( array( 'post_title' => 'Imported Listing Draft' ) );
			$this->listings_logic->update_listing( $listing, $listing_data );
		} catch ( AWPCP_Exception $previous ) {
			$message = _x( 'There was an error trying to store impored data in the database', 'csv importer', 'another-wordpress-classifieds-plugin' );
			throw new AWPCP_Exception( $message, 0, $previous );
		}

		foreach ( $listing_data['attachments'] as $file_path ) {
	        $pathinfo = awpcp_utf8_pathinfo( $file_path );

			$file_logic = $this->file_logic_factory->create_file_logic((object) array(
	            'path' => $file_path,
	            'realname' => $pathinfo['basename'],
	            'name' => $pathinfo['basename'],
	            'dirname' => $pathinfo['dirname'],
	            'filename' => $pathinfo['filename'],
	            'extension' => $pathinfo['extension'],
	            'mime_type' => $this->mime_types->get_file_mime_type( $file_path ),
	            'is_complete' => true,
	        ));

			$this->image_attachment_creator->create_attachment( $listing, $file_logic );
		}
	}

	private function remove_extracted_files( $import_dir, $images_created ) {
		list( $images_dir, $thumbs_dir ) = awpcp_setup_uploads_dir();

		if ( $this->options['test-import'] ) {
			foreach ( $images_created as $filename ) {
				if ( file_exists( $filename ) ) {
					unlink( $filename );
				}

				if ( file_exists( $thumbs_dir . $filename ) ) {
					unlink( $thumbs_dir . $filename );
				}
			}
		}

		awpcp_rmdir( $import_dir );
	}

	private function prepare_import_dir() {
		$current_user = wp_get_current_user();

		list( $images_dir, $thumbnails_dir ) = awpcp_setup_uploads_dir();
		$import_dir = str_replace( 'thumbs', 'import', $thumbnails_dir );
		$import_dir = $import_dir . wp_hash( $current_user->ID . '-' . microtime() );

		$owner = fileowner( $images_dir );

		if ( !is_dir( $import_dir ) ) {
			umask( 0 );
			@mkdir( $import_dir, awpcp_directory_permissions(), true );
			@chown( $import_dir, $owner );
		}

		return file_exists( $import_dir ) ? $import_dir : false;
	}

	public function unzip( $file, $import_dir, &$errors = array(), &$messages = array() ) {
		if ( !file_exists( $file ) ) {
			$message = __( 'File %s does not exists.', 'another-wordpress-classifieds-plugin' );
			$errors[] = sprintf( $message, $file );
			return false;
		}

		if ( false === $import_dir ) {
			$message = __( 'Import directory %s does not exists.', 'another-wordpress-classifieds-plugin' );
			$errors[] = sprintf( $message, $import_dir );
			return false;
		}

		require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

		$archive = new PclZip( $file );
		$items = $archive->extract( PCLZIP_OPT_EXTRACT_AS_STRING );
		$files = array();

		if ( !is_array( $items ) ) {
			$errors[] = __( 'Incompatible ZIP Archive', 'another-wordpress-classifieds-plugin' );
			return false;
		}

		if ( 0 === count( $items ) ) {
			$errors[] = __( 'Empty ZIP Archive', 'another-wordpress-classifieds-plugin' );
			return false;
		}

		foreach ( $items as $item ) {
			// ignore folder and don't extract the OS X-created __MACOSX directory files
			if ( $item['folder'] || '__MACOSX/' === substr( $item['filename'], 0, 9 ) ) {
				continue;
			}

			// don't extract files with a filename starting with . (like .DS_Store)
			if ( '.' === substr( basename( $item['filename'] ), 0, 1 ) ) {
				continue;
			}

			$path = trailingslashit( $import_dir ) . $item['filename'];

			// if file is inside a directory, create it first
			if ( dirname( $item['filename'] ) !== '.' ) {
				@mkdir( $import_dir . '/' . dirname( $item['filename'] ), awpcp_directory_permissions(), true );
			}

			// extract file
			if ( $h = @fopen( $path, 'w' ) ) {
				fwrite( $h, $item['content'] );
				fclose( $h );
			} else {
				$message = __( 'Could not write temporary file %s', 'another-wordpress-classifieds-plugin' );
				$errors[] = sprintf( $message, $path );
			}

			if ( file_exists( $path ) ) {
				$files[] = array(
					'path' => $path,
					'filename' => $item['filename'],
				);
			}
		}

		return $files;
	}

	/**
	 * TODO: handle test imports
	 */
	private function import_images($images, $row, $import_dir, &$errors) {
		$entries = array();

		$default_import_dir_path = trailingslashit($import_dir);
		$extended_import_dir_path = $default_import_dir_path . basename( $this->zip_file, '.zip' ) . '/';

		foreach (array_filter($images) as $filename) {
			if ( file_exists( $default_import_dir_path . $filename ) ) {
				$entries[] = $default_import_dir_path . $filename;
			} else if ( file_exists( $extended_import_dir_path . $filename ) ) {
				$entries[] = $extended_import_dir_path . $filename;
			} else {
				$message = _x( 'Image file with name <image-name> not found.', 'csv importer', 'another-wordpress-classifieds-plugin' );
				$message = str_replace( '<image-name>', $filename, $message );

				throw new AWPCP_Exception( $message );
			}
		}

		return $entries;
	}

	private function save_images($entries, $adid, $row, &$errors) {
		global $wpdb;

		$test_import = $this->options['test-import'];
		$media_api = awpcp_media_api();

		foreach ($entries as $entry) {
            $extension = awpcp_get_file_extension( $entry['filename'] );
            $mime_type = sprintf( 'image/%s', $extension );

			$data = array(
				'ad_id' => $adid,
				'name' => $entry['filename'],
				'path' => $entry['filename'],
				'mime_type' => $mime_type,
				'enabled' => true,
				'is_primary' => false,
			);

			$result = $test_import || $media_api->create( $data );

			if ($result === false) {
				$msg = __("Could not save the information to the database for %s in row %d", 'another-wordpress-classifieds-plugin');
				$errors[] = sprintf($msg, $entry['original'], $row);
			}
		}
	}
}


function is_valid_date($month, $day, $year) {
	if (strlen($year) != 4)
		return false;
	return checkdate($month, $day, $year);
}


/**
 * Validate extra field values and return value.
 *
 * @param name        field name
 * @param value       field value in CSV file
 * @param row         row number in CSV file
 * @param validate    type of validation
 * @param type        type of input field (Input Box, Textarea Input, Checkbox,
 *                                         SelectMultiple, Select, Radio Button)
 * @param options     list of options for fields that accept multiple values
 * @param enforce     true if the Ad that's being imported belongs to the same category
 *                    that the extra field was assigned to, or if the extra field was
 *                    not assigned to any category.
 *                    required fields may be empty if enforce is false.
 */
function awpcp_validate_extra_field($name, $value, $row, $validate, $type, $options, $enforce, &$errors) {
	$validation_errors = array();
	$serialize = false;

	$list = null;

	switch ($type) {
		case 'Input Box':
		case 'Textarea Input':
			// nothing special here, proceed with validation
			break;

		case 'Checkbox':
		case 'Select Multiple':
			// value can be any combination of items from options list
			$msg = sprintf( __("Extra Field %s's value is not allowed in row %d. Allowed values are: %%s", 'another-wordpress-classifieds-plugin'), $name, $row );
			$list = explode( ';', $value );
			$serialize = true;

		case 'Select':
		case 'Radio Button':
			$list = is_array($list) ? $list : array($value);

			if (!isset($msg)) {
				$msg = sprintf( __("Extra Field %s's value is not allowed in row %d. Allowed value is one of: %%s", 'another-wordpress-classifieds-plugin'), $name, $row );
			}

			// only attempt to validate if the field is required (has validation)
			foreach ($list as $item) {
				if (empty($item)) {
					continue;
				}
				if (!in_array($item, $options)) {
					$msg = sprintf($msg, join(', ', $options));
					$validation_errors[] = $msg;
				}
			}

			// extra fields multiple values are stored serialized
			if ( $serialize ) {
				$value = maybe_serialize( $list );
			}

			break;

		default:
			break;
	}

	if (!empty($validation_errors)) {
		array_splice( $errors, count( $errors ), 0, $validation_errors );
		return false;
	}

	$list = is_array($list) ? $list : array($value);

	foreach ($list as $k => $item) {
		if (!$enforce && empty($item)) {
			continue;
		}

		switch ($validate) {
			case 'missing':
				if (empty($value)) {
					$validation_errors[] = "Extra Field $name is required in row $row.";
				}
				break;

			case 'url':
				if (!isValidURL($item)) {
					$validation_errors[] = "Extra Field $name must be a valid URL in row $row.";
				}
				break;

			case 'email':
				$regex = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$";
				if (!eregi($regex, $item)) {
					$validation_errors[] = "Extra Field $name must be a valid email address in row $row.";
				}
				break;

			case 'numericdeci':
				if (!is_numeric($item)) {
					$validation_errors[] = "Extra Field $name must be a number in row $row.";
				}
				break;

			case 'numericnodeci':
				if (!ctype_digit($item)) {
					$validation_errors[$name] = "Extra Field $name must be an integer number in row $row.";
				}
				break;

			default:
				break;
		}
	}

	if (!empty($validation_errors)) {
		array_splice( $errors, count( $errors ), 0, $validation_errors );
		return false;
	}

	return $value;
}

