<?php
/**
 * Plugin Name: UNTHSC WP Dashboard
 * Description: wordpress dashboard for UNTHSC users
 * Version: 1
 * Author: Leta
 * License: A "Slug" license name e.g. GPL2
 **/
 
/************************************************
 *
 * REMOVE ALL METABOXES AND THEN ADD ONE THAT SHOWS USER'S SITES
 * 
************************************************/
 
function add_user_site_list() {

	if(is_user_logged_in()) {
	global $current_user;
	  $blogs = get_blogs_of_user( $current_user->id );
	     if($blogs) {
	     	 foreach ( $blogs as $blog ) {
	     	 	echo '<h3>' . $blog->blogname . '</h3><p style="margin-left:2em;"><a href="http://' . $blog->domain . $blog->path . '" target="_blank">Visit</a> | <a href="http://' . $blog->domain . $blog->path . 'wp-admin">Dashboard</a></p>';
			} //end foreach
	     }//end if($blogs)
	}//end if(user logged in)
}

function reset_dashboard_widgets() {
 		//remove widgets
 		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
 		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );  
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );  
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'normal' ); 
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
		
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
 		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );


		//call to add a widget
 		wp_add_dashboard_widget( 'user_site_list', 'My Sites....', 'add_user_site_list' );
 }

add_action( 'wp_dashboard_setup', 'reset_dashboard_widgets' );





/************************************************
 *
 * REMOVE SCREEN OPTIONS FROM DASHBOARD
 * 
************************************************/

function remove_screen_options() {
		return current_user_can( 'manager_options' );
}

add_filter( 'screen_options_show_screen', 'remove_screen_options');





/************************************************
 *
 * ADD BACKGROUND TO DASHBOARD
 * 
************************************************/

function add_the_background() {
	echo '<style type="text/css">body {background : url("http://qa.untmed.org/wp-content/uploads/2014/07/hiDefCampus.jpg") no-repeat scroll center center / 100% auto;}';
}

//add_action( 'admin_head', 'add_the_background' );
?>