<?php

class AWPCP_Ad {

	public $ad_id;

	static function from_object($object) {
		$ad = new AWPCP_Ad;

		$ad->ad_id = $object->ad_id;
		$ad->adterm_id = $object->adterm_id; // fee plan id
		$ad->payment_term_type = $object->payment_term_type;
		$ad->ad_fee_paid = $object->ad_fee_paid;
		$ad->ad_category_id = $object->ad_category_id;
		$ad->ad_category_parent_id = $object->ad_category_parent_id;
		$ad->ad_title = $object->ad_title;
		$ad->ad_details = $object->ad_details;
		$ad->ad_contact_name = $object->ad_contact_name;
		$ad->ad_contact_phone = $object->ad_contact_phone;
		$ad->ad_contact_email = $object->ad_contact_email;
		$ad->ad_city = $object->ad_city;
		$ad->ad_state = $object->ad_state;
		$ad->ad_country = $object->ad_country;
		$ad->ad_county_village = $object->ad_county_village;
		$ad->ad_item_price = $object->ad_item_price;
		$ad->ad_views = $object->ad_views;
		$ad->ad_postdate = $object->ad_postdate;
		$ad->ad_last_updated = $object->ad_last_updated;
		$ad->ad_startdate = $object->ad_startdate;
		$ad->ad_enddate = $object->ad_enddate;
		$ad->ad_key = $object->ad_key;
		$ad->ad_transaction_id = $object->ad_transaction_id;
		$ad->user_id = $object->user_id;

		$ad->payment_gateway = $object->payment_gateway;
		$ad->payment_status = $object->payment_status;
		$ad->payer_email = $object->payer_email;

		$ad->is_featured_ad = $object->is_featured_ad;
		$ad->flagged = $object->flagged;

		$ad->disabled = $object->disabled;
		$ad->disabled_date = $object->disabled_date;

		$ad->renewed_date = $object->renewed_date;
		$ad->renew_email_sent = $object->renew_email_sent;

		$ad->verified = $object->verified;
		$ad->verified_at = $object->verified_at;

		$ad->websiteurl = $object->websiteurl;
		$ad->posterip = $object->posterip;

		return apply_filters( 'awpcp-listing-from-object', $ad, $object );
	}

	public static function find_by_category_id($id) {
		return self::find(sprintf('ad_category_id = %d', (int) $id));
	}

	/**
	 * @since 3.0.0
	 */
	public static function find_by_email($email) {
		global $wpdb;
		return self::find( $wpdb->prepare( 'ad_contact_email = %s', $email ) );
	}

	public static function find_by_email_and_key($email, $key) {
		global $wpdb;
		$where = 'ad_key = %s AND ad_contact_email = %s';
		return self::find_by($wpdb->prepare($where, $key, $email));
	}

	public static function find_by($where) {
		$results = AWPCP_Ad::find($where);
		if (!empty($results)) {
			return $results[0];
		}
		return null;
	}

	public static function get_order_conditions($order) {
		$basedate = 'CASE WHEN renewed_date IS NULL THEN ad_startdate ELSE GREATEST(ad_startdate, renewed_date) END';
		$is_paid = 'CASE WHEN ad_fee_paid > 0 THEN 1 ELSE 0 END';

		switch ( $order ) {
			case 1:
				$parts = array( "$basedate DESC" );
				break;
			case 2:
				$parts = array( 'ad_title ASC' );
				break;
			case 3:
				$parts = array( "$is_paid DESC", "$basedate DESC" );
				break;
			case 4:
				$parts = array( "$is_paid DESC", 'ad_title ASC' );
				break;
			case 5:
				$parts = array( 'ad_views DESC', 'ad_title ASC' );
				break;
			case 6:
				$parts = array( 'ad_views DESC', "$basedate DESC" );
				break;
			case 7:
				$parts = array( 'ad_item_price DESC', "$basedate DESC" );
				break;
			case 8:
				$parts = array( 'ad_item_price ASC', "$basedate DESC" );
				break;
			case 9:
				$parts = array( "$basedate ASC" );
				break;
			case 10:
				$parts = array( 'ad_title DESC' );
				break;
			case 11:
				$parts = array( 'ad_views ASC', "ad_title ASC" );
				break;
			case 12:
				$parts = array( 'ad_views ASC', "$basedate ASC" );
				break;
			default:
				$parts = array( 'ad_postdate DESC', 'ad_title ASC' );
				break;
		}

		return array_filter( apply_filters( 'awpcp-ad-order-conditions', $parts, $order ) );
	}

