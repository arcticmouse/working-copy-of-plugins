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

function recursive_print_title( $node, $ancestor_string ){

		$sanitized = sanitize_title_with_dashes( $node->post_title );
		echo  $ancestor_string . $sanitized . ' <br />';

		$args = array( 'post_parent' => $node->ID, 'post_type' => 'page' );
		$kids = get_children( $args );

		if ( count($kids) > 0 ) {
			$ancestor_string .= $sanitized . '/';
			foreach( $kids as $kid ) {
				recursive_print_title( $kid, $ancestor_string );
			} //end foreach
		} //end if there are kids

} //end function





add_action( 'wp_loaded', 'page_titles_finder' );

function page_titles_finder() {
	
	$args = array( 'parent' => 0 );
	$pages = get_pages( $args );
	$parent = null;

	foreach( $pages as $page ) {
		recursive_print_title( $page, $parent );
	} //end foreach

} //end function

?>