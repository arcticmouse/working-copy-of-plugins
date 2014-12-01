<?php

/*
	Plugin Name: import
	Plugin URI: 
	Description: csv to wp-post
	Version: 4
	Author: leta
	Author URI: 
	License: 
*/



ob_start();
setlocale( LC_ALL, 'en_US.UTF-8' );

require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');


/*************************************************************
* http://php.net/manual/en/function.iconv.php#83238
* using for titles but not for content
*************************************************************/
function clearUTF( $s )
{
    $r = '';
    $s1 = iconv( 'UTF-8', 'ASCII//TRANSLIT', utf8_encode( $s ) );
    $j = 0;
    for ( $i = 0; $i < strlen( $s1 ); $i++ ) {
        $ch1 = $s1[$i];
        $ch2 = @mb_substr($s, $j++, 1, 'UTF-8');
        if (strstr( '`^~\'"', $ch1 ) !== false) {
            if ( $ch1 <> $ch2 ) {
                --$j;
                continue;
            }
        }
        $r .= ($ch1=='?') ? $ch2 : $ch1;
    }
    return $r;
}




/*************************************************************
* put google spreadsheet data into an array
* input: string of data in HTML table format
* returns: array
*************************************************************/
function getGoogleData( $a ){
	$dom = new domDocument;
	$dom->loadHTML( $a );
	$rows = $dom->getElementsByTagName('tr');
	$count = 1;
	$new_arr = array();
	$titles = array();
	foreach( $rows as $row ){
		$cells = $row->getElementsByTagName('td');
		$col = 0;
		$obj = "";
		foreach( $cells as $cell ){
			if( strlen( trim( $cell->nodeValue ) ) ){
				if( $count == 2 ){
					$titles[] = $cell->nodeValue;
				} else {
					$thisCol = $titles[$col];
					$obj[$thisCol] = $cell->nodeValue;
				} //end if else
			} //end if strlen
			$col++;
		} //end foreach cell
		if( $count <> 1 ){
			$ary[] = $obj;
		} //end if count
		$count++;
	} //end foreach row
	return $ary;
}





/*************************************************************
* the curl function to get the contents
*************************************************************/
function get_file_contents( $url ){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);

	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}




