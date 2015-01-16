<?php
/**
 * Plugin Name: sanitize titles
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: sanitize the titles of all pages to make them into friendly urls
 * Version: 1
 * Author: leta
 *
 * 1. get 0 parent titles
 * 2. sanitize titles
 * 3. print titles
 * 4. get titles with parents
 * 5. sanitize titles
 * 6. print titles in hierarchy
 * 7. repeat until all childrens are ded. or not there.
 */


function do_the_curl( &$array_of_pages ){

	$path = plugins_url() . "/sanitize_titles/thelist.txt";

	echo $path;
	if ( !is_writable( $path ) )
    	die('File does not exist');

	$file = fopen( $path, "a" );

	if (!$file) {
		die('file is not');
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FILE, $file);

	curl_exec($ch);

	foreach( $array_of_pages as $a ){
		fputs( $file, 'Friendly, Page ID, GUID' );
		fputcsv( $file, explode( ',', $a ) );
	}

	curl_close($ch);
	fclose( $file );

/*
	$file = fopen( plugins_url() . "/sanitize_titles/Workbook2.csv", "a" );

	if (!$file) {
		echo 'not open';
		exit;
	}  

	foreach( $array_of_pages as $a ){
		fputs( $file, 'Friendly, Page ID, GUID' );
		fputcsv( $file, explode( ',', $a ) );
	}
	
	fclose( $file );
*/

} //end function





function recursive_print_title( $node, $ancestor_string, &$list_of_pages ){

		$sanitized = sanitize_title_with_dashes( $node->post_title );

		#$list_of_pages[] = 
		echo $ancestor_string . $sanitized . ', ' . $node->ID . ', ' . $node->guid . '<br />';

		$args = array( 'post_parent' => $node->ID, 'post_type' => 'page' );
		$kids = get_children( $args );

		if ( count($kids) > 0 ) {
			$ancestor_string .= $sanitized . '/';
			foreach( $kids as $kid ) {
				recursive_print_title( $kid, $ancestor_string, $list_of_pages );
			} //end foreach
		} //end if there are kids

} //end function





function page_titles_finder( &$arr ) {
	
	$args = array( 'parent' => 0 );
	$pages = get_pages( $args );
	$parent = null;

	foreach( $pages as $page ) {
		recursive_print_title( $page, $parent, $arr );
	} //end foreach

} //end function





function initialize_get_all_pages_data() {

	$list_of_sites = wp_get_sites();
	$arr = array();

	foreach( $list_of_sites as $s ) {

		switch_to_blog( $s['blog_id'] );
			echo $s['blog_id'] . '<br />';
			$arr[] = 'blog id ' . $s['blog_id'];
			page_titles_finder( $arr );
		restore_current_blog();

	} //foreach blog

	#do_the_curl( $arr );

} //end function





add_action( 'wp_loaded', 'initialize_get_all_pages_data' );

?>