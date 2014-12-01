<?php
/*
Template Name: Newsroom Homepage
*/
get_header(); ?>

<div id="fb-root"></div>

<style>
#twitter-widget-0 {
    width: 100% !important;
}
.panel-heading .panel-title {
  background-color: #c0392b;
  color: #fff;
  padding: 18px;
}
.feed a {
	color: #000000;
	padding-top: 0;
}
.news-story a h4 {
	color: #000000;
}
</style>

<div class="news">
  <div id="content" class="home-news">
  	<div id="main" class="" role="main">
  		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    		<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">
    			<header class='page-title-breadcrumbs'>
    				<div class='container'>
    					<?=get_bloginfo('name')?>
              <div class="breadcrumbs">
                <?php the_breadcrumb(); ?>
              </div>
    				</div>
    			</header>
				
          <?
		  /**************************************/
		  //editors picks tab
		  /**************************************/
		  $query = new WP_Query( array( 'meta_value' => 'editor_pick', 'post_type' => 'story' ) );
		  if ( $query->have_posts() ){
			$k = 0;
			while( $query->have_posts() ) : $query->the_post();
				$epick[$k]['title'] = get_the_title();
				$epick[$k]['url'] = get_permalink();
					$ee = get_post_meta( get_the_ID(), '_cmbi_exerpt', true );
				$epick[$k]['excerpt'] = get_post_meta( get_the_ID(), '_cmbi_exerpt', true );
					if ( has_post_thumbnail() ){
						$epick[$k]['thumb'] = the_post_thumbnail();
					} else {
						$epick[$k]['thumb'] = 'http://qa.untmed.org/wp-content/uploads/2014/07/hiDefCampus-150x150.jpg';
					} //end if else
				
					$eptags = get_the_tags( get_the_ID(), 'post_tag' );
					foreach( $eptags as $ep ){
						$eptaglist[] = $ep->name;
					}
				$epick[$k]['tags'] = $eptaglist;
				$k++;
				endwhile;
		  } //end if
		  
		  
		  
		  //send it to timber
          $context = Timber::get_context();
          // Lots of stuff happens in this function /library/card-control/card-control.php
          $context['astory'] = new TimberPost( get_the_ID );
		  $context['thenews'] = Timber::get_posts( array( 'post_type' => 'story', 'posts_per_page' => 4 ) );
		  $context['research'] = Timber::get_posts( array( 'post_type' => 'story', 'flag' => 'Research' ) );
		  $context['education'] = Timber::get_posts( array( 'post_type' => 'story', 'flag' => 'Education' ) );
		  $context['patientcare'] = Timber::get_posts( array( 'post_type' => 'story', 'flag' => 'Patient-Care' ) );
		  $context['editor_pick'] = $epick;
          Timber::render('single-story.twig', $context);
          ?>
    		</article> <!-- end article -->
  		<?php endwhile; ?>
  		<?php else : ?>
    		<article id="post-not-found">
    		    <header>
    		    	<h1><?php _e("Not Found", "bonestheme"); ?></h1>
    		    </header>
    		    <section class="post_content">
    		    	<p><?php _e("Sorry, but the requested resource was not found on this site.", "bonestheme"); ?></p>
    		    </section>
    		    <footer>
    		    </footer>
    		</article>
  		<?php endif; ?>
  	</div> <!-- end #main -->
  </div> <!-- end #content -->
</div>
<?php get_footer(); ?>