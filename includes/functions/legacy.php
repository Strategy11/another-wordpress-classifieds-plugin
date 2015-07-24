<?php

function get_awpcp_setting($column, $option) {
    global $wpdb;
    $tbl_ad_settings = $wpdb->prefix . "awpcp_adsettings";
    $myreturn=0;
    $tableexists=checkfortable($tbl_ad_settings);

    if($tableexists)
    {
        $query="SELECT ".$column." FROM  ".$tbl_ad_settings." WHERE config_option='$option'";
        $res = $wpdb->get_var($query);
        $myreturn = stripslashes_deep($res);
    }
    return $myreturn;
}

function get_awpcp_option_group_id($option) {
    return get_awpcp_setting('config_group_id', $option);
}

function get_awpcp_option_type($option) {
    return get_awpcp_setting('option_type', $option);
}

function get_awpcp_option_config_diz($option) {
    return get_awpcp_setting('config_diz', $option);
}


function checkifisadmin() {
    return awpcp_current_user_is_admin() ? 1 : 0;
}

function awpcpistableempty($table){
    global $wpdb;

    $query = 'SELECT COUNT(*) FROM ' . $table;
    $results = $wpdb->get_var( $query );

    if ( $results !== false && intval( $results ) === 0 ) {
        return true;
    } else {
        return false;
    }
}

function awpcpisqueryempty($table, $where){
    global $wpdb;

    $query = 'SELECT COUNT(*) FROM ' . $table . ' ' . $where;
    $count = $wpdb->get_var( $query );

    if ( $count !== false && intval( $count ) === 0 ) {
        return true;
    } else {
        return false;
    }
}

function adtermsset(){
    global $wpdb;
    $myreturn = !awpcpistableempty(AWPCP_TABLE_ADFEES);
    return $myreturn;
}

function categoriesexist(){

    global $wpdb;
    $tbl_categories = $wpdb->prefix . "awpcp_categories";

    $myreturn=!awpcpistableempty($tbl_categories);
    return $myreturn;
}

function countlistings($is_active) {
    global $wpdb;

    $query = 'SELECT COUNT(*) FROM ' . AWPCP_TABLE_ADS . ' WHERE disabled = %d';
    $query = $wpdb->prepare( $query, $is_active ? false : true );

    return $wpdb->get_var( $query );
}

function countcategories(){
    return AWPCP_Category::query( array( 'fields' => 'count' ) );
}

function countcategoriesparents() {
    $params = array(
        'fields' => 'count',
        'where' => 'category_parent_id = 0'
    );

    return AWPCP_Category::query( $params );
}

function countcategorieschildren(){
    $params = array(
        'fields' => 'count',
        'where' => 'category_parent_id != 0'
    );

    return AWPCP_Category::query( $params );
}


function get_adposteremail($adid) {
    return get_adfield_by_pk('ad_contact_email', $adid);
}

function get_adstartdate($adid) {
    return get_adfield_by_pk('ad_startdate', $adid);
}

// START FUNCTION: Get the number of times an ad has been viewed
function get_numtimesadviewd($adid)
{
    return get_adfield_by_pk('ad_views', $adid);
}
// END FUNCTION: Get the number of times an ad has been viewed
// START FUNCTION: Get the ad title based on having the ad ID
function get_adtitle($adid) {
    return stripslashes_deep(get_adfield_by_pk('ad_title', $adid));
}

// START FUNCTION: Create list of top level categories for admin category management
function get_categorynameid($cat_id = 0,$cat_parent_id= 0,$exclude)
{

    global $wpdb;
    $optionitem='';
    $tbl_categories = $wpdb->prefix . "awpcp_categories";

    if(isset($exclude) && !empty($exclude))
    {
        $excludequery="AND category_id !='$exclude'";
    }else{$excludequery='';}

    $catnid=$wpdb->get_results("select category_id as cat_ID, category_parent_id as cat_parent_ID, category_name as cat_name from " . AWPCP_TABLE_CATEGORIES . " WHERE category_parent_id=0 AND category_name <> '' $excludequery");

    foreach($catnid as $categories)
    {

        if($categories->cat_ID == $cat_parent_id)
        {
            $optionitem .= "<option selected='selected' value='$categories->cat_ID'>$categories->cat_name</option>";
        }
        else
        {
            $optionitem .= "<option value='$categories->cat_ID'>$categories->cat_name</option>";
        }

    }

    return $optionitem;
}
// END FUNCTION: create list of top level categories for admin category management

