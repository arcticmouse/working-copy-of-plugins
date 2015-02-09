<?php
/**
 * Plugin Name: Share News by Category
 * Description: lets end users create a news feed template based on news category
 * Version: 1
 * Author: Leta
 * License: A "Slug" license name e.g. GPL2
 *
 * code basically copied pasted from the shared featured bios plugin
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

function add_my_news_feed_settings(){
	add_options_page( 'Create News Feed By Category', 'Create News Feed By Category', 'manage_options', 'news-feed-options', 'news_feed_plugin_options_page' );
}

add_action( 'admin_menu', 'add_my_news_feed_settings' );

function news_feed_plugin_options_page() {

	if (!isset($_POST['submit'])) {
		
		$pages = get_pages(); 
		/***************************************************************
		 * 2a. get parent page from user form
		 * 2b. get featured employee from user form
		 * 2c. save data in wp_options in options group
		***************************************************************/	
		echo '<div class="wrap">';
			echo '<h2>Add News Feed by News Category</h2><br /><br />';
			echo '<form method="post" action="' . $PHP_SELF .'">';
				echo '<div class="postbox" style="padding: 2em; font-size: 1.5em;">';
				
				echo '<h3 class="hndle"><span>Choose the parent page/department/school</span></h3>';
				echo '<div class="inside">';
				$x = 0;
				foreach ( $pages as $page ) {
					if ( $page->post_parent == '0' ) {
						$p = $page->post_title;
						$page_id = $page->ID;
						echo '<input type="radio" name="feed_parent_page" id="feed_parent_page" value="' . $page_id . '"> ' . $p . '<br />';
						$x++;	
						}
				} //end foreach
				echo '</div></div><br />';
				
				echo '<div class="postbox" style="padding: 2em; font-size: 1.5em;">';
				echo '<h3 class="hndle"><span>Choose the news category to display (only one, sorry!)</span></h3>';
				echo '<div class="inside">';
				
				//switch to news blog and get all the categories
				switch_to_blog(16);
			 				
					$loop = get_categories( );
					echo '<select name="news_feed_selection" id="news_feed_selection">';
					foreach( $loop as $cat ) {
						echo'<option value="'. $cat->term_id .'">' . $cat->cat_name . '</option>';
					} //end foreach
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
			$the_page_id = $_POST['feed_parent_page'];
			$the_cat_id = $_POST[news_feed_selection];

			switch_to_blog(16);
				$cname = get_term( $the_cat_id, 'category' );
			restore_current_blog();

			$the_page_title =  ucfirst( $cname->name ) . ' News Feed';
			
			$page['post_type']    = 'page';
			$page['post_content'] = $the_cat_id;
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
					 update_post_meta( $pageid, '_wp_page_template', 'news-feed-template.php' );

					 $one = get_the_title( $the_page_id );
					 $two = get_permalink( $pageid );
					 echo '<div class="wrap"><h2>Add News Feed by News Category</h2><br /><br /><div class="postbox" style="padding: 2em; font-size: 1em;">Your page was successfully created. Find it under ' . $one . ' to delete or get the url, which is <a href="' . $two . '" target="_blank" >' . $two . '</a><br />You will need to set the page template to NEWS FEED TEMPLATE on the edit page here, if it is not already set at that : <a href="https://qa.untmed.org/physbios/wp-admin/post.php?post=' . $pageid . '&action=edit">https://qa.untmed.org/physbios/wp-admin/post.php?post=' . $pageid . '&action=edit</a></div></div>';
				 }
			
		echo '</div>';
	}
		
 } //end function
 ?>