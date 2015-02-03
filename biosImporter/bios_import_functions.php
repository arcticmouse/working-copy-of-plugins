<?php

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


//import new employee
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
	return $post_id;
} //end import bios into database



//if bios already exists refresh data if needed
function change_existing_bios_in_database( $emp, $post_id ) {
	$post_s = get_post( $post_id, ARRAY_A );
	$curr = get_post_meta( $post_id );

	//last name / post title
	if ( $post_s[post_title] != $emp[lname] ){
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
		update_post_meta( $post_id, '_cmbi_department', $emp[department] );
	}
	//phone
	if ( $curr[_cmbi_fphone][0] != $emp[phone] ){
		update_post_meta( $post_id, '_cmbi_fphone', $emp[phone] );
	}
	//email
	if ( $curr[_cmbi_email][0] != $emp[email] ){
		update_post_meta( $post_id, '_cmbi_email', $emp[email] );
	}	
} //end change_existing_bios_in_database

?>