<?php

/*
	Plugin Name: kw_import_csv_test
	Plugin URI: 
	Description: Kai-Lea import csv to wp-post test
	Version: 1.5
	Author: Kai-Lea
	Author URI: 
	License: 
*/
ob_start();
require_once( ABSPATH . 'wp-admin/includes/image.php' );


function get_file_contents($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);

	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}

function newsimport() {

	echo "blargh <br>";
	$arr = get_file_contents( plugins_url( 'test.csv', __FILE__ ) );
	$data = array_map( "str_getcsv", preg_split('/\r*\n+|\r+/', $arr) );
	echo gettype($arr);
	print_r($arr);

	/***************************************
	* import excel
	***************************************
	ini_set('auto_detect_line_endings', true);  //restrict to read first line


	
	$arr = array(array());  //2D array  , [num][colum]. ex: arr[0]["id"]
	$row = 0; //for count how many row we have go through
	$handle = fopen( plugins_url( 'test.csv', __FILE__ ), "r");

	if($handle !== FALSE) {
		//array itself for get the colum name
		$name = fgetcsv($handle, 1000, ",");
		$num = count($name); //get the # of element from the fist line of csv file
		
		//process the csv file and store into 2D array
		while($data = fgetcsv($handle, 1000, ",")){   

            for ($j=0; $j < $num; $j++){
            	$arr[$row][$name[$j]] = $data[$j];
            	
            }
	    	$row++;
		}
	}	
	fclose($handle);


	/***************************************
	* import fields
	***************************************/
	$title = 'title';  //just need the title
	$news = 'news';	   //news as content
	$flag = 'flag (Patient care, research, education, community)';	//flags as tags
	$cat = 'category (school name or research)';
	$dept = 'Department';
	$keywords = 'Keywords';
	$format = 'Multimedia type';
	$date = 'newsdate';

	for($k = 0; $k < $row; $k++){

		if( false == get_page_by_title( $arr[$k][$title], 'OBJECT', 'story') ){
			/***************************************
			* import flags as a wp flag
			***************************************/
			$blob = get_terms( 'flag', array( 'hide_empty' => 0 ) );
			$f_list = explode( ", ", $arr[$k][$flag] );
			foreach( $f_list as $f ){
				$f = ucfirst( $f );
				$new_flag_term = get_term_by( 'name', $f, 'flag' );

				$new_flag_list[] = $new_flag_term->term_id;
			} //end foreach

			/***************************************
			* import cateogry & department as a wp category
			***************************************/
			$fluffy = get_terms( 'category', array( 'hide_empty' => 0 ) );
			$cat_list = explode( ", ", $arr[$k][$cat] );
			$dept_list = explode( ", ", $arr[$k][$dept] );
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
			$key_list = explode( ", ", $arr[$k][$keywords] );
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
			$multimedia = $arr[$k][$format];
			if ($multimedia == 'Photo'){
				$multimedia = 'image';
			}

			/***************************************
			* import format for post image
			***************************************/
			$haystack = $arr[$k][$news];
			$needle = '<img [^>]*>';
			
			preg_match( '/<img [^>]*>/', $haystack, $image );

			if ($image[0]){
				$content = str_replace( $image[0], '' , $haystack );

				preg_match( '/src="[^"]*"/', $image[0], $src_arr );
				$img_src = $src_arr[0];

				preg_match( '/[^\/]*$/', $img_src, $file_arr );
				$filename = rtrim( $file_arr[0], '"' );
				$n_filename = str_replace( '%20', ' ', $filename );
				

				$filepath = 'https://qa.untmed.org/http_docs/wp-content/uploads/sites/17/2014/10' . $n_filename;

				preg_match( '/alt="[^"]*"/', $image[0], $alt_arr );
				$img_alt = substr( $alt_arr[0], 5, -1 );
			} else {
				$content = $haystack;
			}

			/***************************************
			* import format for post date
			***************************************/

			$news_date = date( 'Y-m-d H:i:s', strtotime( $arr[$k][$date] ) );


			/***************************************
			* insert data into the post
			***************************************/
			$post_id = wp_insert_post(
				array(
					'post_title' => $arr[$k][$title],
					'post_type' => 'story',
					'post_status' => 'publish',	
				)
			); //end insert post

			//upload the images and put them as post thumbnail
			$filetype = wp_check_filetype( basename( $filename ), null );
			$wp_upload_dir = wp_upload_dir();
			$attachment = array(
				'post_mime_type' => $filetype['type'],
				'post_title' => $arr[$k][$title],
				'post_content' => '',
				'post_status' => 'inherit'
				);
			$thumb_id = wp_insert_attachment( $attachment, $filepath, $post_id );
			$thumb_data = wp_generate_attachment_metadata( $thumb_id, $filepath );
			wp_update_attachment_metadata( $thumb_id, $thumb_data );
			set_post_thumbnail( $post_id, $thumb_id );


			wp_set_post_terms( $post_id, $new_flag_list, 'flag' );
			wp_set_post_categories( $post_id, $new_cat_list );
			wp_set_post_tags( $post_id, $new_tag_list );
			wp_update_post( array( 'ID' => $post_id, 'post_content' => $content, 'post_date' => $news_date ) );

			set_post_format( $post_id, $multimedia );
		} //end if inserting the post
		unset($new_flag_list);
		unset($new_cat_list);
		unset($new_tag_list);
		unset($image);

	} //end for loop
	
}

add_filter( 'wp_loaded', 'newsimport' );
 
?>