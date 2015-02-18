<?php get_header();


/**
 * Getting all the page data $vars to use throughout the template
 */

	$page_title = get_the_title();
	$post_thumbnail_id = get_post_thumbnail_id();
	$featured_img = wp_get_attachment_image_src( $post_thumbnail_id, 'wpbs-featured-home' );
	
	if ( $featured_img ) {
	$featured_img = (object)array( "url" 	=> $featured_img[0]
											 				 , "w"		=> $featured_img[1]
															 , "h"		=> $featured_img[2]
											 );
	}


?>

	<header class='page-title'>
		<div class='container text-left'>
			<?=get_bloginfo('name')?>
            <div class="breadcrumbs">
				<?php the_breadcrumb(); ?>
            </div><!--breadcrumbs -->
		</div><!-- container -->

	</header>

<div class="container">
	<div class="row main-wrapper">
		<div class="main-content">
			<?php require_once( 'templates/archive-rad-template.php' ); ?>
    </div>
		<?php get_sidebar(); // sidebar 1 ?>
  	</div>
</div>

<?php get_footer(); ?>