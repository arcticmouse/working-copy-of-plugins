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
.facebook .content, .twitter .content {
    height: 100%;
    overflow-x: auto;
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
		  //get the twitter feed
		  /**************************************/
		  /**
		   * Carrie's note to whoever comes along next:
		   *
		   * The following code chunk utilizes TwitterWP, which is located in the hsc/library/ file and
		   * called in the hsc/functions.php file
		   *
		   * [https://github.com/jtsternberg/TwitterWP]
		   *
		   * The code isn't very well documented, so honestly not sure if you'll opt to use it. It uses the WordPress
		   * HTTP API  [http://codex.wordpress.org/HTTP_API] to connect to the Twitter API and let's you pull out
		   * one slice of data at a time (i.e. the tweet, the date), which makes styling much easier than just using
		   * the Twitter Embedded Widget
		   *
		   * Your Twitter app credentials came from here -> https://apps.twitter.com/app/6893751/keys (requires a
		   * login to the UNTHSC Twitter account)
		   *
		   * The code below obviously works for this page only. Next step would be to wrap it in a function and hook
		   * it wherever you want (not sure if you want this conditionally on certain pages, but that'd be better if
		   * you don't need to show a tweet on every page)
		   */

		  // app credentials (must be in this order)
		  $app = array(
		    'consumer_key' => 'N5zVgDHtCOIncu7rOrpI5HI1L', //Twitter API Key
		    'consumer_secret' => '8Lec12UcsYgglgMR759HuoNqaq9KAZLEgLh0TS1TG9CW2aO5uP', //Twitter API Secret
		    'access_token' => '29803896-Np4MQ4YKXNi3r03ZXWOwjna9s7zVpDdIZ2ciTIPnQ',
		    'access_token_secret' => 'c7Z4FzY0tZAdZgjowuXgWCvORQCnHBq22hKm9VJjabi4r',
		  );

		  // initiate your app
		  $tw = TwitterWP::start( $app );

		  $user   = 'unthsc';
		    // bail here if the user doesn't exist
		    if ( ! $tw->user_exists( $user ) )
		      return;

		  $latest    = $tw->get_tweets( $user, 20 ); // change the number to number of tweets you want returned
		  //$created   = $latest[0]->created_at;
		  //$created   = strtotime( $created );
		  //$created   = human_time_diff( $created, current_time( 'timestamp' ) );
		  //$update    = $latest[0]->text;
		  foreach( $latest as $late ){
		  	$twitterfeed[] = $late->text;
		  }
		  
		  
		  /**************************************/
		  //get the facebook feed
		  /**************************************/
		  function fetchUrl($url){
			   $ch = curl_init();
			   curl_setopt($ch, CURLOPT_URL, $url);
			   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			   curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			   // You may need to add the line below
			   // curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);

			   $feedData = curl_exec($ch);
			   curl_close($ch); 

			   return $feedData;
		  }

		  $profile_id = "57899672077";
		  //App Info, needed for Auth
		  $app_id = "736498876420720";
		  $app_secret = "cb8271cae83b14f4e61605aeae082486";
		  //Retrieve auth token
		  $authToken = fetchUrl("https://graph.facebook.com/oauth/access_token?grant_type=client_credentials&client_id={$app_id}&client_secret={$app_secret}");
		  $json_object = fetchUrl("https://graph.facebook.com/{$profile_id}/feed?{$authToken}");
		  $feedarray = json_decode($json_object);
		  foreach ( $feedarray->data as $feed_data )
		  {
		      $fbfeed[] = "{$feed_data->message}";
		  }

		  
		  /**************************************/
		  //get the external rss feed
		  /**************************************/
		  $feed = fetch_feed( 'http://hosted.ap.org/lineups/TOPHEADS.rss?SITE=AP&SECTION=HOME' );
		  $rssitems = $feed->get_item_quantity(4);
		  $thefeed = $feed->get_items( 0, $rssitems );
		  foreach ( $thefeed as $f ){
			$title = $f->get_title();
			$url = $f->get_permalink();
			$string = explode( '-- ', $f->get_description() );
			$str = substr( $string[1], 0, 75) . '...';
			$line = '<a href="'. $url .'"><strong>'. $title .'</strong></a>';
			$rss[] = $line;
		  }
		  
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
          $context['cards'] = Cards::prepare_homepage_cards();
		  $context['thenews'] = Timber::get_posts( array( 'post_type' => 'story', 'posts_per_page' => 4 ) );
          $context['rssfeed'] = $rss;
		  $context['twitterfeed'] = $twitterfeed;
		  $context['fbfeed'] = $fbfeed;
		  $context['research'] = Timber::get_posts( array( 'post_type' => 'story', 'flag' => 'Research' ) );
		  $context['education'] = Timber::get_posts( array( 'post_type' => 'story', 'flag' => 'Education' ) );
		  $context['patientcare'] = Timber::get_posts( array( 'post_type' => 'story', 'flag' => 'Patient-Care' ) );
		  $context['editor_pick'] = $epick;
          Timber::render('newsroom-cards.twig', $context);
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