<?php
/**
 * Page Content Template
 *
 * This template is the default page content template. It is used to display the content of the
 * `page.php` template file, contextually, as well as in archive lists or search results.
 *
 * @package WooFramework
 * @subpackage Template
 */

/**
 * Settings for this template file.
 *
 * This is where the specify the HTML tags for the title.
 * These options can be filtered via a child theme.
 *
 * @link http://codex.wordpress.org/Plugin_API#Filters
 */
 global $woo_options;

 $heading_tag = 'h1';
 if ( is_front_page() ) { $heading_tag = 'h2'; }
 $title_before = '<' . $heading_tag . ' class="title">';
 $title_after = '</' . $heading_tag . '>';

 $page_link_args = apply_filters( 'woothemes_pagelinks_args', array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) );

 woo_post_before();
?>
<article <?php post_class(); ?>>
<?php
	woo_post_inside_before();
?>
	<header>
		<?php the_title( $title_before, $title_after ); ?>
	</header>

	<section class="entry">

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
								<h1 class="animated fadeInLeft"><?php the_title(); ?></h1>
							</div>
						</div>
					</li>
				</ul>	
				
				<div class="subRt">	
					<h2><?php echo (types_render_field("page-header", array())); ?></h2>
				</div> <!-- subRt -->	
			</div> <!-- subTop -->	
			
			<div class="fix"></div>
			
			<div class="subTxt">	
			<hr size="1px" />
  
			    <?php
			    
			    	if ( is_page('about-us') ) {
			    		echo '<div id="au-left">';
			    			the_content();
			    		echo '</div>';
			    	} else {			    	
			    		the_content();
			    	}
			    
			    	if ( is_page('about-us') ) {
			    		echo '<div id="newsflash">';
			    			echo '<div id="newsflashtop">Firm News</div>';
			    			
			    			$args1 = array( 'post_type' => 'post', 'orderby'=> 'date', 'order' => 'ASC', 'showposts' => 3 );	
							$posties = new WP_Query($args1);
							
			    				while ( $posties->have_posts() ) : $posties->the_post();
									echo '<div class="newsflashitem">';
										echo '<div class="newsflashdate">';
											echo the_date();
										echo '</div>';
										echo '<div class="newsflashtitle">';
											echo '<a href="';
											echo the_permalink();
											echo '">';
											echo the_title();
											echo '</a>';
										echo '</div>';
									echo '</div>';
								endwhile;
								
							wp_reset_query();
							
							echo '<div id="newsflashmore"><a href="';
							bloginfo('url');
							echo '/firm-news"><span>view all </span><img src="'. get_stylesheet_directory_uri() .'/images/newsflasharrow.jpg"></a></div>';
			    		echo '</div>';
			    	}
			    
			    	
			    	
			    	if ( $woo_options['woo_post_content'] == 'content' || is_singular() ) wp_link_pages( $page_link_args );
			    	
			    	if ( is_page( 'our-team' ) ) {
						if ( function_exists( 'get_roster_slider' ) ) { 
							get_roster_slider(); 
						}
					}
					
					if ( is_page( 'practice-areas' ) ) {
						$args2 = array( 'post_type' => 'practice-area', 'orderby'=> 'date', 'order' => 'ASC', 'showposts' => 300 );	
						$snippet = new WP_Query($args2);
								
								while ( $snippet->have_posts() ) : $snippet->the_post();
									print '<h3>';
										echo the_title();
									print '</h3>';
									echo the_content();
								endwhile;

						wp_reset_query();
					}
					
			    ?>
				
			
			</div> <!-- subTxt -->
		</div> <!-- subFull -->
				
	</section><!-- /.entry -->
	<div class="fix"></div>
	

</article><!-- /.post -->

		<?php if ( is_page('about-us') ) { ?>
			<br><br>
			<div class="whitebox"></div>
			<div id="about-logos">
				<ul>
					<div class="fifty">
						<li><a href="http://www.martindale.com/" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/preem.png" title="PREEMINENT Martindale-Hubbell Lawyer Ratings" alt="PREEMINENT Martindale-Hubbell Lawyer Ratings"></a></li>
						
						<li><a href="http://www.superlawyers.com/" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/superlaw.png" title="Super Lawyers" alt="Super Lawyers"></a></li>
					</div>
					
					<div class="fifty">
						<li><a href="http://www.bestlawyers.com/" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/bestlaw.png" title="Best Lawyers" alt="Best Lawyers"></a></li>
						
						<li><a href="http://www.litcounsel.org/index.htm" target="_blank"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/fellow.png" title="FELLOW Litigation Counsel of America" alt="FELLOW Litigation Counsel of America"></a></li>
					</div>
				</ul>
			</div>
		<?php } else {} ?>

<?php
	/*woo_post_after();
	$comm = get_option( 'woo_comments' );
	if ( ( $comm == 'page' || $comm == 'both' ) && is_page() ) { comments_template(); }*/
?>