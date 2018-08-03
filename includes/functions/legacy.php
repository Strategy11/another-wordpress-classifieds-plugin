<?php
/**
 * @package AWPCP
 */

// phpcs:disable

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
    return count( awpcp_categories_collection()->find_categories() ) > 0;
}

function countcategories(){
    return awpcp_categories_collection()->count_categories();
}

function countcategoriesparents() {
    $all_categories_count = countcategories();
    $childless_categories_count = countcategorieschildren();

    if ( $all_categories_count == $childless_categories_count ) {
        return 0;
    } else {
        return $all_categories_count - $childless_categories_count;
    }
}

function countcategorieschildren() {
    $childless_categories_count = awpcp_categories_collection()->count_categories(array(
        'childless' => true,
    ));

    if ( countcategories() == $childless_categories_count ) {
        return 0;
    } else {
        return $childless_categories_count;
    }
}


function get_adposteremail($adid) {
    try {
        $listing = awpcp_listings_collection()->get( $adid );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }

    return awpcp_listing_renderer()->get_contact_email( $listing );
}

function get_adstartdate($adid) {
    try {
        $listing = awpcp_listings_collection()->get( $adid );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }

    return awpcp_listing_renderer()->get_plain_start_date( $listing );
}

function get_numtimesadviewd($adid) {
    try {
        $listing = awpcp_listings_collection()->get( $adid );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }

    return awpcp_listing_renderer()->get_views_count( $listing );
}

function get_adtitle($adid) {
    try {
        $listing = awpcp_listings_collection()->get( $adid );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }

    return awpcp_listing_renderer()->get_listing_title( $listing );
}

function get_categorynameid( $cat_id = 0, $cat_parent_id = 0, $exclude = array() ) {
    $parent_categories = awpcp_categories_collection()->find_categories( array(
        'fields' => 'id=>name',
        'parent' => 0,
        'exclude' => $exclude,
        'hide_empty' => false,
    ) );

    $params = array(
        'current-value' => $cat_parent_id,
        'options' => $parent_categories
    );

    return awpcp_html_options( $params );
}

// END FUNCTION: create list of top level categories for admin category management

function get_adcatname($cat_ID) {
    try {
        $category = awpcp_categories_collection()->get( $cat_ID );
        $category_name = stripslashes_deep( $category->name );
    } catch( AWPCP_Exception $e ) {
        $category_name = null;
    }

    return $category_name;
}

function get_adparentcatname( $cat_ID ) {
    if ( $cat_ID == 0 ) {
        return __( 'Top Level Category', 'another-wordpress-classifieds-plugin' );
    }

    return get_adcatname( $cat_ID );
}

function get_cat_parent_ID($cat_ID){
    global $wpdb;

    $query = 'SELECT category_parent_id FROM ' . AWPCP_TABLE_CATEGORIES . ' WHERE category_id = %d';
    $query = $wpdb->prepare( $query, $cat_ID );

    return intval( $wpdb->get_var( $query ) );
}

function ads_exist_cat( $catid ) {
    $listings = awpcp_listings_collection()->find_listings(array(
        'tax_query' => array(
            array(
                'taxonomy' => AWPCP_CATEGORY_TAXONOMY,
                'terms' => (int) $catid,
                'include_children' => true,
            ),
        ),
    ));

    return count( $listings ) > 0;
}

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


/**
 * Originally developed by Dan Caragea.  
 * Permission is hereby granted to AWPCP to release this code 
 * under the license terms of GPL2
 * @author Dan Caragea
 * http://datemill.com
 */
function smart_table($array, $table_cols=1, $opentable, $closetable) {
    $usingtable = false;
    if (!empty($opentable) && !empty($closetable)) {
        $usingtable = true;
    }
    return smart_table2($array,$table_cols,$opentable,$closetable,$usingtable);
}


