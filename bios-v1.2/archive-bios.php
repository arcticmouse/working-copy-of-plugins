<?php
/**
 * Template Name: Bios Archive
 *
 */

?>
<?php get_header(); ?>

<header class='page-title'>
	<div class='container'>
		UNT HEALTH SCIENCE CENTER  | Extraordinary Stories, Every Day
	</div>
</header>

<div class="container">
	<div class="row main-wrapper">
		<div class="main-content">

			<div class="page-header">
				<h1 class="archive_title h2">
					Bios
				</h1>
			</div>

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">

				<?php

				$post_id = get_the_ID();

				// Grab all the post meta values
				$fname      = get_post_meta( $post_id, '_cmbi_fname', true );
				$titles     = get_post_meta( $post_id, '_cmbi_titles', true);
				$phone      = get_post_meta( $post_id, '_cmbi_fphone', true);
				$fax        = get_post_meta( $post_id, '_cmbi_fax', true);
				$department = get_post_meta( $post_id, '_cmbi_department', true);
				$email      = get_post_meta( $post_id, '_cmbi_email', true);

				?>

				<div class="container-fluid">
					<div class="row-fluid">

						<div class="col-xs-12">
							<h3 class="h2"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php echo $fname . ' ' . get_the_title(); ?></a></h3>
						</div>
						<div class="col-xs-2">
							<a href="<?php the_permalink() ?>"><?php the_post_thumbnail( 'thumbnail' ); ?></a>
						</div>

						<div class="col-xs-10">
							<section class="post_content">
								<strong><?php echo $titles; ?></strong><br /><br />
								<strong>Department:</strong> <?php echo $department; ?><br /><br />
								<?php echo $phone; ?> (ph)<br />
								<?php echo $fax; ?> (fax)<br />
								<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a><br /><br />
								<a href="<?php the_permalink() ?>">View Profile</a>
							</section>
						</div>
					</div>

				</div>

				<footer>

				</footer> <!-- end article footer -->

			</article> <!-- end article -->

			<?php endwhile; ?>

			<?php if (function_exists('page_navi')) { // if expirimental feature is active ?>

				<?php page_navi(); // use the page navi function ?>

			<?php } else { // if it is disabled, display regular wp prev & next links ?>
				<nav class="wp-prev-next">
					<ul class="clearfix">
						<li class="prev-link"><?php next_posts_link(_e('&laquo; Older Entries', "bonestheme")) ?></li>
						<li class="next-link"><?php previous_posts_link(_e('Newer Entries &raquo;', "bonestheme")) ?></li>
					</ul>
				</nav>
			<?php } ?>


			<?php else : ?>

			<article id="post-not-found">
			    <header>
			    	<h1><?php _e("No Posts Yet", "bonestheme"); ?></h1>
			    </header>
			    <section class="post_content">
			    	<p><?php _e("Sorry, What you were looking for is not here.", "bonestheme"); ?></p>
			    </section>
			    <footer>
			    </footer>
			</article>

			<?php endif; ?>

		<!-- end #main -->
		</div>
				<?php get_sidebar(); // sidebar 1 ?>

			<!-- end #content -->
	</div>
</div>
<?php get_footer(); ?>