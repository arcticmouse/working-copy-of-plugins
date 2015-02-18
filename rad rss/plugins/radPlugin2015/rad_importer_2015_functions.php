<?php
//get feed from rss
function get_import_rad_xml_contents( $data ) {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_URL, $data );
	$imported_data = curl_exec( $ch );
	curl_close( $ch );

	return $imported_data;
} //end get_import_bios_xml_contents function





function get_rad_rss_callback(){
	$feed = get_import_rad_xml_contents( 'http://digitalcommons.hsc.unt.edu/rad/rad_feed.rss' );
	$xml = simplexml_load_string( $feed, null, LIBXML_NOCDATA );
	$json = json_encode( $xml );

	$file = dirname( plugin_dir_path( __FILE__ ) ) . '/radPlugin2015/digitalCommonsFeed.json';
	//keep the old one to check for removed employees
	if ( file_exists($file ) ) {
		rename( $file, dirname( plugin_dir_path( __FILE__ ) ) . '/radPlugin2015/digitalCommonsFeedOLD.json' );
	}

	file_put_contents( $file, $json );
} //end callback





//get json files contents in xml format
function get_rad_json_file(){
	$json = null;
	$iterate = 0;

	$file = dirname( plugin_dir_path( __FILE__ ) ) . '/radPlugin2015/digitalCommonsFeed.json';
	$json = file_get_contents( $file );
	$decoded = json_decode( $json, true );
	foreach( $decoded['channel']['item'] as $d ){
		$big_arr[] = $d;
	} //end foreach
	
	return $big_arr;
} //end function get_json_file





function is_in_rad_category( $cat ){
	$all_cats = get_terms( 'category', array( 'hide_empty' => false ) );

	foreach( $cat as $c ){
		if ( !in_array( $c, $all_cats ) ){
			wp_create_category( $c );
		} //end if
	} //end foreach
}





function is_in_rad_terms( $term_arr ){
	$all_terms = get_terms( 'post_tag', array( 'hide_empty' => 0 ) ); //could be in a better place but 

	if ( is_string( $term_arr ) ) {
		if ( !in_array( $term_arr, $all_terms ) )
			wp_insert_term( $term_arr, 'post_tag' );
	} else {
		foreach( $term_arr as $term ){
			if ( !in_array( $term, $all_terms ) ){
				wp_insert_term( $term, 'post_tag' );
			} //end if
		} //end foreach
	} //end if else
}





//if rad post already exists refresh data if needed
function change_existing_rad_in_database( $pap, $post_id ) {
	$post_s = get_post( $post_id, ARRAY_A );
	$curr = get_post_meta( $post_id );

	//last name / post title / post slug
	if ( $post_s['post_title'] != $pap['title'] ){
		$new_slug = sanitize_title( $pap['title'] );
		wp_update_post( array(
				'ID' => $post_id,
				'post_title' => $pap['title'],
				'post_name' => $new_slug,
			) );
	}

	//pub date = post date
	if ( $post_s['post_date'] != $pap['pubDate'] ) {
		wp_update_post( array(
			'ID' => $post_id,
			'post_date' => $pap['pubDate'],
		) );
	}

	//content = description
	if ( $post_s['post_content'] != $pap['desc'] ){
		wp_update_post( array(
			'ID' => $post_id,
			'post_content' => $pap['desc'],
		) );
	}

	//link = post excerpt
	if ( $post_s['post_excerpt'] != $pap['link'] ){
		wp_update_post( array(
			'ID' => $post_id,
			'post_excerpt' => $pap['link'],
		) );
	}

	//category
	is_in_rad_category( $pap['category'] );
	foreach( $pap['category'] as $cat ){
		if ( !has_term( $cat, 'category', $pap ) ) {
			$i = get_term_by( 'name', $cat, 'category', ARRAY_N );
			if( !$i ) 
				$i = get_term_by( 'slug', $cat, 'category', ARRAY_N );
			$cat_list[] = $i[0];
		} //end if
	} //end foreach
	wp_set_post_categories( $post_id, $cat_list, true );


	//authors
	is_in_rad_terms( $pap['author'] );

	if ( is_string( $pap['author'] ) ) {
		if ( !has_term( $pap['author'], 'post_tag', $pap ) ) {
			wp_set_post_tags( $post_id, $pap['author'], true );
		} //end if
	} else {
		foreach( $pap['author'] as $auth ) {
			if ( !has_term( $auth, 'post_tag', $pap ) ) {
				wp_set_post_tags( $post_id, $auth, true );
			} //end if
		} //end foreach
	}
	
} //end change_existing_bios_in_database




//import new erad paper
function import_rad_into_database( $paper ){
	$post_id = wp_insert_post(
		array(
			'post_title' => $paper['title'],
			'post_type' => 'rad',
			'post_status' => 'publish',
			'post_date' => $paper['pubDate'],
			'post_content' => $paper['desc'],
			'post_excerpt' => $paper['link'],
			)
		);


	//category
	is_in_rad_category( $paper['category'] );
	foreach( $paper['category'] as $cat ){
		$i = get_term_by( 'name', $cat, 'category', ARRAY_N );
		if( !$i ) 
			$i = get_term_by( 'slug', $cat, 'category', ARRAY_N );
		$cat_list[] = $i[0];
	} //end foreach
	wp_set_post_categories( $post_id, $cat_list, true );


	//authors to tags
	is_in_rad_terms( $paper['author'] );

	if ( is_string( $paper['author'] ) ) {
		wp_set_post_tags( $post_id, $paper['author'], true );
	} else {
		foreach( $paper['author'] as $auth ) {
			wp_set_post_tags( $post_id, $auth, true );
		} //end foreach
	}
	
	return $post_id;
} //end import bios into database





?>