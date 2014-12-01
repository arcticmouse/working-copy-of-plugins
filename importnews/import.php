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
* the curl function to get the contents
*************************************************************/
function get_file_contents($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);

	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}




/*************************************************************
* import contents of csv into WP
*************************************************************/
function newsimport() {

	$data = get_file_contents( plugins_url( 'test.csv', __FILE__ ) );
	#$array_multi = array_map( "str_getcsv", preg_split('/\r*\n+|\r+/', $data) );
	$array_multi = array_map( "str_getcsv", preg_split('/\n|\r/', $data) );
	#echo '<pre>';
	#print_r($array_multi);
	#echo '</pre>';

	/***************************************
	* import fields
	***************************************/
	#easier to remember names than numbers
	$title = 2;  //just need the title
	$news = 3;	   //news as content
	$flag = 4;	//flags as tags
	$cat = 5;
	$dept = 6;
	$keywords = 7;
	$format = 8;
	$date = 1;

	foreach( $array_multi as $arr){

		$cleared_title = clearUTF( $arr[$title] );

		if( false == get_page_by_title( $cleared_title, 'OBJECT', 'story') ){
			/***************************************
			* import format for post date
			***************************************/
			$news_date = date( 'Y-m-d H:i:s', strtotime( $arr[$date] ) );
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
				//$new_flag_term = get_term_by( 'name', $f, 'flag' );
				//$new_flag_list[] = $new_flag_term->term_id;
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
			$haystack = clearUTF( $arr[$news] );
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
				$content = clearUTF( $haystack );
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

		} //end if inserting the post

		//unset arrays
		unset($new_flag_list);
		unset($new_cat_list);
		unset($new_tag_list);
		unset($image);

	} //end for loop
	
}

add_filter( 'wp_loaded', 'newsimport' );
 
?>