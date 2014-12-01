<?php
/*
Template Name: Single Bios 
*/
get_header();
/**
 * Getting all the page data $vars to use throughout the template
 */

if (have_posts()) : while (have_posts()) : the_post(); ?>
	<header class='page-title'>
		<div class='container'>
			UNT HEALTH SCIENCE CENTER  | Extraordinary Stories, Every Day
		</div>
	</header>

<div class="container">
	<div class="row main-wrapper">
		<div class="main-content">
			<header><h1 itemprop="headline"><?=$page_title?></h1>
			</header> <!-- end article header -->
			<?php require_once( 'templates/single-bios-template.php' ); ?>	
    </div>
		<?php get_sidebar(); // sidebar 1 ?>
  	</div>
</div>

<?php endwhile;
	else : echo "Post Not Found"; //Make a 404 function here
	endif;
?>




<?php get_footer(); ?>