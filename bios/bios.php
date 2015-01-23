<?php
/**
 * Plugin Name: BIOS
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: input data for bios listings
 * Version: 4
 * Author: Leta
 * License: A "Slug" license name e.g. GPL2
 *
 * references: http://codex.wordpress.org/Plugin_API/Action_Reference/manage_$post_type_posts_custom_column
 *
 **/
 
require_once( ABSPATH . 'wp-admin/includes/screen.php' );

add_action( 'wp_head', 'bios__css' );

function bios__css() {
	global $post_type;
	
	if ( ( $_GET['post_type'] == 'bios' ) || ( $post_type == 'bios' ) ) {
		echo "<link type='text/css' rel='stylesheet' href='" . plugins_url( 'bios_front_end.css', __FILE__ ) . "' />";
		}
}

add_action( 'admin_head', 'bios_admin_css' );

function bios_admin_css() {
	global $post_type;
	
	if ( ( $_GET['post_type'] == 'bios' ) || ( $post_type == 'bios' ) ) {
		echo "<link type='text/css' rel='stylesheet' href='" . plugins_url( 'bios.css', __FILE__ ) . "' />";
		}
}




//change edit columns
add_filter( 'manage_edit-bios_columns', 'set_custom_edit_bios_columns' );
add_action( 'manage_bios_posts_custom_column' , 'custom_bios_column', 10, 2 );

function set_custom_edit_bios_columns( $columns ) {
    unset( $columns['title'] );
 	unset( $columns['taxonomy-med_degree'] );
    unset( $columns['taxonomy-language'] );
 	unset( $columns['taxonomy-specialty'] );
    unset( $columns['taxonomy-insurance'] );
 	unset( $columns['taxonomy-location'] );
	unset( $columns['date'] );
	
    $columns['title'] = __( 'Last Name' );
    $columns['first_name'] = __( 'First Name' );
    $columns['emp_type'] = __( 'Employee Type' );
    $columns['_cmbi_featured'] = __( 'Featured' );
	$columns['date'] = __( 'Last Edit Date' );

    return $columns;
}

function custom_bios_column( $column, $post_id ) {

    switch ( $column ) {
        case 'first_name' :
            echo get_post_meta( $post_id , '_cmbi_fname' , true ); 
            break;
			
	    case 'emp_type' :
	    	$emp_type_arr = wp_get_post_terms( $post_id, 'emp_type' );
	    	if ( !empty($emp_type_arr) ) {
				foreach ( $emp_type_arr as $e ){
					echo $e->name . ' ';
					}
	    	}
            break;
			
		case '_cmbi_featured' :
			$feat = get_post_meta( $post_id , '_cmbi_featured' , true );
			if ( $feat == 1 ) 
				echo '<strong>Featured</strong>';
			break;
			
    } //end switch
}




//sortable custom columns
//soring: featured and employee types ( faculty, physician, staff )
add_filter( 'manage_edit-bios_sortable_columns', 'bios_sort_columns' );
//add_action( 'pre_get_posts', 'my_slice_orderby' );

function bios_sort_columns( $columns ) {
	$columns['_cmbi_featured'] = __( 'Featured' );
	
	return $columns;
}

function my_slice_orderby( $query ){
	if ( $query->is_main_query() && ( $orderby = $query->get( 'orderby' ) ) ) {
		$query->set( 'meta_key', '_cmbi_featured' );
		//$query->set( 'meta_value', 'orderby' );
	} //end if
}



add_action( 'admin_menu', 'remove_default_tax_metaboxes' );

function remove_default_tax_metaboxes() {
	remove_meta_box( 'tagsdiv-med_degree', 'bios', 'side' );
	remove_meta_box( 'tagsdiv-language', 'bios', 'side' );
	remove_meta_box( 'tagsdiv-specialty', 'bios', 'side' );
	remove_meta_box( 'tagsdiv-insurance', 'bios', 'side' );
	remove_meta_box( 'tagsdiv-location', 'bios', 'side' );		
}