// START FUNCTION: Retrieve the category name
function get_adcatname($cat_ID) {
    try {
        $category = awpcp_categories_collection()->get( $cat_ID );
        $category_name = stripslashes_deep( $category->name );
    } catch( AWPCP_Exception $e ) {
        $category_name = '';
    }

    return $category_name;
}

//Function to retrieve ad location data:
function get_adfield_by_pk($field, $adid) {
    global $wpdb;
    $tbl_ads = $wpdb->prefix . "awpcp_ads";
    $thevalue='';
    if(isset($adid) && (!empty($adid))){
        $query="SELECT ".$field." from ".$tbl_ads." WHERE ad_id='$adid'";
        $thevalue = $wpdb->get_var($query);
    }
    return $thevalue;
}

function get_adparentcatname($cat_ID){
    global $wpdb;

    if ( $cat_ID == 0 ) {
        return __( 'Top Level Category', 'AWPCP' );
    }

    $query = 'SELECT category_name FROM ' . AWPCP_TABLE_CATEGORIES . ' WHERE category_id = %d';
    $query = $wpdb->prepare( $query, $cat_ID );

    return $wpdb->get_var( $query );
}

function get_cat_parent_ID($cat_ID){
    global $wpdb;

    $query = 'SELECT category_parent_id FROM ' . AWPCP_TABLE_CATEGORIES . ' WHERE category_id = %d';
    $query = $wpdb->prepare( $query, $cat_ID );

    return $wpdb->get_var( $query );
}

function ads_exist() {
    global $wpdb;
    $tbl_ads = $wpdb->prefix . "awpcp_ads";
    $myreturn=!awpcpistableempty($tbl_ads);
    return $myreturn;
}
// END FUNCTION: check if any ads exist in the system
// START FUNCTION: Check if there are any ads in a specified category
function ads_exist_cat($catid) {
    global $wpdb;
    $tbl_ads = $wpdb->prefix . "awpcp_ads";
    $myreturn=!awpcpisqueryempty($tbl_ads, " WHERE ad_category_id='$catid' OR ad_category_parent_id='$catid'");
    return $myreturn;
}
// END FUNCTION: check if a category has ads
function category_has_children($catid) {
    global $wpdb;
    $tbl_categories = $wpdb->prefix . "awpcp_categories";
    $myreturn=!awpcpisqueryempty($tbl_categories, " WHERE category_parent_id='$catid'");
    return $myreturn;
}

function category_is_child($catid) {
    global $wpdb;

    $query = 'SELECT category_parent_id FROM ' . AWPCP_TABLE_CATEGORIES . ' WHERE category_id = %d';
    $query = $wpdb->prepare( $query, $catid );

    $parent_id = $wpdb->get_var( $query );

    if ( $parent_id !== false && $parent_id != 0 ) {
        return true;
    } else {
        return false;
    }
}

function add_config_group_id($cvalue,$coption) {
    global $wpdb;

    $query = 'UPDATE ' . AWPCP_TABLE_ADSETTINGS . ' SET config_group_id = %d WHERE config_option = %s';
    $query = $wpdb->prepare( $query, $cvalue, $coption );

    $wpdb->query( $query );
}

function field_exists($field) {
    global $wpdb;

    if ( ! checkfortable( AWPCP_TABLE_ADSETTINGS ) ) {
        return false;
    }

    $query = 'SELECT config_value FROM ' . AWPCP_TABLE_ADSETTINGS . ' WHERE config_option = %s';
    $query = $wpdb->prepare( $query, $field );

    $value = $wpdb->get_var( $config_value );

    if ( $value === false || is_null( $value ) ) {
        return false;
    } else {
        return true;
    }
}
