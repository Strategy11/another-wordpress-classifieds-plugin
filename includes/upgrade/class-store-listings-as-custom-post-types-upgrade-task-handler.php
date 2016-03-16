<?php

function awpcp_store_listings_as_custom_post_types_upgrade_task_handler() {
    return new AWPCP_Upgrade_Task_Handler(
        new AWPCP_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler(
            awpcp_categories_registry(),
            awpcp_legacy_listings_metadata(),
            awpcp_wordpress(),
            $GLOBALS['wpdb']
        )
    );
}

class AWPCP_Store_Listings_As_Custom_Post_Types_Upgrade_Task_Handler implements AWPCP_Upgrade_Task_Runner {

    private $categories;
    private $legacy_listing_metadata;
    private $wordpress;
    private $db;

    public function __construct( $categories, $legacy_listing_metadata, $wordpress, $db ) {
        $this->categories = $categories;
        $this->legacy_listing_metadata = $legacy_listing_metadata;
        $this->wordpress = $wordpress;
        $this->db = $db;
    }

    public function get_last_item_id() {
        return $this->wordpress->get_option( 'awpcp-slacpt-last-listing-id' );
    }

    public function update_last_item_id( $last_item_id  ) {
        $this->wordpress->update_option( 'awpcp-slacpt-last-listing-id', $last_item_id );
    }

    public function count_pending_items( $last_item_id ) {
        $query = 'SELECT COUNT(ad_id) FROM ' . AWPCP_TABLE_ADS . ' WHERE ad_id > %d';
        return intval( $this->db->get_var( $this->db->prepare( $query, $last_item_id ) ) );
    }

    public function get_pending_items( $last_item_id ) {
        $query = 'SELECT * FROM ' . AWPCP_TABLE_ADS . ' WHERE ad_id > %d LIMIT 0, 50';
        return $this->db->get_results( $this->db->prepare( $query, $last_item_id ) );
    }

    public function process_item( $item, $last_item_id ) {
        /* create post and import standard fields as custom fields. */
        $post_id = $this->wordpress->insert_post(
            array(
                'post_content' => $item->ad_details, // TODO: do I need to strip slashes?,
                'post_title' => $item->ad_title,
                'post_name' => sanitize_title( $item->ad_title ),
                'post_status' => 'draft',
                'post_type' => 'awpcp_listing',
                // 'post_excerpt' => '',
                'post_date' => $item->ad_postdate,
                'post_date_gmt' => get_gmt_from_date( $item->ad_postdate ),
                'post_modified' => $item->ad_last_updated,
                'post_modified_gmt' => get_gmt_from_date( $item->ad_last_updated ),
                'comment_status' => 'closed',
            ),
            true // return a WP_Error object on failure
        );

        if ( is_wp_error( $post_id ) ) {
            throw new AWPCP_Exception( sprintf( "A custom post entry couldn't be created for listing %d.", $item->ad_id ) );
        }

        /* update post status and meta information */
        $this->update_post_status_with_item_properties( $post_id, $item );

        /* store listing properties as custom fields */
        $this->update_post_metadata_with_item_properties( $post_id, $item );

        /* import information from ad_meta table */
        $this->update_post_metadata_with_item_metadata( $post_id, $item );

        $this->update_post_terms_with_item_properties( $post_id, $item );

        $this->update_post_author_with_item_properties( $post_id, $item );

        /* update references to listing's id in ad_regions table */
        $sql = 'UPDATE ' . AWPCP_TABLE_AD_REGIONS . ' SET ad_id = %d WHERE ad_id = %d';
        $this->db->query( $this->db->prepare( $sql, $post_id, $item->ad_id ) );

        /* store old listing's ad_id in custom field so premium modules can rebuild relationships */
        $this->wordpress->add_post_meta( $post_id, '_old_id', $item->ad_id );

        return $item->ad_id;
    }

    private function update_post_status_with_item_properties( $post_id, $item ) {
        if ( $item->payment_status === 'Unpaid' ) {
            $this->wordpress->update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
            $this->wordpress->add_post_meta( $post_id, '_payment_pending', true );
        } else if ( $item->disabled || $item->verified != 1 ) {
            $this->wordpress->update_post( array( 'ID' => $post_id, 'post_status' => 'disabled' ) );
        } else {
            $this->wordpress->update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
        }

        // update verified status
        if ( $item->verified != 1 ) {
            $this->wordpress->add_post_meta( $post_id, '_verfication_needed', true );
        } else {
            $this->wordpress->add_post_meta( $post_id, '_verified', true );
        }

        // update reviewed status
        $reviewed = $this->legacy_listing_metadata->get( $item->ad_id, 'reviewed' );

        if ( is_null( $reviewed ) || $reviewed ) {
            $this->wordpress->add_post_meta( $post_id, '_reviewed', true );
        } else {
            $this->wordpress->add_post_meta( $post_id, '_content_needs_review', true );
        }

        // update expired status
        if ( strtotime( $item->ad_enddate ) < current_time( 'timestamp' ) ) {
            $this->wordpress->add_post_meta( $post_id, '_expired', true );
        }
    }

