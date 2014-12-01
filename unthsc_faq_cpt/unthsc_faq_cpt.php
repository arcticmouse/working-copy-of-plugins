<?php
/**
 * Plugin Name: UNTHSC_FAQ_Post_Type
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: creates a FAQ post type for departmental FAQs
 * Version: 3.0
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
add_action('init', 'create_post_type_faq');

/**create the type**/
function create_post_type_faq() {
	$labels = array( 
		'name' => __( 'FAQ' ), 
		'singular_name' => __( 'FAQ' ),
		'add_new' => __('Add new', 'Frequently Asked Question'),
		'add_new_item' => __('Add new Frequently Asked Question'),
		'edit_item' => __('Edit Frequently Asked Question'),
		'new_item' => __('New Frequently Asked Question'),
		'all_items' => _('All FAQs'),
		'view_item' => __('View Frequently Asked Question'),
		'search_items' => ('Search FAQs'),
		'menu_name' => 'FAQs'
		);
		
	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'show_ui' => true,
		'public' => true,
		'has_archive' => true,
		'rewrite' => array('slug' => 'FAQ'),
		'menu_position' => 5,
		'supports' => array('title', 'editor'),
		'taxonomies' => array('category'),
		'capability_type' => 'post'
		);

	register_post_type( 'FAQ', $args);
}


?>