/*************************************************************
* import contents of data into WP
*************************************************************/
function newsimport() {

	#to get from csv
	#$data = get_file_contents( plugins_url( 'test.csv', __FILE__ ) );
	#$array_multi = array_map( "str_getcsv", preg_split('/\n|\r/', $data) );

	#to get from google doc
	//test sheet
	$data = get_file_contents( "https://docs.google.com/spreadsheets/d/14P-2SvlkvQJxcmkopZBWB-iSBECfpv5SsB7rIawSvbI/pubhtml?gid=913094666&single=true" );
	//real sheet
	#$data = get_file_contents( "https://docs.google.com/spreadsheets/d/14QIEsldohbQIOGzsCqKGRXvAw4EKTeT-7iRY43naHOk/pubhtml?gid=913094666&single=true" );
	$array_multi = getGoogleData( $data );


	/***************************************
	* import fields
	***************************************/
	#use these numbers for csv import
	/*easier to remember names than numbers
	$title = 2;  
	$news = 3;	 #news as content
	$flag = 4;	 #flags taxonomy as tags 
	$cat = 5;	 #cat taxonomy = cat + dept
	$dept = 6;	 #cat taxonomy = cat + dept
	$keywords = 7; #non taxonomy
	$format = 8;	#taxonomy
	$date = 1;	 	#have to encode later on*/

	#use these for google docs import
	$id = 'id';
	$title = 'title';  
	$news = 'news';	 #news as content
	$flag = 'flag (Patient care, research, education, community)';	 #flags taxonomy as tags 
	$cat = 'category (school name or research)';	 #cat taxonomy = cat + dept
	$dept = 'Department';	 #cat taxonomy = cat + dept
	$keywords = 'Keywords (required: faculty, staff, students. '; #non taxonomy
	$format = 'Multimedia type';	#taxonomy
	$date = 'newsdate';	 	#have to encode later on*/

	$id_arr = array();

	foreach( $array_multi as $arr ){
		$cleared_title = clearUTF( $arr[$title] );
		$n_id = $arr[$id];
		$news_date = date( 'Y-m-d H:i:s', strtotime( $arr[$date] ) );
		$exist_id = get_page_by_title( $cleared_title, 'OBJECT', 'story');
		$exist_date = get_the_date( 'Y-m-d H:i:s', $exist_id );


		#if ( false == get_page_by_title( $cleared_title, 'OBJECT', 'story') ) {
		#if ( !in_array( $n_id, $id_arr ) && false == get_page_by_title( $cleared_title, 'OBJECT', 'story') && $exist_id != $news_date ){
		if ( !in_array( $n_id, $id_arr ) ) {
			if ( ( false == get_page_by_title( $cleared_title, 'OBJECT', 'story') ) && ( $exist_id != $news_date ) ) {

			/***************************************
			* import format for post date
			***************************************/
			$past_date = date( 'Y-m-d', strtotime( '2013/07/01' ) );

			//if news date earlier than past date (eg, date Pres Williams started being pres) than category is archive
			if( $news_date < $past_date ){
				$arr[$cat] = 'Archive';
			} 

			/***************************************
			* import flags as a wp flag
			***************************************/
			$f_list = explode( ", ", $arr[$flag] );
			foreach( $f_list as $f ){
				$f = ucfirst( $f );
				$new_flag_list[] = $f;
			} //end foreach

			/***************************************
			* import cateogry & department as a wp category
			***************************************/
			$fluffy = get_terms( 'category', array( 'hide_empty' => 0 ) );
			$cat_list = explode( ", ", $arr[$cat] );
			$dept_list = explode( ", ", $arr[$dept] );
			$categories = array_merge( $cat_list, $dept_list );
			foreach( $categories as $c ){
				if ( !in_array( $c, $fluffy ) ){				
					wp_insert_term( $c, 'category' );
				} 
				$new_cat_term = get_term_by( 'name', $c, 'category' );
				
				$new_cat_list[] = $new_cat_term->term_id;
			} //end for	

			/***************************************
			* import keywords as wp tags
			***************************************/
			$bouncey = get_terms( 'post_tag', array( 'hide_empty' => 0 ) );
			$key_list = explode( ", ", $arr[$keywords] );
			foreach( $key_list as $akey ){
				if ( !in_array( $akey, $bouncey )){
					wp_insert_term( $akey, 'post_tag');
				} //end if
				$new_tag_term = get_term_by( 'name', $akey, 'post_tag' );

				$new_tag_list[] = $new_tag_term->term_id;
			} //end for

			/***************************************
			* import format for wp post format
			***************************************/
			$multimedia = $arr[$format];
			if ($multimedia == 'Photo'){
				$multimedia = 'image';
			}

			/***************************************
			* import format for post image
			***************************************/
			$haystack = $arr[$news];
			$needle = '<img [^>]*>';
			
			preg_match( '/<img [^>]*>/', $haystack, $image );

			if ($image[0]){
				$content = str_replace( $image[0], '' , $haystack );

				preg_match( '/src="[^"]*"/', $image[0], $src_arr );
				$img_src = $src_arr[0];

				preg_match( '/[^\/]*$/', $img_src, $file_arr );
				$filename = rtrim( $file_arr[0], '"' );
				$n_filename = str_replace( '%20', ' ', $filename );

				preg_match( '/alt="[^"]*"/', $image[0], $alt_arr );
				$img_alt = substr( $alt_arr[0], 5, -1 );
			} else {
				$content = $haystack;
			} 


			/***************************************
			* insert data into the post
			***************************************/
			$post_id = wp_insert_post(
				array(
					'post_title' => $cleared_title,
					'post_type' => 'story',
					'post_status' => 'publish',	
				)
			); //end insert post


			if ( $n_filename ){
				//set image path
				$filepath = plugins_url() . '/importnews/' . basename( $n_filename );
				//get image filetype
				$filetype = wp_check_filetype( basename( $n_filename ), null );
				//get upload directory
				$wp_upload_dir = wp_upload_dir();
				//metadata
				$attachment = array(
					'post_mime_type' => $filetype['type'],
					'post_title' 	 => $n_filename,
					'post_content'   => '',
					'post_status'    => 'inherit',
					);
				//load image
				$image = media_sideload_image( $filepath, $post_id, $img_alt );
				//get attachments for post (should jsut be our one image but just in case.... we loop)
				$attachments = get_children( $post_id );
				foreach( $attachments as $attachment_id => $attachment );
				//set image as the post thumbnail
				set_post_thumbnail( $post_id, $attachment_id );
				//gtfo of dodge!
			} //end if post image/thumbnail exists

			//set post flag, categories, tags, content, multimedia/format
			wp_set_post_terms( $post_id, $new_flag_list, 'flag' );
			wp_set_post_categories( $post_id, $new_cat_list );
			wp_set_post_tags( $post_id, $new_tag_list );
			wp_update_post( array( 'ID' => $post_id, 'post_content' => $content, 'post_date' => $news_date ) );
			set_post_format( $post_id, $multimedia );

			$id_arr[] = $n_id;


			}
		} else echo $cleared_title . ' ';

		//unset arrays
		unset($new_flag_list);
		unset($new_cat_list);
		unset($new_tag_list);
		unset($image);
	} //end for loop

}

add_filter( 'wp_loaded', 'newsimport' );
 
?>