/**
*
*create new post type for directory
*
*author: leta
**/

/**add the hook**/
add_action('init', 'create_post_type_bios');

/**create the type**/
function create_post_type_bios() {

	$current_site_id = get_current_blog_id();
	
	if( $current_site_id == 1 ) {
		$show_in_menu = true;
		} else { 
			$show_in_menu = false;
			}

	$labels = array( 
		'name' => __( 'bios' ), 
		'singular_name' => __( 'bios' ),
		'add_new' => __('Add new', 'bios'),
		'add_new_item' => __('Add new bios'),
		'edit_item' => __('Edit bios'),
		'new_item' => __('New bios'),
		'all_items' => _('All bios'),
		'view_item' => __('View bios'),
		'search_items' => ('Search bios'),
		'menu_name' => 'Bios'
		);
		
	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'show_ui' => true,
		'public' => true,
		'show_in_menu' => $show_in_menu,
		'menu_icon' => 'dashicons-admin-users',
		'has_archive' => true,
		'capability_type' => 'post',
		'rewrite' => array('slug' => 'bios'),
		'menu_position' => 5,
		'supports' => array('title', 'bios', 'bios_text', 'thumbnail', 'excerpt', 'custom-fields'),
		'taxonomies' => array('emp_type', 'med_degree', 'language', 'specialty', 'insurance', 'location')
		);

	register_post_type( 'bios', $args);
}


add_filter( 'gettext', 'custom_enter_title' );

function custom_enter_title( $input ) {

		global $post_type;

		if ( is_admin() AND ( 'Enter title here' == $input ) AND ( 'bios' == $post_type ) )
        return 'Enter bios Last Name';

    return $input;
}


add_filter( 'gettext', 'custom_featured_image' );

function custom_featured_image( $input ) {

		global $post_type;

		if ( is_admin() AND ( 'Featured Image' == $input ) AND ( 'bios' == $post_type ) )
        return 'bios Photograph';

    return $input;
}





/**
*
*register taxonomy emp_type
*
*author: leta
*
*http://codex.wordpress.org/Function_Reference/register_taxonomy
**/

/**the hook**/
add_action( 'init', 'create_emp_type_taxonomy', 0 );

/**the function**/
function create_emp_type_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Employee Type', 'taxonomy general name' ),
		'singular_name'              => _x( 'Employee Type', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Employee Types' ),
		'popular_items'              => __( 'Popular Employee Types' ),
		'all_items'                  => __( 'All Employee Types' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Employee Type' ),
		'update_item'                => __( 'Update Employee Type' ),
		'add_new_item'               => __( 'Add New Employee Type' ),
		'new_item_name'              => __( 'New Employee Type Name' ),
		'separate_items_with_commas' => __( 'Separate Employee Type with commas' ),
		'add_or_remove_items'        => __( 'Add or remove Employee Types' ),
		'choose_from_most_used'      => __( 'Choose from the most used Employee Type' ),
		'not_found'                  => __( 'No Employee Type found.' ),
		'menu_name'                  => __( 'Employee Types' ),
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		//'show_ui'               => true,
		'show_admin_column'     => false,
		'show_tagcloud'			=> true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array('slug' => 'Employee Types'),
	);

	register_taxonomy( 'emp_type', 'bios', $args );
}






/**
*
*register taxonomy degrees
*
*author: leta
*
*http://codex.wordpress.org/Function_Reference/register_taxonomy
**/

/**the hook**/
add_action( 'init', 'create_degree_taxonomy', 0 );