	/**
	 * @since 3.3
	 */
	public static function get_where_conditions_for_valid_ads( $conditions = array() ) {
		$conditions = self::get_where_conditions_for_successfully_paid_listings( $conditions );

        $conditions[] = "verified = 1";

        return array_filter( apply_filters( 'awpcp-ad-where-statement', array_filter( $conditions, 'strlen' ) ) );
	}

	public static function get_where_conditions_for_successfully_paid_listings( $conditions ) {
        $conditions[] = "payment_status != 'Unpaid'";

        if ( ( get_awpcp_option( 'enable-ads-pending-payment' ) == 0 ) && ( get_awpcp_option( 'freepay' ) == 1 ) ) {
            $conditions[] = "payment_status != 'Pending'";
        }

        return $conditions;
	}

	public static function get_where_conditions($conditions=array()) {
		$conditions = self::get_where_conditions_for_valid_ads( $conditions );
		$conditions = array_merge( $conditions, array( "disabled = 0" ) );

        return $conditions;
	}

	public static function get_random_ads($limit, $args=array(), $conditions=array()) {
        $conditions = self::get_where_conditions($conditions);

		$tmpargs = array_merge($args, array('fields' => 'ad_id', 'limit' => 0, 'offset' => 0));
        $results = self::query(array_merge($tmpargs, array('where' => join(' AND ', $conditions))), true);

        $random_ids = awpcp_get_properties($results, 'ad_id');
        shuffle($random_ids);
        $random_ids = array_slice($random_ids, 0, $limit);
        if ($random_ids) {
        	$conditions[] = 'ad_id IN (' . join(',', $random_ids) . ')';
        }

        $args = array_merge($args, array('limit' => 0, 'offset' => 0));

        return self::get_enabled_ads($args, $conditions);
	}

	/**
	 * @param  array  	$args 	paramaters used to build the SQL query.
	 * @param  boolean 	$raw	if true, returns an Array of Objects, if false
	 *                       	will return an Array of AWPCP_Ad or an integer
	 *                        	depending on the 'fields' arg.
	 * @return mixed
	 */
	public static function query($args, $raw=false) {
		global $wpdb;

		extract(wp_parse_args($args, array(
			'fields' => '*',
			'join' => false,
			'where' => '1 = 1',
			'order' => array( 'ad_startdate DESC' ),
			'offset' => 0,
			'limit' => 0,
			'groupby' => false,
		)));

		$query = 'SELECT %s FROM ' . AWPCP_TABLE_ADS . ' ';

		if ($fields == 'count') {
        	$query = sprintf($query, 'COUNT( DISTINCT ' . AWPCP_TABLE_ADS .'.ad_id )');
        	$limit = 0;
        } else {
        	$query = sprintf($query, $fields);
        }

        if ( $join !== false ) {
        	$query = $query . $join . ' ';
        }

        $query.= sprintf('WHERE %s ', $where);

        if ($groupby !== false) {
        	$query.= sprintf('GROUP BY %s ', $groupby);
        }

        if ( ! empty( $order ) ) {
        	$query.= sprintf('ORDER BY %s ', join( ', ', $order ));
        }

        if ($limit > 0) {
        	$query.= sprintf('LIMIT %d, %d', $offset, $limit);
        }

        if ($fields == 'count') {
        	return $wpdb->get_var($query);
        } else if ($raw) {
        	return $wpdb->get_results($query);
        } else {
			$items = $wpdb->get_results($query);
			$results = array();

			foreach($items as $item) {
				$results[] = AWPCP_Ad::from_object($item);
			}

			return $results;
        }
	}

	/**
	 * @since unknown
	 */
	public static function find($where='1 = 1', $order='id', $offset=false, $results=false) {
		global $wpdb;

		switch ($order) {
			case 'titleza':
				$order = "ad_title DESC";
				break;
			case 'titleaz':
				$order = "ad_title ASC";
				break;
			case 'awaitingapproval':
				$order = "disabled DESC, ad_key DESC";
				break;
			case 'paidfirst':
				$order = "payment_status DESC, ad_key DESC";
				break;
			case 'mostrecent':
				$order = "ad_startdate DESC";
				break;
			case 'oldest':
				$order = "ad_startdate ASC";
				break;
			case 'renewed':
				$order = 'renewed_date DESC, ad_startdate DESC';
				break;
			case 'featured':
				$order = "is_featured_ad DESC, ad_startdate DESC";
				break;
			case 'flagged':
				$order = "ad_startdate DESC";
				$where .= ' AND flagged = 1 ';
				break;
			default:
				$order = 'ad_id DESC';
				break;
		}

		$query = "SELECT * FROM " . AWPCP_TABLE_ADS . " WHERE $where ";
		$query.= "ORDER BY $order ";

		if ($offset !== false && $results !== false)
			$query.= "LIMIT $offset,$results";

		$items = $wpdb->get_results($query);
		$results = array();

		foreach($items as $item) {
			$results[] = AWPCP_Ad::from_object($item);
		}

		return $results;
	}