function smart_table2($array, $table_cols=1, $opentable, $closetable, $usingtable) {
    $myreturn="$opentable\n";
    $row=0;
    $total_vals=count($array);
    $i=1;
    $awpcpdisplayaditemclass='';

    foreach ($array as $v) {
            
        if ($i % 2 == 0) { $awpcpdisplayaditemclass = "displayaditemsodd"; } else { $awpcpdisplayaditemclass = "displayaditemseven"; }


        $v=str_replace("\$awpcpdisplayaditems",$awpcpdisplayaditemclass,$v);

        if ((($i-1)%$table_cols)==0)
        {
            if($usingtable)
            {
                $myreturn.="<tr>\n";
            }

            $row++;
        }
        if($usingtable)
        {
            $myreturn.="\t<td valign=\"top\">";
        }
        $myreturn.="$v";
        if($usingtable)
        {
            $myreturn.="</td>\n";
        }
        if ($i%$table_cols==0)
        {
            if($usingtable)
            {
                $myreturn.="</tr>\n";
            }
        }
        $i++;
    }
    $rest=($i-1)%$table_cols;
    if ($rest!=0) {
        $colspan=$table_cols-$rest;
            
        $myreturn.="\t<td".(($colspan==1) ? '' : " colspan=\"$colspan\"")."></td>\n</tr>\n";
    }
    //}
    $myreturn.="$closetable\n";
    return $myreturn;
}

function create_awpcp_random_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (int)$sec+(int)($usec*100000);
}

if (!function_exists('addslashes_mq')) {
    function addslashes_mq($value) {
        if (is_array($value)) {
            $myreturn=array();
            while (list($k,$v)=each($value)) {
                $myreturn[addslashes_mq($k)]=addslashes_mq($v);
            }
        } else {
            if(get_magic_quotes_gpc() == 0) {
                $myreturn=addslashes($value);
            } else {
                $myreturn=$value;
            }
        }
        return $myreturn;
    }
}

/**
 * TODO: replace usage of this function with awpcp_pagination()
 */
