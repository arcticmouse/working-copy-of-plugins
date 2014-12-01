<?php
/**
 * Plugin Name: UNTHSC_Lab_Instruments_Post_Type
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: creates a Lab Instrument post type for laboratory instruments
 * Version: 1.0
 * Author: Leta
 * License: A "Slug" license name e.g. GPL2
 *
 * uses : https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress
 *
 **/
 
 
 /**
*
*create new post type for directory
*
*author: leta
**/

/**add the hook**/
add_action('init', 'create_post_type_lab_instruments');

/**create the type**/
function create_post_type_lab_instruments() {
	$labels = array( 
		'name' => __( 'Lab Instruments' ), 
		'singular_name' => __( 'Instrument' ),
		'add_new' => __('Add new', 'Instrument'),
		'add_new_item' => __('Add new instrument'),
		'edit_item' => __('Edit instrument'),
		'new_item' => __('New instrument'),
		'all_items' => _('All Lab Instrumentss'),
		'view_item' => __('View instrument'),
		'search_items' => ('Search lab instrumentss'),
		'menu_name' => 'Lab Instruments'
		);
		
	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'show_ui' => true,
		'public' => true,
		'has_archive' => true,
		'rewrite' => array('slug' => 'Lab_Instruments'),
		'menu_position' => 5,
		'supports' => array('title', 'editor', 'thumbnail'),
		'taxonomies' => array('category'),
		'capability_type' => 'post'
		);

	register_post_type( 'lab_instrument', $args);
}
?>