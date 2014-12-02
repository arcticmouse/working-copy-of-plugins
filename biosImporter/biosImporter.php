<?php
/****************************************************************
*
* Plugin Name: importBios
* Plugin URI:
* Description: imports employee bios from EIS system in rss form
* Version: 1
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
****************************************************************/




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

	#
	#cant use wordpress fetch_feed function because feed comes from intranet - secure sites not supported in this function
	#
	$feed = get_import_bios_xml_contents( plugins_url( 'profiler_data.xml', __FILE__  ) );
	$feed = preg_replace( '/&(?!#?[a-z0-9]+;)/', '&amp;', $feed );
	$xml = simplexml_load_string( $feed );
	
	echo '<ul>';
	if( !empty($xml) ){
		foreach( $xml->channel->item as $employee ){

			#
			# get the info
			#
			$name = $employee->title;
			$eis_link = $employee->link;
			$title = $employee->jobtitle;
			$email = $employee->mail;
			$phone = $employee->phone;
			$department = $employee->department;


			#
			# parse data : name, eis link
			#
			echo '<li>' . $name . '</li>';

		} //end foreach xml as item
	}//end if not empty xml feed var
	echo '</ul>';

} //end main import function




add_filter( 'wp_loaded', 'import_all_bios_from_xml' );

?>