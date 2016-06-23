<?php

class AWPCP_ListingsTableSearchByPhoneCondition {

    public function match( $search_by ) {
        return $search_by == 'phone';
    }

    public function create( $search_term, $query ) {
        $query['meta_query'][] = array(
            'key' => '_awpcp_contact_phone_number_digits',
            'value' => $search_term,
            'compare' => 'LIKE',
        );

        return $query;
    }
}
