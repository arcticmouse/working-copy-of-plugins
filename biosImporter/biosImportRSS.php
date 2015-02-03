<?
/****************************************************************
*
* Plugin Name: importBios
* Plugin URI:
* Description: imports employee bios from EIS system in rss form
* Version: 1.5
* Author: Leta
*
****************************************************************/
include( plugin_dir_path( __FILE__ ) . 'additions.php');
include( 'bios_import_functions.php' );

if ( !defined('WP_LOAD_IMPORTERS') )
	return;

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( !class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) )
		require_once $class_wp_importer;
}





/**
 * RSS Importer
 *
 * @package WordPress
 * @subpackage Importer
 */

/**
 * RSS Importer
 *
 * Will process a RSS feed for importing posts into WordPress. This is a very
 * limited importer and should only be used as the last resort, when no other
 * importer is available.
 *
 * @since unknown
 */
if ( class_exists( 'WP_Importer' ) ) {
	class RSS_Import extends WP_Importer {

		var $posts = array ();
		var $file;


		function header() {
			echo '<div class="wrap">';
			screen_icon();
			echo '<h2>'.__('Import RSS', 'rss-importer').'</h2>';
		} //end function header


		function greeting(){
			echo 'Choose a letter to import last names from EIS feed <br />';
			echo '<select>';
			foreach( range( 'A', 'Z' ) as $char ) {
				echo '<option value="' . $char . '">' . $char . '</option>';
			}
			echo '</select>';
		} //end function greeting

		function footer() {
			echo '</div>';
		} //end function footer


		function get_posts() {
			global $wpdb;

			if( !empty( $this->file ) ){
				$index = 0;
				
				 foreach( $this->file->channel->item as $employee ){
					$name = $employee->name;
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

					$this->posts[$index] = $emp;
					$index++;
				 } //end foreach employee
			} //end this !empty
		} //end function get_posts


		function import_posts(){
			echo '<ol>';
			foreach( $this->posts as $post ) {
	
				if ( $post_id = post_exists( $post_title, $post_content, $post_date ) ) {
								_e('Post already imported', 'rss-importer');
							} else {
									//check for eid = check for dupes
									$query = new WP_Query( array( 'post_type' => 'bios', 'meta_key' => '_cmbi_eis', 'meta_value' => $post[eis_num] ) ); 

									//if it is update the bios if changes ortherwise if not import a new one
									if ( $query->post_count > 0 ) {
										while ( $query->have_posts() ){
											echo "<li>" . __('Updating post...', 'rss-importer');
											$query->the_post();
											$post_id = get_the_id();
											change_existing_bios_in_database( $post, $post_id );
										} //end while
									} else {
										echo "<li>" . __('Importing post...', 'rss-importer');
										$post_id = import_bios_into_database( $post );
									}

									if ( is_wp_error( $post_id ) )
										return $post_id;
									if (!$post_id) {
										_e('Couldn&#8217;t get post ID', 'rss-importer');
										return;
									} //end if 
				} //end if else

				echo '</li>';
			} //end foreach post
			echo '</ol>';
		} //end function import posts


		function import() {
			#$feed = get_import_bios_xml_contents( 'https://apps.unthsc.edu/biofeed/api/values' );
			$feed = get_import_bios_xml_contents( 'https://appsqa.unthsc.edu/biofeed/api/values?property=l_name&contains=A' );
			$feed = preg_replace( '/&(?!#?[a-z0-9]+;)/', '&amp;', $feed );
			$file = simplexml_load_string( $feed );

			$this->file = $file;
			$this->get_posts();
			$result = $this->import_posts();
			if ( is_wp_error( $result ) )
				return $result;

			echo '<h3>';
			printf(__('All done. <a href="%s">Have fun!</a>', 'rss-importer'), get_option('home'));
			echo '</h3>';
		}


		function dispatch() {
			$this->header();

			$this->greeting();

			$result = $this->import();

			$this->footer();
		} //end function dispatch

	} //end class


	$rss_import = new RSS_Import();
	register_importer('rss', __('RSS', 'rss-importer'), __('Import posts from an RSS feed.', 'rss-importer'), array ($rss_import, 'dispatch'));

} // class_exists( 'WP_Importer' )






function rss_importer_init() {
    load_plugin_textdomain( 'rss-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'rss_importer_init' );

?>