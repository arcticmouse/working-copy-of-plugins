<?
/****************************************************************
*
* Plugin Name: RAD 2015 RSS importer
* Plugin URI:
* Description: Updates RAD objects from digital commons rss feed. Must run manually through TOOLS > IMPORT > RSS. 
* Version: 2
* Author: Leta
*
* feed here http://digitalcommons.hsc.unt.edu/rad/rad_feed.rss
*
****************************************************************/

include( 'create_rad_post_type.php' );
include( 'rad_importer_2015_functions.php' );

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
	class RAD_RSS_Import extends WP_Importer {

		var $posts = array ();
		var $file;





		function header() {
			echo '<div class="wrap">';
			screen_icon();
			echo '<h2>'.__('Import RSS', 'rss-importer').'</h2>';
		} //end function header





		function footer() {
			echo '</div>';
		} //end function footer





		function get_posts() {
			global $wpdb;

			if( !empty( $this->file ) ){

				$index = 0;
				
				 foreach( $this->file as $paper ){
					$ppr['title'] = $paper['title'];
					$ppr['link'] = $paper['link'];

					$pubDate = date( 'Y-m-d H:i:s', strtotime( $paper['pubDate'] ) );
					$ppr['pubDate'] = $pubDate;

					$ppr['desc'] = $paper['description'];

					$ppr['category'] = explode( ',', $paper['category'] );

					if ( is_string( $paper['author'] ) )
						$auth = $paper['author'];

					foreach( $paper['author'] as $a ){
						$auth[] = $a;
					}	
					$ppr['author'] = $auth;
					unset($auth);

					$this->posts[$index] = $ppr;
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
										//check for link = check for dupes
										$page = get_page_by_title( $post['title'], OBJECT, 'rad' );
										//if it is update the bios if changes ortherwise if not import a new one
										if ( $page != NULL ) {
											#while ( $query->have_posts() ){
												echo "<li>" . __('Updating post...', 'rss-importer');
												#$query->the_post();
												$post_id = $page->ID;
												change_existing_rad_in_database( $post, $post_id );
											#} //end while
										} else {
											echo "<li>" . __('Importing post...', 'rss-importer');
											$post_id = import_rad_into_database( $post );
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
			$feed = get_rad_json_file();

			$this->file = $feed;
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

			$this->import();

			$this->footer();
		} //end function dispatch

	} //end class


	$rss_import = new RAD_RSS_Import();
	register_importer('rss', __('RSS', 'rss-importer'), __('Import posts from an RSS feed.', 'rss-importer'), array ($rss_import, 'dispatch'));

} // class_exists( 'WP_Importer' )






function rad_rss_importer_init() {
	#get_rad_rss_callback();
    load_plugin_textdomain( 'rss-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


add_action( 'init', 'rad_rss_importer_init' );

?>