<?php
/*****************************************************************************
	Plugin Name: check images
	Plugin URI: 
	Description: check to see if linked image is on the server
	Version: 1
	Author: leta
	Author URI: 
	License: 

	1. get image from post
	2. check if image is in server
	3. if not, print post id, post title, image file name
*****************************************************************************/



add_filter( 'init', 'check_images' );

function check_images() {
	$query = new WP_Query( 'post_type=story' );
#echo '<pre>';
#print_r($query);
#echo '</pre>';

	if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); 

			if ( has_post_thumbnail() ) {
				the_title();
				echo '<br>';
				$aurl = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );
				echo $aurl[0];
				echo '<br>';
			} //end if thumbnail
			echo '<br>';

		endwhile;
	endif;

	wp_reset_postdata();

} //end function check_images
?>