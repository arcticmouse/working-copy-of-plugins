<?php
/****************************************************************
*
* Plugin Name: importBios
* Plugin URI:
* Description: imports employee bios from EIS system in rss form
* Version: 1.5
* Author: Leta
* RSS Input : 
*	item
*		title (name)
*		eis link
*		job title
*		mail (email)
*		phone
*		department
* Pseudo Code Steps:
*		1. get file contents
*		2. input name
*		3. input eis link
*		4. input title
*		5. input email
*		6. input phone
*		7. input department
*		8. input photo
*
* http://stackoverflow.com/questions/16463230/trying-to-read-values-from-an-xml-feed-and-display-them-in-php?rq=1
* http://stackoverflow.com/questions/561816/php-curl-extract-an-xml-response
* http://stackoverflow.com/questions/6674322/how-to-get-values-inside-cdatavalues-using-php-dom
*
* simple dom parser from boss - not using because already had CURL code that worked
*		include("simple_html_dom.php");
*		$test = file_get_html("http://intranet.hsc.unt.edu/applications/biofeed/");
*
* will most likely be replaced at a later date to a cron job off of server
*
****************************************************************/




//if bios already exists refresh data if needed
function change_existing_bios_in_database( $emp, $post_id ) {

	$post = get_post( $post_id, ARRAY_A );
	$curr = get_post_meta( $post_id );

	//last name / post title
	if ( $post->post_title != $emp[lname] ){
		wp_update_post( array(
				'ID' => $post_id,
				'post_title' => $emp[lname],
			) );
	}

	//first name
	if ( $curr[_cmbi_fname][0] != $emp[fname] ){
		update_post_meta( $post_id, '_cmbi_fname', $emp[fname] );
	}

	//job title
	if ( $curr[_cmbi_titles][0] != $emp[job_title] ){
		update_post_meta( $post_id, '_cmbi_titles', $emp[job_title] );
	}

	//department
	if ( $curr[_cmbi_department][0] != $emp[department] ){
		update_post_meta( $post_id, '_cmbi_department', $employee[department] );
	}

	//phone
	if ( $curr[_cmbi_fphone][0] != $emp[phone] ){
		update_post_meta( $post_id, '_cmbi_fphone', $employee[phone] );
	}

	//email
	if ( $curr[_cmbi_email][0] != $emp[email] ){
		update_post_meta( $post_id, '_cmbi_email', $employee[email] );
	}	

}





//import data into wordpress
function import_bios_into_database( $employee ){

	$post_id = wp_insert_post(
		array(
			'post_title' => $employee[lname],
			'post_type' => 'bios',
			'post_status' => 'publish'
			)
		);

	update_post_meta( $post_id, '_cmbi_fname', $employee[fname] );
	update_post_meta( $post_id, '_cmbi_titles', $employee[job_title] );
	update_post_meta( $post_id, '_cmbi_email', $employee[email] );
	update_post_meta( $post_id, '_cmbi_fphone', $employee[phone] );
	update_post_meta( $post_id, '_cmbi_department', $employee[department] );
	update_post_meta( $post_id, '_cmbi_eis', $employee[eis_num] );
}




//get data from rss with a curl
function get_import_bios_xml_contents( $data ) {

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_URL, $data );
	$imported_data = curl_exec( $ch );
	curl_close( $ch );

	return $imported_data;

} //end get_import_bios_xml_contents function





function import_all_bios_from_xml(){

	$feed = null;

	/****************************************************************
	* getting the data from the feed and putting it into variables
	* cant use wordpress fetch_feed function because feed comes from intranet - secure sites not supported in this function
	****************************************************************/
	$feed = get_import_bios_xml_contents( plugins_url( 'profiler_data.xml', __FILE__  ) );
	//$feed = get_import_bios_xml_contents( 'http://intranet.hsc.unt.edu/applications/biofeed/' );
	$feed = preg_replace( '/&(?!#?[a-z0-9]+;)/', '&amp;', $feed );
	$xml = simplexml_load_string( $feed );
	
	if( !empty($xml) ){
		foreach( $xml->channel->item as $employee ){
			//print_r($employee);

			$name = $employee->title;
			$eis_link = $employee->link;
			
			$emp[job_title] = (string)$employee->jobtitle;
			$emp[email] = (string)$employee->mail;
			$emp[phone] = (string)$employee->phone;
			$emp[department] = (string)$employee->department;

			//split from last white space on to seperate f and l names
			$full_name = preg_split( "/\s+(?=\S*+$)/", $name );
			$emp[fname] = $full_name[0];
			$emp[lname] = $full_name[1];

			//string of digits in url is the eis number 
			preg_match( "/\d+(?=\d*)/", $eis_link, $eis_arr );
			$emp[eis_num] = $eis_arr[0];

			//check for eid = check for dupes
			$query = new WP_Query( array( 'post_type' => 'bios', 'meta_key' => '_cmbi_eis', 'meta_value' => $emp[eis_num] ) ); 

			//if it is update the bios if changes ortherwise if not import a new one
			if ( $query->post_count > 0 ) {

				while ( $query->have_posts() ){
					$query->the_post();
					$pid = get_the_id();
					change_existing_bios_in_database( $emp, $pid );
				} //end while

			} else import_bios_into_database( $emp );
		} //end foreach xml as item
	}//end if not empty xml feed var

} //end main import function




//add_action( 'wp_loaded', 'import_all_bios_from_xml' );
add_action( 'init', 'import_all_bios_from_xml' );
?>