<?php
/**
 * Plugin Name: Share Featured BIOS
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: share a single featured bios on a site
 * Version: 1
 * Author: Leta
 * License: A "Slug" license name e.g. GPL2
 *
 * -1. create user form in dashboard options
 * -2a. get parent page from user form
 * -2b. get featured employee from user form
 * -2c. save data in wp_options in options group
 * -3. apply data from #2 to template
 * -4. create a page under the parent page 
 * x5. apply template to newly created page
 * -6. get link for page
 *
 * http://www.wpexplorer.com/wordpress-page-templates-plugin/
 *
 **/

/***************************************************************
* 1. create user form in dashboard options
***************************************************************/

function add_my_shared_bios_settings(){
	add_options_page( 'Featured Biography', 'Featured Biography', 'manage_options', 'featured-bios-options', 'featured_bios_plugin_options_page' );
}

add_action( 'admin_menu', 'add_my_shared_bios_settings' );

function featured_bios_plugin_options_page() {

	if (!isset($_POST['submit'])) {
		
		$pages = get_pages(); 
		/***************************************************************
		 * 2a. get parent page from user form
		 * 2b. get featured employee from user form
		 * 2c. save data in wp_options in options group
		***************************************************************/	
		echo '<div class="wrap">';
			echo '<h2>Featured Biography Options</h2><br /><br />';
			echo '<form method="post" action="' . $PHP_SELF .'">';
				echo '<div class="postbox" style="padding: 2em; font-size: 1.5em;">';
				
				echo '<h3 class="hndle"><span>Choose the parent page/department/school</span></h3>';
				echo '<div class="inside">';
				$x = 0;
				foreach ( $pages as $page ) {
					if ( $page->post_parent == '0' ) {
						$p = $page->post_title;
						$page_id = $page->ID;
						echo '<input type="radio" name="featured_page" id="featured_page" value="' . $page_id . '"> ' . $p . '<br />';
						$x++;	
						}
				} //end foreach
				echo '</div></div><br />';
				
				echo '<div class="postbox" style="padding: 2em; font-size: 1.5em;">';
				echo '<h3 class="hndle"><span>Choose the biography to display</span></h3>';
				echo '<div class="inside">';
				
				switch_to_blog(1);
			 
					$args = array( 'post_type' => 'bios' );
					$loop = new WP_Query($args);
					echo '<select name="featured_bio_selection" id="featured_bio_selection">';
					while($loop->have_posts()): $loop->the_post();
						$post_id = get_the_id();
						$featured_bio_switch = get_post_meta( $post_id, '_cmbi_featured', true );
						if ( $featured_bio_switch == 'on' ) {
							$lname = get_the_title();
							$fname = get_post_meta( $post_id, '_cmbi_fname', true);
							echo'<option value="'. $post_id .'">' . $lname . ', ' . $fname . '</option>';
						} //end if
						endwhile;
					echo '</select>';
					wp_reset_query();
			
					restore_current_blog();
					
					echo '</div></div>';
				echo '<input type="submit" value="submit" name="submit">';
				echo '</form>';
		
	} else {
		/***************************************************************
		 * 4. create a page under the parent page 
		***************************************************************/
		echo '<div class="wrap">';
			$the_page_id = $_POST['featured_page'];
			$the_bio_id = $_POST[featured_bio_selection];
			

			switch_to_blog(1);
			
				$args = array( 'post_type' => 'bios' );
				$loop_two = new WP_Query($args);

				while($loop_two->have_posts()): $loop_two->the_post();
				$id = get_the_id();
					if ( $id == $the_bio_id ) {
						$the_page_title = get_the_title();
					}
					endwhile;
				wp_reset_query();
				
			restore_current_blog();
			
			
			$page['post_type']    = 'page';
			$page['post_content'] = $the_bio_id;
			$page['post_parent']  = $the_page_id;
			$page['post_author']  = $user_ID;
			$page['post_status']  = 'publish';
			$page['post_title']   = $the_page_title; 
			$pageid = wp_insert_post( $page );
			if ($pageid == 0) { 
				echo 'Page creation has failed, please try again or contact web services';
				 } else {
					 
			 		/***************************************************************
			 		 * 6. get link for page
			 		***************************************************************/
					 //update_post_meta( $the_page_id, '_wp_page_template', 'shared_feature_bios_template.php' );

					 $one = get_the_title( $the_page_id );
					 $two = get_permalink( $pageid );
					 echo '<div class="wrap"><h2>Featured Biography Options</h2><br /><br /><div class="postbox" style="padding: 2em; font-size: 1em;">Your page was successfully created. Find it under ' . $one . ' to delete or get the url, which is <a href="' . $two . '" target="_blank" >' . $two . '</a><br />You will need to set the page template to SHARED FEATURE BIO TEMPLATE on the edit page here : <a href="https://qa.untmed.org/physbios/wp-admin/post.php?post=' . $pageid . '&action=edit">https://qa.untmed.org/physbios/wp-admin/post.php?post=' . $pageid . '&action=edit</a></div></div>';
				 }
			
		echo '</div>';
	}
		
 } //end function
 
 
 
 
 
 
 
 class PageTemplater {

 		/**
          * A Unique Identifier
          */
 		 protected $plugin_slug;

         /**
          * A reference to an instance of this class.
          */
         private static $instance;

         /**
          * The array of templates that this plugin tracks.
          */
         protected $templates;


         /**
          * Returns an instance of this class. 
          */
         public static function get_instance() {

                 if( null == self::$instance ) {
                         self::$instance = new PageTemplater();
                 } 

                 return self::$instance;

         } 

         /**
          * Initializes the plugin by setting filters and administration functions.
          */
         private function __construct() {

                 $this->templates = array();


                 // Add a filter to the attributes metabox to inject template into the cache.
                 add_filter(
 					'page_attributes_dropdown_pages_args',
 					 array( $this, 'register_project_templates' ) 
 				);


                 // Add a filter to the save post to inject out template into the page cache
                 add_filter(
 					'wp_insert_post_data', 
 					array( $this, 'register_project_templates' ) 
 				);


                 // Add a filter to the template include to determine if the page has our 
 				// template assigned and return it's path
                 add_filter(
 					'template_include', 
 					array( $this, 'view_project_template') 
 				);


                 // Add your templates to this array.
                 $this->templates = array(
                         'shared_feature_bios_template.php'     => 'Shared Feature Bios Template',
                 );
				
         } 


         /**
          * Adds our template to the pages cache in order to trick WordPress
          * into thinking the template file exists where it doens't really exist.
          *
          */

         public function register_project_templates( $atts ) {

                 // Create the key used for the themes cache
                 $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

                 // Retrieve the cache list. 
 				// If it doesn't exist, or it's empty prepare an array
                 $templates = wp_get_theme()->get_page_templates();
                 if ( empty( $templates ) ) {
                         $templates = array();
                 } 

                 // New cache, therefore remove the old one
                 wp_cache_delete( $cache_key , 'themes');

                 // Now add our template to the list of templates by merging our templates
                 // with the existing templates array from the cache.
                 $templates = array_merge( $templates, $this->templates );

                 // Add the modified cache to allow WordPress to pick it up for listing
                 // available templates
                 wp_cache_add( $cache_key, $templates, 'themes', 1800 );

                 return $atts;

         } 

         /**
          * Checks if the template is assigned to the page
          */
         public function view_project_template( $template ) {

                 global $post;

                 if (!isset($this->templates[get_post_meta( 
 					$post->ID, '_wp_page_template', true 
 				)] ) ) {
					
                         return $template;
						
                 } 

                 $file = plugin_dir_path(__FILE__). get_post_meta( $post->ID, '_wp_page_template', true );
				
                 // Just to be safe, we check if the file exist first
                 if( file_exists( $file ) ) {
                         return $file;
                 } 
 				else { echo $file; }

                 return $template;

         } 


 } 

 add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );
 
 ?>