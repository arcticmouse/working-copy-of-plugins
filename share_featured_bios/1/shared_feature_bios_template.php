<?php
/*
Template Name: Shared Feature Bios Template
*/
?>

<?php get_header(); ?>

	<style>
	.bios_profile {
	    width: 100%;
	}
	.bios_thumb {
	    height: 18em;
	    position: relative;
	    width: 100%;
	}
	.bios_quote {
	    float: right;
	    margin: -15em 31em 0;
	    position: absolute;
	    width: 25%;
	}
	.bios_name_and_spec {
	    background-color: #e5e5e5;
	    font-size: 0.9em;
	    overflow: auto;
	    padding: 0.75em;
	}
	.bios_contact {
	    background-color: #c0392b;
	    color: #ffffff;
	    font-size: 0.75em;
	    padding: 0.5em;
	}
	.bios_big_text {
	    font-size: 0.9em;
	    overflow: auto;
	    padding: 0.75em;
	    width: 100%;
	}
	</style>	

<header class='page-title'>
	<div class='container'>
		UNT HEALTH SCIENCE CENTER  | Extraordinary Stories, Every Day
	</div>
</header>

<div class="container">
	<div class="row main-wrapper">
		<div class="main-content">
			
			<?php 
			$post_id = $post->post_content;

			switch_to_blog(1); 

			?>	
			
			<article id="post-<?php echo $post_id; ?>" <?php post_class('clearfix'); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">
				<?php
				
					//function to get taxonomy terms
					//put after calling switch to blog JUST IN CASE ... we want to use taxonomies in blog(1), not in current blog
					function get_the_data( $an_array, $the_taxonomy ) {
						foreach( $an_array as $arr ){
							$term = get_term_by( 'id', $arr, $the_taxonomy );
							$choosen[] = $term->name;
							}
						return $choosen;
						} //end function
						
					//function to explode strings to put paragraphs on new lines
					function explode_the_string($string) {
						$stringArr = explode("\n", $string);
						foreach($stringArr as $str) {
							echo $str . "<br />";
							}
					}
				
				
						$fname = get_post_meta( $post_id, '_cmbi_fname', true );
						$quote = get_post_meta( $post_id, '_cmbi_quote', true);
						$interests = get_post_meta( $post_id, '_cmbi_research_interests', true);
						$biography = get_post_meta( $post_id, '_cmbi_biography', true);
						$academics = get_post_meta( $post_id, '_cmbi_academics', true);
						$honors = get_post_meta( $post_id, '_cmbi_honors', true);
						$titles = get_post_meta( $post_id, '_cmbi_titles', true);
						$email = get_post_meta( $post_id, '_cmbi_email', true);
						
						$primary_clinic_number = get_post_meta( $post_id, '_cmbi_primary', true);
						$degree = get_post_meta( $post_id, '_cmbi_med_degree' );
						$specialty = get_post_meta( $post_id, '_cmbi_specialty' );
						$type = get_post_meta( $post_id, '_cmbi_bios_type');
						
						$primary_clinic = get_the_data( $primary_clinic_number, 'location' );
						$degree = get_the_data( $degree[0], 'med_degree' );
						$specialty = get_the_data( $specialty[0], 'specialty' );
							
					?>
					
					<div class="bios_profile container-fluid">
						<div class="row-fluid">
						<?php 
							echo get_the_post_thumbnail( $post_id, 'large', array( 'class' => 'col-xs bios_thumb')); 
						?>
						<div class="col-xs-3 col-xs-offset-9 bios_quote">
							<?php 
								echo $quote; 
							?>
						</div>
						<br />

						<div class="row-fluid bios_name_and_spec">
							<div class="col-xs-7">
								<strong>
								<?php 
								echo $fname . ' ' . get_the_title( $post_id ) . ', '; 
								foreach($degree as $deg) {
									if (!end($degree)) {
										echo $deg . ", ";
									} else {
										echo $deg;
										}
									} //end foreach
								foreach( $specialty as $spec ) {
									echo ", " . $spec;
									} //end foreach
								?></strong><br />
								<?php
								explode_the_string($titles);
								?>
							</div>
							<div class="clearfix-col-xs-4 col-xs-offset-8 bios_contact">
								For an appointment: <strong>817-735-DOCS(3627)</strong>
								<br />
								<?php echo $primary_clinic[0]; 
								?><br /><?php
								$term_id = $primary_clinic_number[0];
								$t_id = $term_id;
								$term_meta = get_option( "taxonomy_$t_id" );
								echo $term_meta[phone_number];
								?><br /><?php
								echo $email; 
								?>
							</div>
							
						</div>
						
						<br />
						<div class="row-fluid bios_big_text">
							<div class="col-xs-12 col-sm-6">
								<strong>Clinical specialty: </strong><em>
									<?php 
									foreach( $specialty as $spec ) {
										echo $spec . ",";
											} //end foreach 
								?></em>
								<br /><br />
								<strong>Research interests: </strong><em><?php echo $interests; ?></em>
								<br /><br />
								<?php 
								explode_the_string($biography); 
								?>
								<br /><br />
							</div>
							<div class="col-xs-12 col-sm-6">
								<strong>Academic:</strong><br />
								<?php echo $academics; ?>
								<br /><br />
								<strong>Honors:</strong><br />
								<?php echo $honors; ?>
							</div>
						</div>
					</div>

			</article> <!-- end article -->
				
			<?php  restore_current_blog();   ?>
		
    </div>
		<?php get_sidebar(); // sidebar 1 ?>
  	</div>
</div>

<?php get_footer(); ?>