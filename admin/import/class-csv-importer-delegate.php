<?php

class AWPCP_CSV_Importer_Delegate {

    private $import_session;
    private $image_attachment_creator;
    private $mime_types;
    private $file_logic_factory;
    private $categories_logic;
    private $categories;
    private $listings_logic;

    private $default_supported_columns = array(
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
            'contact_name' => '_awpcp_contact_name',
            'contact_email' => '_awpcp_contact_email',
            'contact_phone' => '_awpcp_contact_phone',
            'website_url' => '_awpcp_website_url',
            'item_price' => '_awpcp_price',
            'start_date' => '_awpcp_start_date',
            'end_date' => '_awpcp_end_date',
        ),
        'region_fields' => array(
            'city' => 'ad_city',
            'state' => 'ad_state',
            'country' => 'ad_country',
            'county_village' => 'ad_county_village',
        ),
        'custom' => array(
            'images' => 'images',
        ),
    );

    private $supported_columns = null;

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
        '_awpcp_contact_name',
        '_awpcp_contact_email',
        '_awpcp_start_date',
        '_awpcp_end_date',
        'term_id',
    );

    private $parsed_data = array();
    private $messages = array();

    public function __construct( $import_session, $image_attachment_creator, $mime_types, $file_logic_factory, $categories_logic, $categories, $listings_logic ) {
        $this->import_session = $import_session;
        $this->image_attachment_creator = $image_attachment_creator;
        $this->mime_types = $mime_types;
        $this->file_logic_factory = $file_logic_factory;
        $this->categories_logic = $categories_logic;
        $this->categories = $categories;
        $this->listings_logic = $listings_logic;
    }

    public function import_row( $row_data ) {
        $this->clear_state();

        $listing_data = $this->get_listing_data( $row_data );

        if ( ! $this->import_session->is_test_mode_enabled() ) {
            $this->save_listing_data( $listing_data );
        }

        return (object) array(
            'messages' => $this->messages,
        );
    }

    private function clear_state() {
        $this->parsed_data = array();
        $this->messages = array();
    }

    private function get_listing_data( $row_data ) {
        foreach ( $this->get_supported_columns() as $column_type => $columns ) {
            foreach ( $columns as $column_name => $field_name ) {
                if ( ! isset( $row_data[ $column_name ] ) && in_array( $column_name, $this->required_columns ) ) {
                    $message =_x( 'Required value for column "<column-name>" is missing.', 'csv importer', 'another-wordpress-classifieds-plugin' );
                    $message = str_replace( $message, '<column-name>', $column_name );

                    throw new AWPCP_CSV_Importer_Exception( $message );
                }

                try {
                    $parsed_value = $this->parse_column_value( $row_data, $column_name );
                } catch ( AWPCP_Exception $e ) {
                    if ( ! in_array( $field_name, $this->required_fields ) ) {
                        continue;
                    }

                    throw $e;
                }

                $listing_data[ $column_type ][ $field_name ] = $parsed_value;
            }
        }

        if ( isset( $row_data['images'] ) ) {
            $image_names = array_filter( explode( ';', $row_data['images'] ) );
            $listing_data['attachments'] = $this->import_images( $image_names );
        } else {
            $listing_data['attachments'] = array();
        }

        // TODO: fix Extra Fields module to be able to import extra fields data
        return apply_filters( 'awpcp-imported-listing-data', $listing_data, $row_data );
    }

    private function get_supported_columns() {
        if ( is_null( $this->supported_columns ) ) {
            $this->supported_columns = apply_filters( 'awpcp-csv-importer-supported-columns', $this->default_supported_columns );
        }

        return $this->supported_columns;
    }

    /**
     * @since feature/1112
     */
    public function parse_column_value( $row_data, $column_name ) {
        // DO NOT USE awpcp_array_data BECAUSE IT WILL TREAT '0' AS AN EMPTY VALUE
        $raw_value = isset( $row_data[ $column_name ] ) ? $row_data[ $column_name ] : false;

        switch ( $column_name ) {
            case 'username':
                $parsed_value = $this->parse_username_column( $raw_value, $row_data );
                break;
            case 'category_name':
                $parsed_value = $this->parse_category_name_column( $raw_value, $row_data );
                break;
            case 'item_price':
                $parsed_value = $this->parse_item_price_column( $raw_value, $row_data );
                break;
            case 'start_date':
                $parsed_value = $this->parse_start_date_column( $raw_value, $row_data );
                break;
            case 'end_date':
                $parsed_value = $this->parse_end_date_column( $raw_value, $row_data );
                break;
            case 'ad_postdate':
                $parsed_value = $this->parse_post_date_column( $raw_value, $row_data );
                break;
            case 'ad_last_updated':
                $parsed_value = $this->parse_post_modified_column( $raw_value, $row_data );
                break;
            default:
                $parsed_value = $raw_value;
                break;
        }

        return $this->parsed_data[ $column_name ] = $parsed_value;
    }

    /**
     * @since feature/1112
     */
    private function parse_username_column( $username, $row_data ) {
        $contact_email = $this->parse_column_value( $row_data, 'contact_email' );

        $user_info = $this->get_user_info( $username, $contact_email );

        if ( $user_info->created ) {
            $message = _x( "A new user '%s' with email address '%s' and password '%s' was created.", 'csv importer', 'another-wordpress-classifieds-plugin' );
            $message = sprintf( $message, $username, $contact_email, $user_info->password );

            $this->messages[] = $message;
        }

        return $user_info->ID;
    }

    /**
     * Attempts to find a user by its username or email. If a user can't be
     * found one will be created.
     *
     * @since feature/1112
     * @param $username string  User's username.
     * @param $contact_email    string  User's email address.
     * @return User info object or false.
     * @throws AWPCP_Exception
     */
    private function get_user_info( $username, $contact_email ) {
        $user = $this->get_user( $username, $contact_email );

        if ( is_object( $user ) ) {
            return (object) array( 'ID' => $user->ID, 'created' => false );
        }

        $default_user = $this->import_session->get_param( 'default_user' );

        if ( $default_user ) {
            return (object) array( 'ID' => $default_user, 'created' => false );
        }

        $user_data = $this->create_user( $username, $contact_email );

        if ( isset( $user_data['user'] ) && is_object( $user_data['user'] ) ) {
            return (object) array(
                'ID' => $user_data['user']->ID,
                'created' => true,
                'password' => $user_data['password']
            );
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
            $message = _x( "No user could be assigned to this listing. A new user couldn't be created because both the username and contact email columns are missing or have an empty value. Please include a username and contact email or select a default user.", 'csv importer', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( $message );
        } else if ( empty( $username ) ) {
            $message = _x( "No user could be assigned to this listing. A new user couldn't be created because the username column is missing or has an empty value. Please include a username or select a default user.", 'csv importer', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( $message );
        } else if ( empty( $contact_email ) ) {
            $message = _x( "No user could be assigned to this listing. A new user couldn't be created because the contact_email column is missing or has an empty value. Please include a contact_email or select a default user.", 'csv importer', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( $message );
        }

        $password = wp_generate_password( 14, false, false );

        if ( $this->import_session->is_test_mode_enabled() ) {
            $result = 1; // fake it!
        } else {
            $result = wp_create_user( $username, $password, $contact_email );
        }

        if ( is_wp_error( $result ) ) {
            $message = __( 'No user could be assigned to this listing. Our attempt to create a new user failed with the following error: <error-message>.', 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '<error-message>', $result->get_error_message(), $message );

            throw new AWPCP_CSV_Importer_Exception( $message );
        }

        $this->users_cache[ $username ] = get_user_by( 'id', $result );

        return array( 'user' => $this->users_cache[ $username ], 'password' => $password );
    }

    /**
     * @since feature/1112
     */
    private function parse_category_name_column( $category_name, $row_data ) {
        $category = $this->get_category( $category_name );

        return $category ? $category->term_id : null;
    }

    private function get_category( $name ) {
        try {
            $category = $this->categories->get_category_by_name( $name );
        } catch ( AWPCP_Exception $e ) {
            $category = null;
        }

        $create_missing_categories = $this->import_session->get_param( 'create_missing_categories' );
        $is_test_mode_enabled = $this->import_session->is_test_mode_enabled();

        if ( is_null( $category ) && $create_missing_categories && $is_test_mode_enabled ) {
            return (object) array( 'term_id' => rand() + 1, 'parent' => 0 );
        } else if ( is_null( $category ) && $create_missing_categories ) {
            return $this->create_category( $name );
        } else if ( is_null( $category ) ) {
            $message = _x( 'No category with name "<category-name>" was found.', 'csv importer', 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '<category-name>', $name, $message );

            throw new AWPCP_CSV_Importer_Exception( $message );
        }

        return $category;
    }

    /**
     * @since feature/1112
     */
    private function create_category( $name ) {
        try {
            $category_id = $this->categories_logic->create_category( array( 'name' => $name ) );
        } catch ( AWPCP_Exception $e ) {
            $message = _x( 'There was an error trying to create category "<category-name>".', 'csv importer', 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '<category-name>', $name, $message );

            throw new AWPCP_CSV_Importer_Exception( $message, null, $e );
        }

        try {
            $category = $this->categories->get( $category_id );
        } catch ( AWPCP_Exception $e ) {
            $message = _x( 'A category with name "<category-name>" was created, but there was an error trying to retrieve its information from the database.', 'csv importer', 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '<category-name>', $name, $message );

            throw new AWPCP_CSV_Importer_Exception( $message, null, $e );
        }

        return $category;
    }

    private function parse_item_price_column( $price, $row_data ) {
        // numeric validation
        if ( ! is_numeric( $price ) ) {
            $message = _x( "Item price must be a number.", 'csv importer', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( $message );
        }

        // AWPCP stores Ad prices using an INT column (WTF!) so we need to
        // store 99.95 as 9995 and 99 as 9900.
        return $price * 100;
    }

    private function parse_start_date_column( $start_date, $row_data ) {
        return $this->parse_date_column(
            $start_date,
            $this->import_session->get_param( 'default_start_date' ),
            array(
                'empty-date-with-no-default' => _x( 'The start date is missing and no default value was defined.', 'csv importer', 'another-wordpress-classifieds-plugin' ),
                'invalid-date' => _x( 'The start date is invalid and no default value was defined.', 'csv importer', 'another-wordpress-classifieds-plugin' ),
                'invalid-default-date' => _x( "Invalid default start date.", 'csv importer', 'another-wordpress-classifieds-plugin' ),
            )
        );
    }

    private function parse_date_column( $date, $default_date, $error_messages = array() ) {
        if ( empty( $date ) && empty( $default_date ) ) {
            $message = $error_messages['empty-date-with-no-default'];
            throw new AWPCP_Exception( $message );
        }

        $parsed_value = $this->parse_date(
            $date,
            $this->import_session->get_param( 'date_format' ),
            $this->import_session->get_param( 'date_separator' ),
            $this->import_session->get_param( 'time_separator' )
        );

        // TODO: validation
        if ( empty( $parsed_value ) && ! empty( $date ) ) {
            $message = $error_messages['invalid-date'];
            throw new AWPCP_Exception( $message );
        }

        $parsed_value = $this->parse_date(
            $default_date,
            'us_date',
            $this->import_session->get_param( 'date_separator' ),
            $this->import_session->get_param( 'time_separator' )
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
            $suffix = implode($time_separator, array('%H', '%M', '%S'));
        else
            $suffix = '';

        $date = null;
        foreach ($date_formats[$date_time_format] as $_format) {
            $_format = trim(sprintf("%s %s", implode($date_separator, $_format), $suffix));
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

    private function parse_end_date_column( $end_date, $row_data ) {
        return $this->parse_date_column(
            $end_date,
            $this->import_session->get_param( 'default_end_date' ),
            array(
                'empty-date-with-no-default' => _x( 'The end date is missing and no default value was defined.', 'csv importer', 'another-wordpress-classifieds-plugin' ),
                'invalid-date' => _x( 'The end date is missing and no default value was defined.', 'csv importer', 'another-wordpress-classifieds-plugin' ),
                'invalid-default-date' => _x( "Invalid default end date.", 'csv importer', 'another-wordpress-classifieds-plugin' ),
            )
        );
    }

    private function parse_post_date_column( $post_date, $row_data ) {
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

    private function parse_post_modified_column( $post_modified, $row_data ) {
        return current_time( 'mysql' );
    }

    private function import_images( $filenames ) {
        $images_directory = $this->import_session->get_images_directory();

        if ( empty( $images_directory ) ) {
            throw new AWPCP_CSV_Importer_Exception( __( 'No images directory was configured. Are you sure you uploaded a ZIP file or defined a local directory?', 'another-wordpress-classifieds-plugin' ) );
        }

        $entries = array();

        foreach ( $filenames as $filename ) {
            if ( file_exists( "$images_directory/$filename" ) ) {
                $entries[] = "$images_directory/$filename";
            } else {
                $message = _x( 'Image file with name <image-name> not found.', 'csv importer', 'another-wordpress-classifieds-plugin' );
                $message = str_replace( '<image-name>', $filename, $message );

                throw new AWPCP_CSV_Importer_Exception( $message );
            }
        }

        return $entries;
    }

    private function save_listing_data( $listing_data ) {
        $listing_data['metadata']['_awpcp_verified'] = true;
        $listing_data['metadata']['_awpcp_payment_status'] = AWPCP_Payment_Transaction::PAYMENT_STATUS_NOT_REQUIRED;

        try {
            $a = microtime();
            $listing = $this->listings_logic->create_listing( array(
                'post_fields' => array( 'post_title' => 'Imported Listing Draft' ),
                'metadata' => array(),
            ) );
            $b = microtime();
            $d = $b - $a;

            $this->listings_logic->update_listing( $listing, $listing_data );
        } catch ( AWPCP_Exception $previous ) {
            $message = _x( 'There was an error trying to store imported data in the database', 'csv importer', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_CSV_Importer_Exception( $message, 0, $previous );
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

        do_action( 'awpcp-listing-imported', $listing, $listing_data );
    }

    private function get_extra_fields() {
        if ( is_array( $this->extra_fields ) ) {
            return $this->extra_fields;
        }

        $this->extra_fields = array();

        if ( function_exists( 'awpcp_get_extra_fields' ) ) {
            foreach ( awpcp_get_extra_fields() as $field ) {
                $this->extra_fields[ $field->field_name ] = $field;
            }
        }

        return $this->extra_fields;
    }
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
function awpcp_validate_extra_field( $name, $value, $validate, $type, $options, $enforce, &$errors ) {
    $validation_errors = array();
    $serialize = false;

    $list = null;

    switch ( $type ) {
        case 'Input Box':
        case 'Textarea Input':
            // nothing special here, proceed with validation
            break;

        case 'Checkbox':
        case 'Select Multiple':
            // value can be any combination of items from options list
            $msg = sprintf( __( "The value for Extra Field %s's is not allowed. Allowed values are: %%s", 'another-wordpress-classifieds-plugin' ), $name );
            $list = explode( ';', $value );
            $serialize = true;

        case 'Select':
        case 'Radio Button':
            $list = is_array( $list ) ? $list : array( $value );

            if ( ! isset( $msg ) ) {
                $msg = sprintf( __( "The value for Extra Field %s's is not allowed. Allowed value is one of: %%s", 'another-wordpress-classifieds-plugin' ), $name, $row );
            }

            // only attempt to validate if the field is required (has validation)
            foreach ( $list as $item ) {
                if ( empty( $item ) ) {
                    continue;
                }
                if ( ! in_array( $item, $options ) ) {
                    $msg = sprintf( $msg, implode( ', ', $options ) );
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

    if ( ! empty( $validation_errors ) ) {
        array_splice( $errors, count( $errors ), 0, $validation_errors );
        return false;
    }

    $list = is_array( $list ) ? $list : array( $value );

    foreach ( $list as $k => $item ) {
        if ( ! $enforce && empty( $item ) ) {
            continue;
        }

        switch ( $validate ) {
            case 'missing':
                if ( empty( $value ) ) {
                    $validation_errors[] = "A value for Extra Field $name is required.";
                }
                break;

            case 'url':
                if ( ! isValidURL( $item ) ) {
                    $validation_errors[] = "The value for Extra Field $name must be a valid URL.";
                }
                break;

            case 'email':
                if ( ! awpcp_is_valid_email_address( $item ) ) {
                    $validation_errors[] = "The value for Extra Field $name must be a valid email address.";
                }
                break;

            case 'numericdeci':
                if ( ! is_numeric( $item ) ) {
                    $validation_errors[] = "The value for Extra Field $name must be a number.";
                }
                break;

            case 'numericnodeci':
                if ( ! ctype_digit( $item ) ) {
                    $validation_errors[ $name ] = "The value for Extra Field $name must be an integer number.";
                }
                break;

            default:
                break;
        }
    }

    if ( ! empty( $validation_errors ) ) {
        array_splice( $errors, count( $errors ), 0, $validation_errors );
        return false;
    }

    return $value;
}