/**the function**/
function create_degree_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Medical Degrees', 'taxonomy general name' ),
		'singular_name'              => _x( 'Medical Degree', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Medical Degrees' ),
		'popular_items'              => __( 'Popular Medical Degrees' ),
		'all_items'                  => __( 'All Medical Degrees' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Medical Degree' ),
		'update_item'                => __( 'Update Medical Degree' ),
		'add_new_item'               => __( 'Add New Medical Degree' ),
		'new_item_name'              => __( 'New Medical Degree Name' ),
		'separate_items_with_commas' => __( 'Separate Medical Degrees with commas' ),
		'add_or_remove_items'        => __( 'Add or remove medical degrees' ),
		'choose_from_most_used'      => __( 'Choose from the most used medical degrees' ),
		'not_found'                  => __( 'No medical degrees found.' ),
		'menu_name'                  => __( 'Medical Degrees' ),
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		//'show_ui'               => true,
		'show_admin_column'     => true,
		'show_tagcloud'			=> true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array('slug' => 'Medical Degrees'),
	);

	register_taxonomy( 'med_degree', 'bios', $args );
}



/**
*
*register taxonomy language
*
*author: leta
*
*http://codex.wordpress.org/Function_Reference/register_taxonomy
**/

/**the hook**/
add_action( 'init', 'create_language_taxonomy', 0 );

/**the function**/
function create_language_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Languages', 'taxonomy general name' ),
		'singular_name'              => _x( 'Language', 'taxonomy singular name' ),
		'search_items'               => __( 'Search languagess' ),
		'popular_items'              => __( 'Popular languagess' ),
		'all_items'                  => __( 'All languagess' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Language' ),
		'update_item'                => __( 'Update Language' ),
		'add_new_item'               => __( 'Add New Language' ),
		'new_item_name'              => __( 'New Language Name' ),
		'separate_items_with_commas' => __( 'Separate Languages with commas' ),
		'add_or_remove_items'        => __( 'Add or remove languages' ),
		'choose_from_most_used'      => __( 'Choose from the most used languages' ),
		'not_found'                  => __( 'No languages found.' ),
		'menu_name'                  => __( 'Languages' ),
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		//'show_ui'               => true,
		'show_admin_column'     => false,
		'show_tagcloud'			=> true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array('slug' => 'Languages'),
	);

	register_taxonomy( 'language', 'bios', $args );
}


/**
*
*register taxonomy specialty
*
*author: leta
*
*http://codex.wordpress.org/Function_Reference/register_taxonomy
**/

/**the hook**/
add_action('init', 'create_specialty_taxonomy', 0);

/**the function**/
function create_specialty_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Specialties', 'taxonomy general name' ),
		'singular_name'              => _x( 'Specialty', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Specialties' ),
		'popular_items'              => __( 'Popular Specialties' ),
		'all_items'                  => __( 'All Specialties' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Specialty' ),
		'update_item'                => __( 'Update Specialty' ),
		'add_new_item'               => __( 'Add New Specialty' ),
		'new_item_name'              => __( 'New Specialty Name' ),
		'separate_items_with_commas' => __( 'Separate Specialties with commas' ),
		'add_or_remove_items'        => __( 'Add or remove specialties' ),
		'choose_from_most_used'      => __( 'Choose from the most used specialties' ),
		'not_found'                  => __( 'No specialties found.' ),
		'menu_name'                  => __( 'Specialties' ),
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		//'show_ui'               => true,
		'show_admin_column'     => false,
		'show_tagcloud'			=> true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array('slug' => 'Specialties'),
	);

	register_taxonomy( 'specialty', 'bios', $args );
}


/**
*
*register taxonomy insurance
*
*author: leta
*
*http://codex.wordpress.org/Function_Reference/register_taxonomy
**/

/**the hook**/
add_action( 'init', 'create_insurance_taxonomy', 0 );

