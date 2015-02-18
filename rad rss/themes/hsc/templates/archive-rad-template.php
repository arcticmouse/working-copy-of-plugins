<?php
$args = array( 'post_type' => 'rad', 'posts_per_page' => -1 );
$context = Timber::get_context();
query_posts( $args );
$context['posts'] = Timber::get_posts();
Timber::render( 'archive-rad.twig', $context );
?>