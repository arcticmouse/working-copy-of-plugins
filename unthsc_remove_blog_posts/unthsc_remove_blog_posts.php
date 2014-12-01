<?php
/**
 * Plugin Name: UNTHSC_Remove_Regular_Posts
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: remove regular blog posts
 * Version: 1.0
 * Author: Leta
 * License: A "Slug" license name e.g. GPL2
 *
 *http://wordpress.stackexchange.com/questions/52099/how-to-remove-entire-admin-menu/52151#52151
 *
 **/
 
add_action( 'admin_menu', 'remove_post_on_menu' );

function remove_post_on_menu() {
	remove_menu_page( 'edit.php' );
	} 
 
 ?>