    public function update_post_metadata_with_item_properties( $post_id, $item ) {
        $this->wordpress->add_post_meta( $post_id, '_payment_term_id', $item->adterm_id );
        $this->wordpress->add_post_meta( $post_id, '_payment_term_type', $item->payment_term_type );
        $this->wordpress->add_post_meta( $post_id, '_payment_gateway', $item->payment_gateway );
        $this->wordpress->add_post_meta( $post_id, '_payment_amount', $item->ad_fee_paid );
        $this->wordpress->add_post_meta( $post_id, '_payment_status', $item->payment_status );
        $this->wordpress->add_post_meta( $post_id, '_payer_email', $item->payer_email );
        $this->wordpress->add_post_meta( $post_id, '_contact_name', $item->ad_contact_name );
        $this->wordpress->add_post_meta( $post_id, '_contact_phone', $item->ad_contact_phone );
        $this->wordpress->add_post_meta( $post_id, '_contact_email', $item->ad_contact_email );
        $this->wordpress->add_post_meta( $post_id, '_website_url', $item->websiteurl );
        $this->wordpress->add_post_meta( $post_id, '_price', $item->ad_item_price );
        $this->wordpress->add_post_meta( $post_id, '_views', $item->ad_views );
        $this->wordpress->add_post_meta( $post_id, '_last_updated', $item->ad_last_updated );
        $this->wordpress->add_post_meta( $post_id, '_start_date', $item->ad_startdate );
        $this->wordpress->add_post_meta( $post_id, '_end_date', $item->ad_enddate );
        $this->wordpress->add_post_meta( $post_id, '_most_recent_start_date', $item->ad_startdate );
        $this->wordpress->add_post_meta( $post_id, '_disabled_date', $item->disabled_date );
        $this->wordpress->add_post_meta( $post_id, '_flagged', $item->flagged );
        $this->wordpress->add_post_meta( $post_id, '_verification_date', $item->verified_at );
        $this->wordpress->add_post_meta( $post_id, '_access_key', $item->ad_key );
        $this->wordpress->add_post_meta( $post_id, '_transaction_id', $item->ad_transaction_id );
        $this->wordpress->add_post_meta( $post_id, '_poster_ip', $item->posterip );
        $this->wordpress->add_post_meta( $post_id, '_renew_email_sent', $item->renew_email_sent );
        $this->wordpress->add_post_meta( $post_id, '_renewed_date', $item->renewed_date );
        $this->wordpress->add_post_meta( $post_id, '_is_paid', $item->ad_fee_paid > 0 );
    }

    private function update_post_metadata_with_item_metadata( $post_id, $item ) {
        // 'reviewed' was handled in update_post_status_with_item_properties()
        $meta_keys = array(
            'sent-to-facebook' => 'sent_to_facebook_page',
            'sent-to-facebook-group' => 'sent_to_facebook_group',
            'verification_email_sent_at' => 'verification_email_sent_at',
            'verification_emails_sent' => 'verification_emails_sent',
        );

        foreach ( $meta_keys as $old_key => $new_key ) {
            if ( $this->legacy_listing_metadata->get( $item->ad_id, $old_key ) ) {
                $this->wordpress->add_post_meta( $post_id, $new_key, true );
            }
        }
    }

    private function update_post_terms_with_item_properties( $post_id, $item ) {
        if ( empty( $item->ad_category_id ) ) {
            return;
        }

        $categories_registry = $this->categories->get_categories_registry();

        if ( ! isset( $categories_registry[ $item->ad_category_id ] ) ) {
            return;
        }

        $this->wordpress->add_object_terms(
            $post_id,
            $categories_registry[ $item->ad_category_id ],
            'awpcp_listing_category'
        );
    }

    private function update_post_author_with_item_properties( $post_id, $item ) {
        if ( empty( $item->user_id ) ) {
            $user = null;
        } else {
            $user = $this->wordpress->get_user_by( 'id', $item->user_id );
        }

        if ( is_a( $user, 'WP_User' ) ) {
            $user_id = $user->ID;
        } else {
            $user_id = 0;
        }

        $this->wordpress->update_post( array( 'ID' => $post_id, 'post_author' => $user_id ) );
    }
}
