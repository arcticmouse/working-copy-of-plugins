<?php

$context = Timber::get_context();
$context['post'] = new TimberPost(); // It's a new TimberPost object, but an existing post from WordPress.
Timber::render('single-rad.twig', $context);

?>