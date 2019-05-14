<?php
/**
 * CSV import class
 *
 * @package Includes/Admin/CSV Exporter
 */

/**
 * CSV export.
 *
 * @since 4.1.0
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AWPCP_CSVExporter {
	// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fwrite
	// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fclose
	// phpcs:disableWordPress.WP.AlternativeFunctions.file_system_read_fopen
	const BATCH_SIZE = 20;

	private $settings = array(
		'csv-file-separator'    => ',',
		'time-separator'        => ';',
		'date-separator'        => ';',
		'images-separator'      => ';',
		'category-separator'    => ';',
		'export-images'         => false,
		'include-users'         => false,
		'listing_status'        => 'all',
		'generate-sequence-ids' => false,
	);

	private $settings_api;

	private $workingdir = '';

	private $columns  = array();
	private $listings = array();
	private $exported = 0;

	public function __construct( $settings, $settings_api, $workingdir = null, $listings = array() ) {
		$this->settings     = array_merge( $this->settings, $settings );
		$this->settings_api = $settings_api;

		$this->setup_columns();
		$this->setup_working_dir( $workingdir );
		$this->get_listings( $listings );
	}

	public function setup_columns() {
		if ( $this->settings['generate-sequence-ids'] ) {
			$this->columns['sequence_id'] = 'sequence_id';
		}

		if ( $this->settings['export-images'] ) {
			$this->columns['images'] = 'images';
		}

		if ( $this->settings['include-users'] ) {
			$this->columns['username'] = 'username';
		}

		if ( $this->settings_api->get_option( 'displaycountyvillagefield' ) ) {
			$this->columns['county_village'] = 'county_village';
		}

		$this->columns['title']             = 'ad_title';
		$this->columns['details']           = 'ad_details';
		$this->columns['username']          = 'username';
		$this->columns['category_name']     = 'ad_category';
		$this->columns['contact_name']      = 'ad_contact_name';
		$this->columns['contact_email']     = 'ad_contact_email';
		$this->columns['contact_phone']     = 'ad_contact_phone';
		$this->columns['website_url']       = 'websiteurl';
		$this->columns['item_price']        = 'ad_item_price';
		$this->columns['start_date']        = 'start_date';
		$this->columns['end_date']          = 'end_date';
		$this->columns['payment_term_id']   = 'payment_term_id';
		$this->columns['payment_term_type'] = 'payment_term_type';
		$this->columns['country']           = 'country';
		$this->columns['state']             = 'state';
		$this->columns['city']              = 'city';
	}

	/**
	 * @throws AWPCP_Exception If unable to create exports directory.
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function setup_working_dir( $workingdir ) {
		$this->workingdir = $workingdir;

		if ( ! $workingdir ) {
			$direrror = '';

			$upload_dir = wp_upload_dir();

			if ( ! $upload_dir['error'] ) {
				$csvexportsdir = rtrim( $upload_dir['basedir'], DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'awpcp-csv-exports';
				if ( is_dir( $csvexportsdir ) || mkdir( $csvexportsdir ) ) {
					$this->workingdir = rtrim( $csvexportsdir . DIRECTORY_SEPARATOR . uniqid(), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

					if ( ! mkdir( $this->workingdir, 0777 ) ) {
						$direrror = _x( 'Could not create a temporary directory for handling this CSV export.', 'admin csv-export', 'another-wordpress-classifieds-plugin' );
					}
				} else {
					$direrror = _x( 'Could not create awpcp-csv-exports directory.', 'admin csv-export', 'another-wordpress-classifieds-plugin' );
				}
			}

			if ( $direrror ) {
				/* translators: %s the error. */
				throw new Exception( sprintf( _x( 'Error while creating a temporary directory for CSV export: %s', 'admin csv-export', 'another-wordpress-classifieds-plugin' ), $direrror ) );
			}
        }

	}

	public function get_listings( $listings ) {
		if ( $listings ) {
			$this->listings = $listings;

			return false;
		}
		switch ( $this->settings['listing_status'] ) {
			case 'publish+disabled':
				$post_status = array( 'publish', 'disabled', 'pending' );
				break;
			case 'publish':
				$post_status = 'publish';
				break;
			case 'all':
			default:
				$post_status = array( 'publish', 'draft', 'pending', 'private', 'future', 'trash', 'disabled' );
				break;
		}

		$this->listings = get_posts(
			array(
				'post_status'    => $post_status,
				'posts_per_page' => - 1,
				'post_type'      => AWPCP_LISTING_POST_TYPE,
				'fields'         => 'ids',
			)
		);

	}

	public static function &from_state( $state ) {
		$export           = new self( $state['settings'], awpcp_settings_api(), trailingslashit( $state['workingdir'] ), (array) $state['listings'] );
		$export->exported = abs( intval( $state['exported'] ) );

		return $export;
	}

	public function get_state() {
		return array(
			'settings'   => $this->settings,
			'columns'    => array_keys( $this->columns ),
			'workingdir' => $this->workingdir,
			'listings'   => $this->listings,
			'exported'   => $this->exported,
			'filesize'   => file_exists( $this->get_file_path() ) ? filesize( $this->get_file_path() ) : 0,
			'done'       => $this->is_done(),
		);
	}

	public function cleanup() {
		$upload_dir = wp_upload_dir();

		awpcp_rmdir( $this->workingdir );

		if ( ! $upload_dir['error'] ) {
			$csvexportsdir = rtrim( $upload_dir['basedir'], DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'awpcp-csv-exports';
			$contents      = awpcp_scandir( $csvexportsdir );

			if ( ! $contents ) {
				awpcp_rmdir( $csvexportsdir );
			}
		}
	}

	public function advance() {
		if ( $this->is_done() ) {
			return;
		}

		$csvfile = $this->get_csvfile( $this->workingdir . 'export.csv' );

		// Write header as first line.
		if ( $this->exported === 0 ) {
			fwrite( $csvfile, $this->prepare_header( $this->header() ) );
		}

		$nextlistings = array_slice( $this->listings, $this->exported, self::BATCH_SIZE );

		foreach ( $nextlistings as $listing_id ) {
			$data = $this->extract_data( $listing_id );
			if ( $data ) {
				$content = implode( $this->settings['csv-file-separator'], $data );
				fwrite( $csvfile, $this->prepare_content( $content ) );
			}

			$this->exported ++;
		}

		fclose( $csvfile );

		if ( $this->is_done() ) {
			if ( file_exists( $this->workingdir . 'images.zip' ) ) {
				unlink( $this->workingdir . 'export.zip' );
				$zip = $this->get_pclzip_instance( $this->workingdir . 'export.zip' );

				$files   = array();
				$files[] = $this->workingdir . 'export.csv';
				$files[] = $this->workingdir . 'images.zip';

				$zip->create( implode( ',', $files ), PCLZIP_OPT_REMOVE_ALL_PATH );

				unlink( $this->workingdir . 'export.csv' );
				unlink( $this->workingdir . 'images.zip' );
			}
		}
	}

	protected function get_csvfile( $path ) {
		return fopen( $path, 'a' );
	}

	protected function get_pclzip_instance( $path ) {
		if ( ! class_exists( 'PclZip' ) ) {
			define( 'PCLZIP_TEMPORARY_DIR', $this->workingdir );
			require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
		}

		return new PclZip( $path );
	}

	public function is_done() {
		return $this->exported === count( $this->listings );
	}

	private function prepare_header( $header ) {
		$bom = "\xEF\xBB\xBF"; /* UTF-8 BOM */
		return $bom . $this->prepare_content( $header );
	}

	private function prepare_content( $content ) {
        // remove line break to avoid empty line on last write.
        if ( $this->exported !== count( $this->listings ) - 1 ) {
            $content = $content . "\n";
        }

		return $content;
	}

	public function get_file_path() {
		if ( file_exists( $this->workingdir . 'export.zip' ) ) {
			return $this->workingdir . 'export.zip';
		}

		return $this->workingdir . 'export.csv';
	}

	public function get_file_url() {
		$uploaddir = wp_upload_dir();
		$urldir    = trailingslashit( untrailingslashit( $uploaddir['baseurl'] ) . '/' . ltrim( str_replace( DIRECTORY_SEPARATOR, '/', str_replace( $uploaddir['basedir'], '', $this->workingdir ) ), '/' ) );

		if ( file_exists( $this->workingdir . 'export.zip' ) ) {
			return $urldir . 'export.zip';
		}

		return $urldir . 'export.csv';

	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 * @return bool|string
	 */
	private function header() {
		$out = '';

		foreach ( $this->columns as $colname => &$col ) {
			$out .= $colname;
			$out .= $this->settings['csv-file-separator'];
		}

		$out = substr( $out, 0, - 1 );

		return $out;
	}

	private function prepare_images( $post_id ) {
		$images        = array();
		$image_objects = get_attached_media( 'image', $post_id );
		if ( count( $image_objects ) > 0 ) {
			$upload_dir = wp_upload_dir();

			foreach ( $image_objects as $image ) {
				$img_meta = wp_get_attachment_metadata( $image->ID );

				if ( empty( $img_meta['file'] ) ) {
					continue;
				}

				$img_path = realpath( $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $img_meta['file'] );

				if ( ! is_readable( $img_path ) ) {
					continue;
				}

				$this->images_archive = ( ! isset( $this->images_archive ) ) ? $this->get_pclzip_instance( $this->workingdir . 'images.zip' ) : $this->images_archive;
				$success              = $this->images_archive->add( $img_path, PCLZIP_OPT_REMOVE_ALL_PATH );
				if ( $success ) {
					$images[] = basename( $img_path );
				}
			}
		}

		return implode( $this->settings['images-separator'], $images );
	}

    private function prepare_categories( $post_id ) {
        $categories = get_the_terms( $post_id, AWPCP_CATEGORY_TAXONOMY );
        $term_array = array();
        foreach ( $categories as $category ) {
            $term_array[] = $category->name;
        }

        return implode( $this->settings['category-separator'], $term_array );
    }

	/**
	 * @param int $post_id the post id to extract data from.
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @return array|bool
	 */
	private function extract_data( $post_id ) {
		global $awpcp;
		$listing                    = get_post( $post_id );
		$listing_data               = $awpcp->container['FormFieldsData']->get_stored_data( $listing );
		$start_date                 = date_create( $listing_data['start_date'] );
		$listing_data['start_date'] = date_format( $start_date, 'm/d/y H:i:s' );
		$end_date                   = date_create( $listing_data['end_date'] );
		$listing_data['end_date']   = date_format( $end_date, 'm/d/y H:i:s' );
		if ( ! $listing ) {
			return false;
		}

		$data = array();

		foreach ( $this->columns as $column_name => $meta ) {
			$value = '';

			switch ( $column_name ) {
				case 'title':
					$value = $listing->post_title;
					break;
				case 'username':
					$author = get_userdata( $listing->post_author );
					$value  = $author ? $author->user_login : '';
					break;
				case 'images':
					$value = $this->prepare_images( $post_id );
					break;
				case 'country':
					$value = $listing_data['regions'][0]['country'];
					break;
				case 'county_village':
					$value = $listing_data['regions'][0]['county'];
					break;
				case 'state':
					$value = $listing_data['regions'][0]['state'];
					break;
				case 'city':
					$value = $listing_data['regions'][0]['city'];
					break;
				case 'category_name':
                    $value = $this->prepare_categories( $post_id );
					break;
				case 'sequence_id':
                    $sequence_id = "awpcp-{$post_id}";
                    update_post_meta( $post_id, '_awpcp_sequence_id', $sequence_id);
                    $value = $sequence_id;
					break;
				case 'payment_term_id':
					$value = get_post_meta( $post_id, '_awpcp_payment_term_id', true );
					break;
				case 'payment_term_type':
					$value = get_post_meta( $post_id, '_awpcp_payment_term_type', true );
					break;
				default:
					$value = $listing_data[ $meta ];
					break;
			}

			if ( ! is_string( $value ) && ! is_array( $value ) ) {
				$value = strval( $value );
			}

			$data[ $column_name ] = '"' . str_replace( '"', '""', $value ) . '"';
		}

		return $data;
	}
}
