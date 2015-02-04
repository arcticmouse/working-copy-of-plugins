<?php
//get feed from rss
function get_import_bios_xml_contents( $data ) {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_URL, $data );
	$imported_data = curl_exec( $ch );
	curl_close( $ch );

	return $imported_data;
} //end get_import_bios_xml_contents function





//get day as a number eg 0:sunday 1:monday
function get_week_day_as_number(){
	$day = jddayofweek( cal_to_jd(CAL_GREGORIAN, date("m"),date("d"), date("Y")) );

	switch( $day ){
		case 0:
			$r = range( 'A', 'D' );
			break;
		case 1:
			$r = range( 'E', 'H' );
			break;
		case 2:
			$r = range( 'I', 'L' );
			break;
		case 3: 
			$r = range( 'M', 'Q' );
			break;
		case 4:
			$r = range( 'R', 'U' );
			break;
		case 5:
			$r = range( 'V', 'S' );
			break;
		case 6:
			$r = range( 'T', 'W' );
			break;
		case 7:
			$r = range( 'X', 'Z' );
			break;
	} //end switch

	return $r;
} //end function





//get json files contents in xml format
function get_json_file(){
	$json = null;
	$iterate = 0;

	$range = get_week_day_as_number();

	foreach( $range as $char ){
		$file = dirname( plugin_dir_path( __FILE__ ) ) . '/biosImporter/' . $char . '.json';
		$json = file_get_contents( $file );
		$decoded = json_decode( $json, true );
		foreach( $decoded['channel']['item'] as $d ){
			$big_arr[] = $d;
		} //end foreach
	} //end foreach
	
	return $big_arr;
} //end function get_json_file




//if employee is in OLD.json but not new.json, delete it
function check_for_removed_employees( $letter ){
	$temp_arr = null;
	$file_new = dirname( plugin_dir_path( __FILE__ ) ) . '/biosImporter/' . $letter . '.json';
	$file_old = dirname( plugin_dir_path( __FILE__ ) ) . '/biosImporter/' . $letter . 'OLD.json';

	$json_new = file_get_contents( $file_new );
	$json_old = file_get_contents( $file_old );

	$new = json_decode( $json_new, true );
	$old = json_decode( $json_old, true );

	if( isset( $new['channel']['item'] ) ) {
		foreach( $new['channel']['item'] as $newkey=>$newvalue ) {
			$temp_arr[] = $newvalue['link'];
		} //make an array of new values to check against

		foreach( $old['channel']['item'] as $key=>$value ) {
			if ( !in_array( $value['link'], $temp_arr ) ) {
				preg_match( "/\d+(?=\d*)/", $$value['link'], $eis_arr );
				$eis = $eis_arr[0];
				$args = array(
						'post_type' => 'bios',
						'meta_key' => '_cmbi_eis',
						'meta_value' => $eis,
					);
				$query = get_posts( $args );

				if ( count($query) > 0 ) {
					wp_delete_post( $query[0]->ID );
				} //end if query has results
			} //end if old value doesnt exist in here delete it
		} //end foreach going through each old value
	} //end if new hsa stuff

	unlink( $file_old ); //delete the old!
} //end fucntion check for removed employees





function get_eis_rss_callback(){
	$range = get_week_day_as_number();

	foreach( $range as $char ){
		$feed = get_import_bios_xml_contents( 'https://appsqa.unthsc.edu/biofeed/api/values?property=l_name&contains=' . $char );
		$feed = preg_replace( '/&(?!#?[a-z0-9]+;)/', '&amp;', $feed );
		$xml = simplexml_load_string( $feed );
		$json = json_encode( $xml );

		$file = dirname( plugin_dir_path( __FILE__ ) ) . '/biosImporter/' . $char . '.json';
		//keep the old one to check for removed employees
		if ( file_exists($file ) ) {
			rename( $file, dirname( plugin_dir_path( __FILE__ ) ) . '/biosImporter/' . $char . 'OLD.json' );
		}

		file_put_contents( $file, $json );

		check_for_removed_employees( $char );
	} //end foreach
} //end callback





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
	update_post_meta( $post_id, 'eis_photo_link', $employee[eis_image] );
	update_post_meta( $post_id, '_cmbi_eis', $employee[eis_num] );
	return $post_id;
} //end import bios into database





//if bios already exists refresh data if needed
function change_existing_bios_in_database( $emp, $post_id ) {
	$post_s = get_post( $post_id, ARRAY_A );
	$curr = get_post_meta( $post_id );

	//last name / post title / post slug
	if ( $post_s[post_title] != $emp[lname] ){

		$new_slug = sanitize_title( $emp[name] );

		wp_update_post( array(
				'ID' => $post_id,
				'post_title' => $emp[lname],
				'post_name' => $new_slug,
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
	//eis image
	if ( $curr[eis_photo_link][0] != $emp[eis_image] ){
		update_post_meta( $post_id, 'eis_photo_link', $emp[eis_image] );
	}
} //end change_existing_bios_in_database





function wi_create_daily_import_feed_schedule(){
	//http://www.smashingmagazine.com/2013/10/16/schedule-events-using-wordpress-cron/
	//Use wp_next_scheduled to check if the event is already scheduled
  	$timestamp = wp_next_scheduled( 'wi_import_daily_feed' );

  	//If $timestamp == false schedule daily backups since it hasn't been done previously
  	if( $timestamp == false ){
    	//Schedule the event for right now, then to repeat daily using the hook 'wi_import_daily_feed'
    	wp_schedule_event( time(), 'daily', 'wi_import_daily_feed' );
  	} //end if
} //end wi_create_daily_backup_schedule function

?>