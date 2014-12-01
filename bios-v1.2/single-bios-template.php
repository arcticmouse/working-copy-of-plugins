<?php
function get_the_data( $an_array, $the_taxonomy ) {
				foreach( $an_array as $arr ){
					$term = get_term_by( 'id', $arr, $the_taxonomy );
					$choosen[] = $term->name;
					}
				return $choosen;
			} //end function



function bios_shared_featured_css() {
	echo 'blah';
	echo "<link type='text/css' rel='stylesheet' href='" . plugins_url() . "/bios-v1.2/bios_front_end.css' />";
}






$post_id = get_the_id();


//if this be a page
if ( get_post_type( $post_id ) == 'page' ) {
	add_action( 'wp_footer', 'bios_shared_featured_css' );
	$page = get_page( $post_id );
	$content = get_the_content();
	$post_id = (int) $content;
	switch_to_blog(1);
	$switched = true;
}


//get thumbnail
$image = get_the_post_thumbnail( $post_id );


//create title name
$name = get_post_meta( $post_id, '_cmbi_fname', true ) . ' ';
$name .=  get_the_title() . ', ';
$name .= get_post_meta( $post_id, '_cmbi_non_med_degree', true);
	
$med = get_post_meta( $post_id, '_cmbi_med_degree', true);				
$degrees = get_the_data( $med[0], 'med_degree' );
	
if ( !empty($degrees) ){
	$name .= ', ';
	foreach( $degrees as $deg ) {
		if ( !end( $degrees ) ) {
			$name .= $deg . ", ";
			} else {
				$name .= $deg;
				} //end if else
		} //end foreach
} //end if


//get specialty list
$specialty = get_post_meta( $post_id, '_cmbi_specialty', true );
$spec = get_the_data( $specialty, 'specialty' );
foreach( $spec as $s ){
	if ( !end( $spec ) ){
		$bs .= $s . ', ';
	} else $bs .= $s;
}//end foreach


//get quote
$quote = '<strong>' . get_post_meta( $post_id, '_cmbi_quote', true ) . '</strong>';
if ( $quote ) {
	$quote .= '<br />-' . $name;
} else $quote = '';

$fname = get_post_meta( $post_id, '_cmbi_fname', true );
$qc = get_post_meta( $post_id, '_cmbi_quote_color', true );
$titles = get_post_meta( $post_id, '_cmbi_titles', true );
$dept = get_post_meta( $post_id, '_cmbi_department', true );
$fphone = get_post_meta( $post_id, '_cmbi_fphone', true );
$email = get_post_meta( $post_id, '_cmbi_email', true );
$ri = get_post_meta( $post_id, '_cmbi_research_interests', true );
$biog = get_post_meta( $post_id, '_cmbi_biography', true );
$acad = get_post_meta( $post_id, '_cmbi_academics', true );
$hon = get_post_meta( $post_id, '_cmbi_honors', true );
$eis = get_post_meta( $post_id, '_cmbi_eis', true );

$featured = get_post_meta( $post_id, '_cmbi_featured', true );

//not using new TimberPost because this template is uses data in more than 1 blog (MULTISITE), and passing a post only works within 1 blog
$single_bio = Timber::get_context();
#$single_bio['abios'] = new TimberPost( $post_id );
$single_bio['bios_img'] = $image;
$single_bio['bios_name'] = $name;
$single_bio['bios_spec'] = $bs;
$single_bio['bios_quote'] = $quote;
$single_bio['bios_fname'] = $fname;
$single_bio['bios_quote_color'] = $qc;
$single_bio['bios_titles'] = $titles;
$single_bio['bios_department'] = $dept;
$single_bio['bios_fphone'] = $fphone;
$single_bio['bios_email'] = $email;
$single_bio['bios_research_interests'] = $ri;
$single_bio['bios_biography'] = $biog;
$single_bio['bios_academics'] = $acad;
$single_bio['bios_honors'] = $hon;
$single_bio['bios_eis'] = $eis;

if ( $featured ) {
	Timber::render( 'single-feature-bios.twig', $single_bio );
} else {
	Timber::render( 'single-bios.twig', $single_bio );
} //end else if featured	

if ( $switched ) {
	restore_current_blog();
	}
?>