/**the function**/
function create_insurance_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Insurance', 'taxonomy general name' ),
		'singular_name'              => _x( 'Insurance', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Insurance' ),
		'popular_items'              => __( 'Popular Insurance' ),
		'all_items'                  => __( 'All Insurance' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Insurance' ),
		'update_item'                => __( 'Update Insurance' ),
		'add_new_item'               => __( 'Add New Insurance' ),
		'new_item_name'              => __( 'New Insurance Name' ),
		'separate_items_with_commas' => __( 'Separate Insurance with commas' ),
		'add_or_remove_items'        => __( 'Add or remove insurance' ),
		'choose_from_most_used'      => __( 'Choose from the most used insurance' ),
		'not_found'                  => __( 'No insurance found.' ),
		'menu_name'                  => __( 'Insurance' ),
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		//'show_ui'               => true,
		'show_admin_column'     => false,
		'show_tagcloud'			=> true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array('slug' => 'Insurance'),
	);

	register_taxonomy( 'insurance', 'bios', $args );
}


/**
*
*register taxonomy locations & add meta fields to the taxonomy
*
*author: leta
*
*http://codex.wordpress.org/Function_Reference/register_taxonomy
*https://pippinsplugins.com/adding-custom-meta-fields-to-taxonomies/
**/

/**the hook**/
add_action( 'init', 'create_location_taxonomy', 0 );


/**the function**/
function create_location_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Location', 'taxonomy general name' ),
		'singular_name'              => _x( 'Location', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Locations' ),
		'popular_items'              => __( 'Popular Locations' ),
		'all_items'                  => __( 'All Locations' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Location' ),
		'update_item'                => __( 'Update Location' ),
		'add_new_item'               => __( 'Add New Location' ),
		'new_item_name'              => __( 'New Location Name' ),
		'separate_items_with_commas' => __( 'Separate locations with commas' ),
		'add_or_remove_items'        => __( 'Add or remove location' ),
		'choose_from_most_used'      => __( 'Choose from the most used locations' ),
		'not_found'                  => __( 'No locations found.' ),
		'menu_name'                  => __( 'Locations' ),
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		//'show_tagcloud'			=> true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array('slug' => 'Location'),
	);

	register_taxonomy( 'location', 'bios', $args );
}


/**
*
* add custom field to taxonomy
*
* https://pippinsplugins.com/adding-custom-meta-fields-to-taxonomies/
*
**/
add_action( 'location_add_form_fields', 'pippin_taxonomy_add_new_meta_field', 10, 2 );
add_action( 'location_edit_form_fields', 'pippin_taxonomy_edit_meta_field', 10, 2 );

add_action( 'edited_location', 'save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_location', 'save_taxonomy_custom_meta', 10, 2 );
add_action( 'gettext_with_context', 'change_description_locations', 20, 3);

// Add term page
function pippin_taxonomy_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
	?>
	<div class="form-field">
		<label for="term_meta[street_address]"><?php _e( 'Street Address', 'pippin' ); ?></label>
		<input type="text" name="term_meta[street_address]" id="term_meta[street_address]" value="">
		<p class="description"><?php _e( 'Enter a value for this field','pippin' ); ?></p>
	</div>
	<div class="form-field">
		<label for="term_meta[suite_number]"><?php _e( 'Suite Number', 'pippin' ); ?></label>
		<input type="text" name="term_meta[suite_number]" id="term_meta[suite_number]" value="">
	</div>
	<div class="form-field">
		<label for="term_meta[city]"><?php _e( 'City', 'pippin' ); ?></label>
		<input type="text" name="term_meta[city]" id="term_meta[city]" value="">
	</div>
	<div class="form-field">
		<label for="term_meta[state]"><?php _e( 'State', 'pippin' ); ?></label>
		<input type="text" name="term_meta[state]" id="term_meta[state]" value="">
	</div>
	<div class="form-field">
		<label for="term_meta[zip_code]"><?php _e( 'Zip Code', 'pippin' ); ?></label>
		<input type="text" name="term_meta[zip_code]" id="term_meta[zip_code]" value="">
	</div>
	<div class="form-field">
		<label for="term_meta[phone_number]"><?php _e( 'Phone Number', 'pippin' ); ?></label>
		<input type="text" name="term_meta[phone_number]" id="term_meta[phone_number]" value="">
		<p class="description"><?php _e( 'Enter with area code first XXX-XXX-XXXX','pippin' ); ?></p>
	</div>
	<div class="form-field">
		<label for="term_meta[fax_number]"><?php _e( 'Fax Number', 'pippin' ); ?></label>
		<input type="text" name="term_meta[fax_number]" id="term_meta[fax_number]" value="">
		<p class="description"><?php _e( 'Enter with area code first XXX-XXX-XXXX','pippin' ); ?></p>
	</div>
	<div class="form-field">
		<label for="term_meta[manager]"><?php _e( 'Office Contact/Manager', 'pippin' ); ?></label>
		<input type="text" name="term_meta[manager]" id="term_meta[manager]" value="">
		<p class="description"><?php _e( 'Enter first and last name','pippin' ); ?></p>
	</div>