	public static function count($where='1=1') {
		global $wpdb;

		$query = "SELECT COUNT(*) FROM " . AWPCP_TABLE_ADS . " WHERE $where";
		$n = $wpdb->get_var($query);

		return $n !== FALSE ? $n : 0;
	}

	protected function sanitize($data) {
		$sanitized = $data;

		// make sure dates are dates or NULL, MySQL Strict mode does not allow empty strings
		$columns = array('ad_postdate', 'ad_last_updated', 'ad_startdate', 'ad_enddate',
						 'disabled_date', 'renewed_date', 'verified_at');
		foreach ($columns as $column) {
			$value = trim($sanitized[$column]);
			if ( ! awpcp_is_mysql_date( $value ) ) {
				// Remove this column. Not a valid date or datetime and
				// WordPress does not handle NULL values very well:
				// http://core.trac.wordpress.org/ticket/15158
				unset($sanitized[$column]);
			} else {
				$sanitized[$column] = $value;
			}
		}

		// make sure values for float columns are float
		$columns = array( 'ad_fee_paid' );
		foreach ($columns as $column) {
			$sanitized[ $column ] = floatval( trim( $sanitized[ $column ] ) );
		}

		// make sure values for int/tinyint columns are int
		$columns = array('ad_id', 'adterm_id', 'ad_category_id', 'ad_category_parent_id',
						 'ad_views',
						 'disabled', 'is_featured_ad', 'flagged', 'renew_email_sent', 'verified',
						 'ad_item_price');
		foreach ($columns as $column) {
			$sanitized[$column] = intval(trim($sanitized[$column]));
		}

		return $sanitized;
	}

	public function save() {
		global $wpdb;

		$data = array(
			'ad_id' => awpcp_get_property($this, 'ad_id'),
			'adterm_id' => awpcp_get_property($this, 'adterm_id'),
			'payment_term_type' => awpcp_get_property($this, 'payment_term_type'),
			'ad_fee_paid' => awpcp_get_property($this, 'ad_fee_paid'),
			'ad_category_id' => awpcp_get_property($this, 'ad_category_id'),
			'ad_category_parent_id' => awpcp_get_property($this, 'ad_category_parent_id'),
			'ad_title' => awpcp_get_property($this, 'ad_title'),
			'ad_details' => awpcp_get_property($this, 'ad_details'),
			'ad_contact_name' => awpcp_get_property($this, 'ad_contact_name'),
			'ad_contact_phone' => awpcp_get_property($this, 'ad_contact_phone'),
			'ad_contact_email' => awpcp_get_property($this, 'ad_contact_email'),
			'ad_city' => awpcp_get_property($this, 'ad_city'),
			'ad_state' => awpcp_get_property($this, 'ad_state'),
			'ad_country' => awpcp_get_property($this, 'ad_country'),
			'ad_county_village' => awpcp_get_property($this, 'ad_county_village'),
			'ad_item_price' => awpcp_get_property($this, 'ad_item_price'),
			'ad_views' => awpcp_get_property($this, 'ad_views'),
			'ad_postdate' => awpcp_get_property($this, 'ad_postdate'),
			'ad_last_updated' => awpcp_get_property($this, 'ad_last_updated'),
			'ad_startdate' => awpcp_get_property($this, 'ad_startdate'),
			'ad_enddate' => awpcp_get_property($this, 'ad_enddate'),
			'ad_key' => awpcp_get_property($this, 'ad_key'),
			'ad_transaction_id' => awpcp_get_property($this, 'ad_transaction_id'),
			'user_id' => awpcp_get_property($this, 'user_id'),

			'payment_gateway' => awpcp_get_property($this, 'payment_gateway'),
			'payment_status' => awpcp_get_property($this, 'payment_status'),
			'payer_email' => awpcp_get_property( $this, 'payer_email' ),

			'is_featured_ad' => awpcp_get_property($this, 'is_featured_ad'),
			'flagged' => awpcp_get_property($this, 'flagged'),
			'disabled' => awpcp_get_property($this, 'disabled'),
			'disabled_date' => awpcp_get_property($this, 'disabled_date'),

			'renew_email_sent' => awpcp_get_property($this, 'renew_email_sent'),
			'renewed_date' => awpcp_get_property($this, 'renewed_date'),

			'verified' => awpcp_get_property( $this, 'verified' ),
			'verified_at' => awpcp_get_property( $this, 'verified_at' ),

			'websiteurl' => awpcp_get_property($this, 'websiteurl'),
			'posterip' => awpcp_get_property($this, 'posterip')
		);

		$data = $this->sanitize($data);

		if (empty($this->ad_id)) {
			$result = $wpdb->insert(AWPCP_TABLE_ADS, $data);
			$this->ad_id = $wpdb->insert_id;
		} else {
			$result = $wpdb->update(AWPCP_TABLE_ADS, $data, array('ad_id' => $this->ad_id));
		}

		return $result === false ? false : true;
	}

