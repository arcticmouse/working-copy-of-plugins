<?php
/*********************************************************************************
 * Plugin Name: News_Room
 * Description: creates news custom post type
 * Version: 1
 * Author: Leta
 * License: A "Slug" license name e.g. GPL2

1. include admin stylying & files
2. create CPT
3. create taxonomies for CPT
4. change order of meta boxes
5. add meta boxes via cmb

uses : https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress

*********************************************************************************/

/*********************************************************************************
1. include admin stylying & files
*********************************************************************************/	

require_once( ABSPATH . 'wp-admin/includes/screen.php' );

add_action( 'admin_head', 'news_room_admin_css' );	

function news_room_admin_css() {
	global $post_type;
	
	if ( ( $_GET['post_type'] == 'news_room' ) || ( $post_type == 'news_room' ) ) {
		echo "<link type='text/css' rel='stylesheet' href='" . plugins_url( 'unthsc_news_admin.css', __FILE__ ) . "' />";
	} //end if
} //end function





/*********************************************************************************
2. create CPT
*********************************************************************************/	

add_action( 'init', 'create_post_type_news_room' );

function create_post_type_news_room() {
	
	$labels = array(
		'name' => __( 'news_room' ), 
		'singular_name' => __( 'News Room' ),
		'add_new' => __('Add new', 'Story'),
		'add_new_item' => __('Add new story'),
		'edit_item' => __('Edit story'),
		'new_item' => __('New story'),
		'all_items' => _('All News Room Items'),
		'view_item' => __('View story'),
		'search_items' => ('Search News Room'),
		'menu_name' => 'News Room'
	);
	
	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'show_ui' => true,
		'public' => true,
		'show_in_menu' => $show_in_menu,
		'menu_icon' => 'dashicons-universal-access',
		'has_archive' => true,
		'capability_type' => 'post',
		'rewrite' => array('slug' => 'news_room'),
		'menu_position' => 5,
		'supports' => array('title', 'editor', 'thumbnail', 'post-formats'),
		'taxonomies' => array('category', 'post_tag', 'flags')	
	);
	
	register_post_type( 'news_room', $args );
} //end function





/*********************************************************************************
3. create taxonomies for CPT
*********************************************************************************/	

add_action( 'init', 'create_flag_taxonomy', 0 );

function create_flag_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Flag', 'taxonomy general name' ),
		'singular_name'              => _x( 'Flag', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Flags' ),
		'popular_items'              => __( 'Popular Flags' ),
		'all_items'                  => __( 'All Flags' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Flag' ),
		'update_item'                => __( 'Update Flag' ),
		'add_new_item'               => __( 'Add New Flag' ),
		'new_item_name'              => __( 'New Flag Name' ),
		'separate_items_with_commas' => __( 'Separate Flags with commas' ),
		'add_or_remove_items'        => __( 'Add or remove flags' ),
		'choose_from_most_used'      => __( 'Choose from the most used flags' ),
		'not_found'                  => __( 'No flags found.' ),
		'menu_name'                  => __( 'Flags' ),
	);
	
	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'show_tagcloud'			=> true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array('slug' => 'Flags'),
	);
	
	register_taxonomy( 'flag', 'news_room', $args);
} //end function





/*********************************************************************************
4. change order of meta boxes
*********************************************************************************/	

add_action( 'do_meta_boxes', 'move_meta_boxes' );

//the function
function move_meta_boxes(){
	//remove side boxes
    remove_meta_box( 'postimagediv', 'news_room', 'side' );
	remove_meta_box( 'tagsdiv-flag', 'news_room', 'side' );
	
	remove_meta_box( 'formatdiv', 'news_room', 'side' );
	//remove_meta_box( 'tagsdiv-post_tag', 'news_room', 'side' );
	//remove_meta_box( 'categorydiv', 'news_room', 'side' );
	
	//add them back to the center
	add_meta_box( 'postimagediv', __( 'Story Image' ), 'post_thumbnail_meta_box', 'news_room', 'normal', 'high' );
	add_meta_box( 'formatdiv', __( 'Multimedia Types' ), 'post_format_meta_box', 'news_room', 'normal', 'high' );
	
	//add_meta_box( 'tagsdiv-post_tag', __( 'Tags' ), 'post_tags_meta_box', 'news_room', 'normal', 'high' );
	//add_meta_box( 'categorydiv', __( 'Categories' ), 'link_categories_meta_box', 'news_room', 'normal', 'high' );
} //end function





/*********************************************************************************
5. add meta boxes via cmb
*********************************************************************************/

add_filter( 'cmb_meta_boxes', 'front_page_news_info_metaboxes' );

function front_page_news_info_metaboxes( $meta_boxes ) {
	$prefix = '_cmbi_';
	$meta_boxes['front_page_news'] = array(
		'id' => 'front_page_news',
		'title' => 'Front Page News Options',
		'pages' => array('news_room'),
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true,
		'fields' => array(
			array(
				'name' => 'Front Page Story?',
				'id' => $prefix.'front_page',
				'type' => 'radio',
				'options' => array(
					'yes' => __( 'Yes', 'cmb' ),
					'no' => __( 'No', 'cmb' )
					)
				),
			array(
				'name' => 'Front Page Flag',
				'id' => $prefix.'front_page_flag',
				'taxonomy' => 'flag',
				'type' => 'taxonomy_select'
				)
			),
		);
	
return $meta_boxes;			
} //end function




add_action( 'init', 'news_init_cmb_meta_boxes' );

function news_init_cmb_meta_boxes() {
    if ( !class_exists( 'cmb_Meta_Box' ) ) {
        require_once( 'cmb_metabox/init.php' );
    } //end if
} //end function

?>