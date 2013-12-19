<?php

class AWPCP_ListingsTableSearchByUserCondition {

    public function match( $search_by ) {
        return $search_by == 'user';
    }

    public function create( $search_term ) {
        global $wpdb;

        $sql = "SELECT DISTINCT ID FROM wp_users ";
        $sql.= "LEFT JOIN wp_usermeta ON (ID = user_id) ";
        $sql.= 'WHERE (user_login LIKE \'%%%1$s%%\') OR ';
        $sql.= '(meta_key = \'last_name\' AND meta_value LIKE \'%%%1$s%%\') ';
        $sql.= 'OR (meta_key = \'first_name\' AND meta_value LIKE \'%%%1$s%%\')';

        $users = $wpdb->get_col( $wpdb->prepare( $sql, $search_term ) );

        if ( empty( $users ) ) {
            throw new Exception( sprintf( 'No users found for "%s"', $search_term ) );
        }

        return 'user_id IN (' . join( ',', $users ) . ')';
    }
}