	public function delete() {
		global $wpdb;

		do_action('awpcp_before_delete_ad', $this);

		$media = awpcp_media_api()->find_by_ad_id( $this->ad_id );
		foreach ( $media as $file ) {
			awpcp_media_api()->delete( $file );
		}

		$query = 'DELETE FROM ' . AWPCP_TABLE_AD_REGIONS . ' WHERE ad_id = %d';
		$result = $wpdb->query( $wpdb->prepare( $query, $this->ad_id ) );

		$query = 'DELETE FROM ' . AWPCP_TABLE_ADS . ' WHERE ad_id = %d';
		$result = $wpdb->query($wpdb->prepare($query, $this->ad_id));

		do_action('awpcp_delete_ad', $this);

		return $result === false ? false : true;
	}

	public function get_category_name() {
		if ( ! isset( $this->category_name ) ) {
			$this->category_name = get_adcatname( $this->ad_category_id );
		}
		return $this->category_name;
	}

	/**
	 * @since 3.0.0
	 */
	public function get_access_key() {
		if ( empty( $this->ad_key ) ) {
            $access_key = AWPCP_Ad::generate_key();
            $this->ad_key = apply_filters( 'awpcp-listing-access-key', $access_key, $this );
            $this->save();
		}

		return $this->ad_key;
	}

	/**
	 * @since 2.0.7
	 */
	function set_start_date($start_date) {
		$this->ad_startdate = awpcp_datetime( 'mysql', $start_date );
	}

	/**
	 * @since 3.0
	 */
	public function get_start_timestamp() {
		return strtotime($this->ad_startdate);
	}

	/**
	 * @since 2.0.7
	 */
	function set_end_date($end_date) {
		$this->ad_enddate = awpcp_datetime( 'mysql', $end_date );
	}

	/**
	 * @since 3.0
	 */
	public function get_end_timestamp() {
		return strtotime($this->ad_enddate);
	}

	function get_disabled_date() {
		if (!empty($this->disabled_date))
			return awpcp_datetime( 'awpcp-date', strtotime( $this->disabled_date ) );
		return '';
	}

	function get_renewed_date() {
		if (!empty($this->renewed_date))
			return awpcp_datetime( 'awpcp-date', strtotime( $this->renewed_date ) );
		return '';
	}

	function count_image_files() {
		return awpcp_media_api()->count_images_by_ad_id( $this->ad_id );
	}

	/**
	 * @since  3.0-beta22
	 * @deprecated 3.4. Use awpcp_listings_api()->flag().
	 */
	public function flag() {
		$this->flagged = 1;
		return $this->save();
	}

	/**
	 * @deprecated 3.4. Use awpcp_listings_api()->unflag().
	 */
	public function unflag() {
		$this->flagged = 0;
		return $this->save();
	}

	public function set_featured_status($featured) {
		global $wpdb;

		$query = 'UPDATE ' . AWPCP_TABLE_ADS . ' SET ';
		$query.= 'is_featured_ad=' . intval($featured) . ' WHERE ad_id = %d';

		if ($result = $wpdb->query($wpdb->prepare($query, $this->ad_id))) {
			$this->is_featured_ad = $featured;
		}

		return $result;
	}

	public function mark_as_spam() {
		// this doesn't feel right :\
		if ($result = $this->delete()) {
			$spam_submitter = awpcp_listing_spam_submitter();
			$spam_submitter->submit( (array) $this );
        }

        return $result;
	}

	/**
	 * Increase the number of views of this Ad by 1.
	 *
	 * @since 3.0
	 */
	public function visit() {
		$this->ad_views = $this->ad_views + 1;
	}
}
