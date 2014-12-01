<?php
function get_the_data( $an_array, $the_taxonomy ) {
				foreach( $an_array as $arr ){
					$term = get_term_by( 'id', $arr, $the_taxonomy );
					$choosen[] = $term->name;
					}
				return $choosen;
			} //end function
			
			$post_id = get_the_id();
			$page_title = get_the_title();

			/*$post_thumbnail_id = get_post_thumbnail_id();
			$featured_img = wp_get_attachment_image_src( $post_thumbnail_id, 'wpbs-featured-home' );
			$featured_img = (object)array( 'url' 	=> $featured_img[0],
											'w'		=> $featured_img[1],
											'h'		=> $featured_img[2]
											 );*/
			
			$name = get_post_meta( $post_id, '_cmbi_fname', true ) . ' ';
			$name .=  $page_title . ', ';
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

			$specialty = get_post_meta( $post_id, '_cmbi_specialty', true );
			$spec = get_the_data( $specialty, 'specialty' );
			foreach( $spec as $s ){
				if ( !end( $spec ) ){
					$bs .= $s . ', ';
				} else $bs .= $s;
			}//end foreach
	
			//$job_title = get_post_meta( $post_id, '_cmbi_titles', true );
			//$department = get_post_meta( $post_id, '_cmbi_department', true );
			//$office = get_post_meta( $post_id, '_cmbi_room', true );
			//$phone = get_post_meta( $post_id, '_cmbi_fphone', true );
			//$fax = get_post_meta( $post_id, '_cmbi_fax', true );
			//$email = get_post_meta( $post_id, '_cmbi_email', true );
			//$eis = get_post_meta( $post_id, '_cmbi_eis', true );

			$featured = get_post_meta( $post_id, '_cmbi_featured', true );

			$single_bio = Timber::get_context();
			$single_bio['abios'] = new TimberPost( $post_id );
			$single_bio['bios_name'] = $name;
			$single_bio['bios_spec'] = $bs;

			if ( $featured ) {
				Timber::render( 'single-feature-bios.twig', $single_bio );
			} else {
				Timber::render( 'single-bios.twig', $single_bio );
			} //end else if featured
				//$single_bio['image'] = $featured_img['url'];
				//$single_bio['name'] = $name;
				//$single_bio['job_title'] = $job_title;
				//$single_bio['department'] = $department;
				//$single_bio['office'] = $office;
				//$single_bio['phone'] = $phone;
				//$single_bio['fax'] = $fax;
				//$single_bio['email'] = $email;
				//$single_bio['eis'] = $eis;
				//echo '<pre>';
				//print_r($single_bio);
				//echo '</pre>';
				
?>