function create_pager( $items_count, $offset, $results, $tpname ) {
    $permastruc=get_option('permalink_structure');

    if (isset($permastruc) && !empty($permastruc)) {
        $awpcpoffset_set="?offset=";
    } else {
        if(is_admin()) {
            $awpcpoffset_set="?offset=";
        } else {
            $awpcpoffset_set="&offset=";
        }
    }

    mt_srand(create_awpcp_random_seed());
    $radius=5;

    global $accepted_results_per_page;
    $accepted_results_per_page = awpcp_pagination_options( $results );

    // TODO: remove all fields that belongs to the Edit Ad form (including extra fields and others?)
    $params = array_merge($_GET,$_POST);

    unset($params['page_id'], $params['offset'], $params['results']);
    unset($params['PHPSESSID'], $params['aeaction'], $params['category_id']);
    unset($params['cat_ID'], $params['action'], $params['aeaction']);
    unset($params['category_name'], $params['category_parent_id']);
    unset($params['createeditadcategory'], $params['deletemultiplecategories']);
    unset($params['movedeleteads'], $params['moveadstocategory']);
    unset($params['category_to_delete'], $params['tpname']);
    unset($params['category_icon'], $params['sortby'], $params['adid']);
    unset($params['picid'], $params['adkey'], $params['editemail']);
    unset($params['awpcp_ads_to_action'], $params['post_type']);

    $cid = intval(awpcp_request_param('category_id'));
    $cid = empty($cid) ? get_query_var('cid') : $cid;

    if ($cid > 0) {
        $params['category_id'] = intval( $cid );
    }

    $myrand=mt_rand(1000,2000);
    $form="<form id=\"pagerform$myrand\" name=\"pagerform$myrand\" action=\"\" method=\"get\">\n";
    $form.="<table>\n";
    $form.="<tr>\n";
    $form.="\t<td>\n";

    $totalrows = $items_count;
    $total_pages=ceil($totalrows/$results);
    $dotsbefore=false;
    $dotsafter=false;
    $current_page = 0;
    $myreturn = '';

    for ($i=1;$i<=$total_pages;$i++) {
        if (((($i-1)*$results)<=$offset) && ($offset<$i*$results)) {
            $myreturn.="$i&nbsp;";
            $current_page = $i; 
        } elseif (($i-1+$radius)*$results<$offset) {
            if (!$dotsbefore) {
                $myreturn.="...";
                $dotsbefore=true;
            }
        } elseif (($i-1-$radius)*$results>$offset) {
            if (!$dotsafter) {
                $myreturn.="...";
                $dotsafter=true;
            }
        } else {
            $href_params = array_merge($params, array('offset' => ($i-1) * $results, 'results' => $results));
            $href = add_query_arg( urlencode_deep( $href_params ), $tpname );
            $myreturn.= sprintf( '<a href="%s">%d</a>&nbsp;', esc_url( $href ), esc_attr( $i ) );
        }
    }

    if ( $offset != 0 ) {
        //Subtract 2, page is 1-based index, results is 0-based, must compensate for 2 pages here
        if ( (($current_page-2) * $results) < $results) {
            $href_params = array_merge($params, array('offset' => 0, 'results' => $results));
            $href = add_query_arg( urlencode_deep( $href_params ), $tpname );
        } else {
            $href_params = array_merge($params, array('offset' => ($current_page-2) * $results, 'results' => $results));
            $href = add_query_arg( urlencode_deep( $href_params ), $tpname );
        }
        $prev = sprintf( '<a href="%s">&laquo;</a>&nbsp;', esc_url( $href ) );
    } else {
        $prev = '';
    }

    if ( $offset != (($total_pages-1)*$results) ) {
        $href_params = array_merge($params, array('offset' => $current_page * $results, 'results' => $results));
        $href = add_query_arg( urlencode_deep( $href_params ), $tpname );
        $next = sprintf( '<a href="%s">&raquo;</a>&nbsp;', esc_url( $href ) );
    } else {
        $next = '';
    }

    if ( isset( $_REQUEST['page_id'] ) && !empty( $_REQUEST['page_id'] ) ) {
        $form.="\t\t<input type=\"hidden\" name=\"page_id\" value='" . esc_attr( $_REQUEST['page_id'] ) ."' />\n";
    }

    $form = $form . $prev . $myreturn . $next;
    $form.="\t</td>\n";

    if ( count( $accepted_results_per_page ) > 1 ) {
        $form.="\t<td>\n";
        $form.="\t\t<input type=\"hidden\" name=\"offset\" value=\"$offset\" />\n";

        $flat_params = awpcp_flatten_array( $params );

        foreach ( $flat_params as $k => $v ) {
            if ( is_array( $v ) ) {
                $v = count( $v ) > 0 ? reset( $v ) : '';
            }
            $form.= "\t\t<input type=\"hidden\" name=\"" . esc_attr($k) . "\" value=\"" . esc_attr($v) . "\" />\n";
        }

        $form.="\t\t<select name=\"results\" onchange=\"document.pagerform$myrand.submit()\">\n";
        $form.=vector2options($accepted_results_per_page,$results);
        $form.="\t\t</select>\n";
        $form.="\t</td>\n";
    }

    $form.="</tr>\n";
    $form.="</table>\n";
    $form.="</form>\n";
    return $form;
}

function vector2options($show_vector,$selected_map_val,$exclusion_vector=array()) {
   $myreturn='';

   foreach( $show_vector as $k => $v ) {
       if (!in_array($k,$exclusion_vector)) {
           $myreturn.="<option value=\"".$k."\"";
           if ($k==$selected_map_val) {
               $myreturn.=" selected='selected'";
           }
           $myreturn.=">".$v."</option>\n";
       }
   }
   return $myreturn;
}

function unix2dos($mystring) {
    $mystring=preg_replace("/\r/m",'',$mystring);
    $mystring=preg_replace("/\n/m","\r\n",$mystring);
    return $mystring;
}
