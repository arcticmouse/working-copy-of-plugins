<?php
/*********************************************************************************
 * Plugin Name: Story
 * Description: creates news story custom post type
 * Version: 2
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

add_action( 'admin_head', 'story_admin_css' );

function story_admin_css() {
	global $post_type;

	if ( ( $_GET['post_type'] == 'story' ) || ( $post_type == 'story' ) ) {
		echo "<link type='text/css' rel='stylesheet' href='" . plugins_url( 'unthsc_news_admin.css', __FILE__ ) . "' />";
	} //end if
} //end function





/*********************************************************************************
2. create CPT
*********************************************************************************/

add_action( 'init', 'create_post_type_story' );

function create_post_type_story() {

	//$current_site_id = get_current_blog_id();
	$current_site_id = get_bloginfo( 'name' );

	if( $current_site_id == 'Newsroom' ) {
		$show_in_menu = true;
		} else { 
			$show_in_menu = false;
			}

	$labels = array(
		'name' => __( 'story' ),
		'singular_name' => __( 'Story' ),
		'add_new' => __('Add new', 'Story'),
		'add_new_item' => __('Add new story'),
		'edit_item' => __('Edit story'),
		'new_item' => __('New story'),
		'all_items' => _('All Stories'),
		'view_item' => __('View story'),
		'search_items' => ('Search stories'),
		'menu_name' => 'Stories'
	);

	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'show_ui' => true,
		'public' => true,
		'show_in_menu' => $show_in_menu,
		'menu_icon' => 'dashicons-id',
		'has_archive' => true,
		'capability_type' => 'post',
		'rewrite' => array('slug' => 'story'),
		'menu_position' => 5,
		'supports' => array('title', 'editor', 'thumbnail', 'post-formats'),
		'taxonomies' => array('category', 'post_tag', 'flags')
	);

	register_post_type( 'story', $args );
} //end function





/*********************************************************************************
3. create taxonomies for CPT
*********************************************************************************
add_action( 'init', 'create_flag_taxonomy', 0 );

function create_flag_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Flags', 'taxonomy general name' ),
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
		//'show_ui'               => true,
		'show_admin_column'     => true,
		'show_tagcloud'			=> true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array('slug' => 'flags'),
	);

	register_taxonomy( 'flags', 'story', $args );
}



/*********************************************************************************
4. change order of meta boxes
*********************************************************************************/

add_action( 'do_meta_boxes', 'move_meta_boxes' );

//the function
function move_meta_boxes(){
	//remove side boxes
    //remove_meta_box( 'postimagediv', 'story', 'side' );
	remove_meta_box( 'tagsdiv-flag', 'story', 'side' );

	remove_meta_box( 'formatdiv', 'story', 'side' );
	//remove_meta_box( 'tagsdiv-post_tag', 'story', 'side' );
	//remove_meta_box( 'categorydiv', 'story', 'side' );

	//add them back to the center
	//add_meta_box( 'postimagediv', __( 'Story Image' ), 'post_thumbnail_meta_box', 'story', 'normal', 'high' );
	add_meta_box( 'formatdiv', __( 'Multimedia Types' ), 'post_format_meta_box', 'story', 'normal', 'high' );

	//add_meta_box( 'tagsdiv-post_tag', __( 'Tags' ), 'post_tags_meta_box', 'story', 'normal', 'high' );
	//add_meta_box( 'categorydiv', __( 'Categories' ), 'link_categories_meta_box', 'story', 'normal', 'high' );
} //end function





/*********************************************************************************
5. add meta boxes via cmb
*********************************************************************************/

add_filter( 'cmb_meta_boxes', 'front_page_story_info_metaboxes' );

function front_page_story_info_metaboxes( $meta_boxes ) {
	$prefix = '_cmbi_';
	
	$meta_boxes['story_meta'] = array(
		'id' => 'story_meta',
		'title' => 'Story Meta',
		'pages' => array('story'),
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true,
		'fields' => array(
			array(
				'name' => 'Story Exerpt',
				'id' => $prefix.'exerpt',
				'type' => 'text_medium',
				),
			array(
				'name' => 'Editor Pick',
				'id' => $prefix.'e_pick',
				'type' => 'radio',
				'type' => 'radio',
				'options' => array(
					'editor_pick' => __( 'Yes', 'cmb' ),
					'not_editor_pick' => __( 'No', 'cmb' )
					)
				)
			),
		);
	
	$meta_boxes['front_page_story'] = array(
		'id' => 'front_page_story',
		'title' => 'Front Page Story Options',
		'pages' => array('story'),
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