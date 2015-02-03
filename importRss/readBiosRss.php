<?
/****************************************************************
*
* Plugin Name: readBiosRss
* Plugin URI:
* Description: read bios from eis feed into 26 different json files. yes, 26, one for each letter of the alphabet. 0_0
* Version: 1
* Author: Leta
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







function get_eis_rss_callback(){
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

	foreach( $r as $char ){
		$feed = get_import_bios_xml_contents( 'https://appsqa.unthsc.edu/biofeed/api/values?property=l_name&contains=' . $char );
		$feed = preg_replace( '/&(?!#?[a-z0-9]+;)/', '&amp;', $feed );
		$xml = simplexml_load_string( $feed );
		$json = json_encode( $xml );
		
		$file = dirname( plugin_dir_path( __FILE__ ) ) . '/readBiosRss/' . $char . '.json';

		file_put_contents( $file, $json );
	} //end foreach
} //end callback

add_action( 'init', 'get_eis_rss_callback' );
?>