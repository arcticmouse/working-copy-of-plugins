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
						if ( $featured_bio_switch[0] == 'on' ) {
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
					 update_post_meta( $pageid, '_wp_page_template', 'single-bios.php' );

					 $one = get_the_title( $the_page_id );
					 $two = get_permalink( $pageid );
					 echo '<div class="wrap"><h2>Featured Biography Options</h2><br /><br /><div class="postbox" style="padding: 2em; font-size: 1em;">Your page was successfully created. Find it under ' . $one . ' to delete or get the url, which is <a href="' . $two . '" target="_blank" >' . $two . '</a><br />You will need to set the page template to SHARED FEATURE BIO TEMPLATE on the edit page here : <a href="https://qa.untmed.org/physbios/wp-admin/post.php?post=' . $pageid . '&action=edit">https://qa.untmed.org/physbios/wp-admin/post.php?post=' . $pageid . '&action=edit</a></div></div>';
				 }
			
		echo '</div>';
	}
		
 } //end function
 ?>