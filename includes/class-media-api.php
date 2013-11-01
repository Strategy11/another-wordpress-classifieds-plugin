<?php

class AWPCP_MediaAPI {
    private static $instance = null;

    private function __construct() {}

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new AWPCP_MediaAPI();
        }
        return self::$instance;
    }

    private function translate( $object ) {
        $properties = array(
            'id' => awpcp_get_property( $object, 'id', null ),
            'ad_id' => awpcp_get_property( $object, 'ad_id', null ),
            'name' => awpcp_get_property( $object, 'name', null ),
            'path' => awpcp_get_property( $object, 'path', null ),
            'mime_type' => awpcp_get_property( $object, 'mime_type', null ),
            'enabled' => awpcp_get_property( $object, 'enabled', null ),
            'is_primary' => awpcp_get_property( $object, 'is_primary', null ),
        );

        $data = array();
        foreach ( $properties as $name => $value ) {
            if ( ! is_null( $value ) ) {
                $data[ $name ] = $value;
            }
        }

        return $data;
    }

    public function create( $args ) {
        extract( wp_parse_args( $args, array( 'enabled' => null, 'is_primary' => false, ) ) );

        $image_mime_types = awpcp_get_image_mime_types();

        if ( ! is_null( $enabled ) ) {
            // pass
        } else if ( ! awpcp_current_user_is_admin() ) {
            if ( in_array( $mime_type, $image_mime_types ) && get_awpcp_option( 'imagesapprove' ) ) {
                $enabled = false;
            }
        } else {
            $enabled = true;
        }

        $data = compact( 'ad_id', 'name', 'path', 'mime_type', 'enabled', 'is_primary' );

        if ( $insert_id = $this->save( $data ) ) {
            return $this->find_by_id( $insert_id );
        } else {
            return null;
        }
    }

    /**
     * @return      an integer (new row id) if a new row was added to the table.
     *              true if the media was properly updated.
     *              false if the data couldn't be saved.
     * @since 3.0.2
     */
    public function save( $data ) {
        global $wpdb;

        if ( is_object( $data ) ) {
            if ( ! awpcp_get_property( $data, 'id', false ) ) {
                $data->created = awpcp_datetime();
            }
            $data = $this->translate( $data );
        } else if ( is_array( $data ) && ! awpcp_array_data( 'id', false, $data ) ) {
            $data['created'] = awpcp_datetime();
        }

        if ( isset( $data[ 'id' ] ) ) {
            $result = $wpdb->update( AWPCP_TABLE_MEDIA, $data, array( 'id' => $data[ 'id' ] ) );
            $result = $result !== false;
        } else {
            $result = $wpdb->insert( AWPCP_TABLE_MEDIA, $data );
            $result = $result === false ? false : $wpdb->insert_id;
        }

        return $result;
    }

    public function delete( $media ) {
        global $wpdb;

        $info = pathinfo( AWPCPUPLOADDIR . "{$media->name}" );
        $filename = preg_replace( "/\.{$info['extension']}/", '', $info['basename'] );

        $filenames = array(
            AWPCPUPLOADDIR . "{$info['basename']}",
            AWPCPUPLOADDIR . "{$filename}-large.{$info['extension']}",
            AWPCPTHUMBSUPLOADDIR . "{$info['basename']}",
            AWPCPTHUMBSUPLOADDIR . "{$filename}-primary.{$info['extension']}",
        );

        foreach ( $filenames as $filename ) {
            if ( file_exists( $filename ) ) {
                @unlink( $filename );
            }
        }

        $query = 'DELETE FROM ' . AWPCP_TABLE_MEDIA . ' WHERE id = %d';
        $result = $wpdb->query( $wpdb->prepare( $query, $media->id ) );

        return $result === false ? false : true;
    }

    public function enable( $media ) {
        $media->enabled = true;
        return $this->save( $media );
    }

    public function disable( $media ) {
        $media->enabled = false;
        return $this->save( $media );
    }

    public function set_ad_primary_image( $ad, $media ) {
        global $wpdb;

        $query = 'UPDATE ' . AWPCP_TABLE_MEDIA . ' SET is_primary = 0 WHERE ad_id = %d';

        if ( $wpdb->query( $wpdb->prepare( $query, $ad->ad_id ) ) === false ) {
            return false;
        }

        $query = 'UPDATE ' . AWPCP_TABLE_MEDIA . ' SET is_primary = 1 WHERE ad_id = %d AND id = %d';
        $query = $wpdb->prepare( $query, $ad->ad_id, $media->id );

        return $wpdb->query( $query ) !== false;
    }

    public function get_ad_primary_image( $ad ) {
        global $wpdb;

        $image_mime_types = awpcp_get_image_mime_types();

        $results = $this->query( array(
            'ad_id' => $ad->ad_id,
            'is_primary' => true,
            'enabled' => true,
            'mime_type' => $image_mime_types,
        ) );

        if ( empty( $results ) ) {
            $results = $this->query( array(
                'ad_id' => $ad->ad_id,
                'enabled' => true,
                'order' => array( 'id ASC' ),
                'mime_type' => $image_mime_types,
            ) );
        }

        return empty( $results ) ? null : AWPCP_Media::create_from_object( $results[0] );
    }

    public function query( $args=array() ) {
        global $wpdb;

        extract( wp_parse_args( $args, array(
            'fields' => '*',
            'id' => false,
            'ad_id' => false,
            'mime_type' => false,
            'enabled' => null,
            'is_primary' => null,
            'order' => array( 'id ASC' ),
        ) ) );

        /*---------------------------------------------------------------------
         * Conditions
         */

        $conditions = array();

        if ( false !== $id ) {
            $conditions[] = $wpdb->prepare( 'id = %d', intval( $id ) );
        }

        if ( false !== $ad_id ) {
            $conditions[] = $wpdb->prepare( 'ad_id = %d', intval( $ad_id ) );
        }

        if ( is_array( $mime_type ) && ! empty( $mime_type ) ) {
            $conditions[] = "mime_type IN ('" . join( "', '", $mime_type ) . "')";
        } else if ( ! empty( $mime_type ) ) {
            $conditions[] = $wpdb->prepare( 'mime_type = IN %s', $mime_type );
        }

        if ( ! is_null( $enabled ) ) {
            $conditions[] = $wpdb->prepare( 'enabled = %d', (bool) $enabled );
        }

        if ( ! is_null( $is_primary ) ) {
            $conditions[] = $wpdb->prepare( 'is_primary = %d', (bool) $is_primary );
        }

        if ( empty( $conditions ) ) {
            $conditions[] = '1 = 1';
        }

        /*---------------------------------------------------------------------
         * Fields, Order
         */

        if ( $fields == 'count' ) {
            $fields = 'COUNT(*)';
        }

        $query = 'SELECT ' . $fields . ' FROM ' . AWPCP_TABLE_MEDIA . ' ';
        $query.= 'WHERE ' . join( ' AND ', $conditions ) . ' ';
        $query.= 'ORDER BY ' . join( ', ', $order );


        if ( $fields == 'COUNT(*)' ) {
            return $wpdb->get_var( $query );
        } else {
            $media = array();
            foreach ( $wpdb->get_results( $query ) as $item ) {
                $media[] = AWPCP_Media::create_from_object( $item );
            }
        }

        return $media;
    }

    public function find_by_id( $id ) {
        $results = self::query( array( 'id' => $id ) );
        return empty( $results ) ? null : array_shift( $results );
    }

    public function find_by_ad_id( $ad_id, $args=array() ) {
        return self::query( array_merge( $args, array( 'ad_id' => $ad_id ) ) );
    }

    public function find_images_by_ad_id( $ad_id, $args=array() ) {
        $mime_types = awpcp_get_image_mime_types();

        return self::query( array_merge( $args, array(
            'ad_id' => $ad_id,
            'mime_type' => $mime_types,
        ) ) );
    }

    public function count_images_by_ad_id( $ad_id ) {
        $mime_types = awpcp_get_image_mime_types();
        return self::query( array( 'fields' => 'count', 'ad_id' => $ad_id, 'mime_type' => $mime_types ) );
    }
}

function awpcp_media_api() {
    return AWPCP_MediaAPI::instance();
}
