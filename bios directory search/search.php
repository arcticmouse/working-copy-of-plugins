<?php get_header(); ?>

			<div id="content" class="clearfix row-fluid">
				<div id="main" class="span8 clearfix" role="main">
					<div class="page-header search-header"><div class="container"><h1 class="">Search Results</h1> <p>HOME &raquo; SEARCH RESULTS</p></div></div>

						<header>
						</header> <!-- end article header -->

						<div class="container">
							<div class="row search-results-main">
								<div class="col-md-2">
									<ul>
										<li><a href="">Directory</a></li>
										<li><a href="">Maps</a></li>
										<li><a href="">Useful Links</a></li>
									</ul>
								</div>
								<div class="col-md-10">

									<?php 
								    if ( !isset( $_GET['post_type'] ) ) {
								    	?>

										<h3>Search UNT Health Sciences</h3>

										<form class="search-results-form" role="search" method="get" id="searchform" action="/">
									    <div class="form-group row">
									      <div class="col-sm-10">
									        <input type="text" value="" name="s" id="s" placeholder="<?= get_search_query(); ?>">
									      </div>
									      <div class="col-sm-2">
									        <button type="submit" class="btn btn-block btn-primary">Search</button>
									      </div>
									    </div>
									    </form>

										<!-- begin Google search results -->
										<gcse:searchresults-only queryParameterName="s"></gcse:searchresults-only>
										<!-- end Google search results -->
										<?php
									} else {

										?><h3>UNT Health Sciences Biographies Search Results</h3><br /><br /><?php
										#get data from form
										$fname = $_GET['fname'];
										$lname = $_GET['s'];
										$type_arr = $_GET['emp_type'];
										$dept = $_GET['department'];

										#set search variables if thats what the user is looking for 
										#special case for post title, which isnt post_meta
										#put a filter on lname and fname, which are user strings
										if( $fname ){
											$fn = sanitize_text_field( $fname );
											$fname_arr = array(
										            'key' => '_cmbi_fname',
										            'value' => $fn,
										            'compare' => 'LIKE'
										        	);
										}
										if ( $lname ){
											$ln = sanitize_text_field( $lname );
											$title_query = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE UCASE(post_title) LIKE '$ln%' OR UCASE(post_title) LIKE '%$ln%'" );
										}
										if ( in_array( 'faculty', $type_arr ) ){
											$type_arr_fac = array(
													'key' => '_cmbi_emp_type_faculty',
													'value' => 'on',
													'compare' => '='
												);
										}
										if ( in_array( 'staff', $type_arr ) ){
											$type_arr_staff = array(
													'key' => '_cmbi_emp_type_staff',
													'value' => 'on',
													'compare' => '='
												);
										}
										if ( $dept ){
											$dept_arr = array(
										            'key' => '_cmbi_department',
										            'value' => $dept,
										            'compare' => 'LIKE'
										        );
										}

										#set args to query for
										$args = array(
										    'meta_query' => array(
										        'relation' => 'OR',
										        $fname_arr,
										        $type_arr_fac,
										        $type_arr_staff,
										        $dept_arr,
										    ), //end meta query
										    'post_type' => 'bios',
										    'post__in' => $title_query,
										); //end args

										#query for args
										$query = new WP_Query( $args );

										#if there are results loop through them and set variables to print
							       		if ( $query->have_posts() ){
											$k = 0;
											while( $query->have_posts() ) : $query->the_post();
												$post_id = get_the_ID();
												$results[$k]['thumb']	   = get_the_post_thumbnail();
												$results[$k]['url']		   = get_the_permalink( $post_id );
												$results[$k]['lname']	   = get_the_title();
												$results[$k]['fname']      = get_post_meta( $post_id, '_cmbi_fname', true );
												$results[$k]['titles']     = get_post_meta( $post_id, '_cmbi_titles', true);
												$results[$k]['phone']      = get_post_meta( $post_id, '_cmbi_fphone', true);
												$results[$k]['fax']        = get_post_meta( $post_id, '_cmbi_fax', true);
												$results[$k]['department'] = get_post_meta( $post_id, '_cmbi_department', true);
												$results[$k]['email']      = get_post_meta( $post_id, '_cmbi_email', true);
												$k++;
											endwhile;
										} 

										#send everything to the lil twig to print out
								        $context = Timber::get_context();
										$context['results'] = $results;
								        Timber::render('bios-search-results.twig', $context);

								        #reset the query
								        wp_reset_query();
									} // if else
									?>

								</div>
							</div>
						</div><!-- end container -->

						<footer>
						</footer> <!-- end article footer -->

				</div> <!-- end #main -->

			</div> <!-- end #content -->

<?php get_footer(); ?>