<?php
}

// Edit term page
function pippin_taxonomy_edit_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[street_address]"><?php _e( 'Street Address', 'pippin' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[street_address]" id="term_meta[street_address]" value="<?php echo esc_attr( $term_meta['street_address'] ) ? esc_attr( $term_meta['street_address'] ) : ''; ?>">
			<p class="description"><?php _e( 'Enter street number and name','pippin' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[suite_number]"><?php _e( 'Suite Number', 'pippin' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[suite_number]" id="term_meta[suite_number]" value="<?php echo esc_attr( $term_meta['suite_number'] ) ? esc_attr( $term_meta['suite_number'] ) : ''; ?>">
		</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[city]"><?php _e( 'City', 'pippin' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[city]" id="term_meta[city]" value="<?php echo esc_attr( $term_meta['city'] ) ? esc_attr( $term_meta['city'] ) : ''; ?>">
		</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[state]"><?php _e( 'State', 'pippin' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[state]" id="term_meta[state]" value="<?php echo esc_attr( $term_meta['state'] ) ? esc_attr( $term_meta['state'] ) : ''; ?>">
		</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[zip_code]"><?php _e( 'Zip Code', 'pippin' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[zip_code]" id="term_meta[zip_code]" value="<?php echo esc_attr( $term_meta['zip_code'] ) ? esc_attr( $term_meta['zip_code'] ) : ''; ?>">
		</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[phone_number]"><?php _e( 'Phone Number', 'pippin' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[phone_number]" id="term_meta[phone_number]" value="<?php echo esc_attr( $term_meta['phone_number'] ) ? esc_attr( $term_meta['phone_number'] ) : ''; ?>">
			<p class="description"><?php _e( 'Enter with area code first XXX-XXX-XXXX','pippin' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[fax_number]"><?php _e( 'Fax Number', 'pippin' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[fax_number]" id="term_meta[fax_number]" value="<?php echo esc_attr( $term_meta['fax_number'] ) ? esc_attr( $term_meta['fax_number'] ) : ''; ?>">
			<p class="description"><?php _e( 'Enter with area code first XXX-XXX-XXXX','pippin' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[manager]"><?php _e( 'Office Contact/Manager', 'pippin' ); ?></label></th>
		<td>
			<input type="text" name="term_meta[manager]" id="term_meta[manager]" value="<?php echo esc_attr( $term_meta['manager'] ) ? esc_attr( $term_meta['manager'] ) : ''; ?>">
			<p class="description"><?php _e( 'Enter first and last name','pippin' ); ?></p>
		</td>
	</tr>
<?php
}

// Save extra taxonomy fields callback function.
function save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
} 


function change_description_locations($translated, $text, $context) {
		$screen = get_current_screen();

	    if ( is_admin() AND ( $text == "Description" ) AND ( $context == "Taxonomy Description" ) AND ( $screen->taxonomy == "location" ) )
        return __("Office Hours");

    return $translated;
}

?>