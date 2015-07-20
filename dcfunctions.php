<?php

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
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
function create_pager($from,$where,$offset,$results,$tpname) {
	global $wpdb;

	$totalrows = $wpdb->get_var( "SELECT count(*) FROM $from WHERE $where" );

	return _create_pager( $totalrows, $offset, $results, $tpname );
}

/**
 * TODO: replace usage of this function with awpcp_pagination()
 */
function _create_pager( $item_count, $offset, $results, $tpname ) {
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

	$totalrows = $item_count;
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
		while ( list( $k, $v ) = each( $flat_params ) ) {
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

/**
 * @since 3.2.1
 */
function awpcp_pagination_options( $selected=10 ) {
	$options = get_awpcp_option( 'pagination-options' );
	return awpcp_build_pagination_options( $options, $selected );
}

/**
 * @since 3.3.2
 */
function awpcp_build_pagination_options( $options, $selected ) {
	array_unshift( $options, 0 );

	for ( $i = count( $options ) - 1; $i >= 0; $i-- ) {
		if ( $options[ $i ] < $selected ) {
			array_splice( $options, $i + 1, 0, $selected );
			break;
		}
	}

	$options_without_zero = array_filter( $options, 'intval' );

	return array_combine( $options_without_zero , $options_without_zero );
}

/**
 * @since 3.3.2
 */
function awpcp_default_pagination_options( $selected = 10 ) {
	$default_options = awpcp()->settings->get_option_default_value( 'pagination-options' );
	return awpcp_build_pagination_options( $default_options, $selected );
}

function unix2dos($mystring) {
	$mystring=preg_replace("/\r/m",'',$mystring);
	$mystring=preg_replace("/\n/m","\r\n",$mystring);
	return $mystring;
}

/**
 * TODO: move to AWPCP_Email?
 */
function awpcp_send_email($from,$to,$subject,$message, $html=false, $attachments=array(), $bcc='') {
	$separator='Next.Part.331925654896717'.time();
	$att_separator='NextPart.is_a_file9817298743'.time();
	$headers="From: $from\n";
	$headers.="MIME-Version: 1.0\n";
	if (!empty($bcc)) {
		$headers.="Bcc: $bcc\n";
	}
	$text_header="Content-Type: text/plain; charset=\"iso-8859-1\"\nContent-Transfer-Encoding: 8bit\n\n";
	$html_header="Content-Type: text/html; charset=\"iso-8859-1\"\nContent-Transfer-Encoding: 8bit\n\n";
	$html_message=$message;
	$text_message=$message;
	$text_message=str_replace('&nbsp;',' ',$text_message);
	$text_message=trim(strip_tags(stripslashes($text_message)));
	// Bring down number of empty lines to 2 max
	$text_message=preg_replace("/\n[\s]+\n/","\n",$text_message);
	$text_message=preg_replace("/[\n]{3,}/", "\n\n",$text_message);
	$text_message=wordwrap($text_message,72);
	$message="\n\n--$separator\n".$text_header.$text_message;

	if ($html) {
		$message.="\n\n--$separator\n".$html_header.$html_message;
	}

	$message.="\n\n--$separator--\n";

	if (!empty($attachments)) {
		$headers.="Content-Type: multipart/mixed; boundary=\"$att_separator\";\n";
		$message="\n\n--$att_separator\nContent-Type: multipart/alternative; boundary=\"$separator\";\n".$message;
		while (list(,$file)=each($attachments)) {
			$message.="\n\n--$att_separator\n";
			$message.="Content-Type: application/octet-stream; name=\"".basename($file)."\"\n";
			$message.="Content-Transfer-Encoding: base64\n";
			$message.='Content-Disposition: attachment; filename="'.basename($file)."\"\n\n";
			$message.=wordwrap(base64_encode(fread(fopen($file,'rb'),filesize($file))),72,"\n",1);
		}
		$message.="\n\n--$att_separator--\n";
	} else {
		$headers.="Content-Type: multipart/alternative;\n\tboundary=\"$separator\";\n";
	}
	$message='This is a multi-part message in MIME format.'.$message;
	if (isset($_SERVER['WINDIR']) || isset($_SERVER['windir']) || isset($_ENV['WINDIR']) || isset($_ENV['windir'])) {
		$message=unix2dos($message);
	}
	//	$headers=unix2dos($headers);
	$sentok=@mail($to,$subject,$message,$headers,"-f$from");
	return $sentok;
}
