<?php

/**
*
*create new post type for rad objects
*
*author: leta
**/



/**create the type**/
function create_post_type_rad() {

	/*
	$current_site_id = get_current_blog_id();
	
	if( $current_site_id == 1 ) {
		$show_in_menu = true;
		} else { 
			$show_in_menu = false;
			}
	*/ //fill this out when/if you know what site this will be on

	$labels = array( 
		'name' => __( 'rad' ), 
		'singular_name' => __( 'rad' ),
		'add_new' => __('Add new', 'RAD paper'),
		'add_new_item' => __('Add new RAD paper'),
		'edit_item' => __('Edit RAD Paper'),
		'new_item' => __('New RAD Paper'),
		'all_items' => __('All RAD Papers'),
		'view_item' => __('View RAD Paper'),
		'search_items' => __('Search RAD Papers'),
		'menu_name' => 'RAD Papers'
		);
		
	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'show_ui' => true,
		'public' => true,
		'show_in_menu' => true,
		'menu_icon' => 'dashicons-welcome-learn-more',
		'has_archive' => true,
		'capability_type' => 'post',
		'rewrite' => array('slug' => 'rad'),
		'menu_position' => 5,
		'supports' => array('title', 'editor', 'author', 'excerpt' ),
		'taxonomies' => array( 'category', 'post_tag' )
		);

	register_post_type( 'rad', $args);
}

/**add the hook**/
add_action('init', 'create_post_type_rad');

?>