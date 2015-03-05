<?php
/**
 * Loop - Blog
 *
 * This is the loop file used on the "Blog" page template.
 *
 * @package WooFramework
 * @subpackage Template
 */
global $more; $more = 0; 

woo_loop_before(); ?>

		<div class="subFull">
			<div class="subTop">
				<div class="bigImage animated fadeInLeftBig">
					<img src="<?php bloginfo('stylesheet_directory'); ?>/images/big_building.png" />
				</div>
				
				<ul>
					<li>
						<div class="subBluShape">
							<img src="<?php bloginfo('stylesheet_directory'); ?>/images/katzyoon_blueDiag.png" />
						</div>
						<div class="subTitle">
							<div class="bluBg">
								<h1 class="animated fadeInLeft">Firm News</h1>
							</div>
						</div>
					</li>
				</ul>	
				
				<div class="subRt">	
					<!--<h2>This text must be changed in the file loop-blog.php on line 34. It can be removed, if you wish.</h2>-->
				</div> <!-- subRt -->	
			</div> <!-- subTop -->	
			
			<div class="fix"></div>
			
			<div class="subTxt">	
			<hr size="1px" />

				<?php
				// Fix for the WordPress 3.0 "paged" bug.
				$paged = 1;
				if ( get_query_var( 'paged' ) ) { $paged = get_query_var( 'paged' ); }
				if ( get_query_var( 'page' ) ) { $paged = get_query_var( 'page' ); }
				$paged = intval( $paged );
				
				$query_args = array(
									'post_type' => 'post', 
									'paged' => $paged
								);
				
				$query_args = apply_filters( 'woo_blog_template_query_args', $query_args ); // Do not remove. Used to exclude categories from displaying here.
				
				remove_filter( 'pre_get_posts', 'woo_exclude_categories_homepage', 10 );
				
				query_posts( $query_args );
						
				if ( have_posts() ) { $count = 0;
				?>

				<?php
					while ( have_posts() ) { the_post(); $count++;
				
						woo_get_template_part( 'content', 'post-on-blog-page' );
				
					} // End WHILE Loop
				} else {
					get_template_part( 'content', 'noposts' );
				} // End IF Statement
				
				woo_loop_after();
				
				woo_pagenav();
				?>
				
			</div> <!-- subTxt -->
		</div> <!-- subFull -->
