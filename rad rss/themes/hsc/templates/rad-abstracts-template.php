<?php
/*
Template Name: RAD Abstracts Template 
Description: lists all categories
Author: Leta
*/
?>
<?php get_header();


/**
 * Getting all the page data $vars to use throughout the template
 */

    $page_title = get_the_title();
    $post_thumbnail_id = get_post_thumbnail_id();
    $featured_img = wp_get_attachment_image_src( $post_thumbnail_id, 'wpbs-featured-home' );
    
    if ( $featured_img ) {
    $featured_img = (object)array( "url"    => $featured_img[0]
                                                             , "w"      => $featured_img[1]
                                                             , "h"      => $featured_img[2]
                                             );
    }


?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
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
            <header><h1><?=$page_title?></h1></header>
            <? if($featured_img) { ?>
                <img src="<?=$featured_img->url?>" class='featured' alt="">
            <? } ?>
            <?php
                //get cpt rad posts
                $args = array(
                        'posts_per_page' => -1,
                        'post_type' => 'rad',
                    );
                $posts = get_posts( $args );

                //get categories for rad posts
                $args = array(
                        'exclude' => 1,
                    );
                $cats = get_terms( 'category', $args );

                //get names for rad posts
                $names = get_terms( 'post_tag' );

                //print categories
                if ( $cats ){
                    foreach( $cats as $c ){
                        echo $c->name;
                        echo '<br />';
                    }
                } //end if cats exists

                //print titles of abstracts
                if( $posts ){
                    foreach( $posts as $p ){
                        echo $p->post_title;
                        echo '<br>';
                    }
                } //end exists posts



            ?>
    </div>
        <?php get_sidebar(); // sidebar 1 ?>
    </div>
</div>




<?php endwhile;
    else : echo "Post Not Found"; //Make a 404 function here
    endif;
?>




<?php get_footer(); ?>