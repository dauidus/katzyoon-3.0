<?php
/**
 * Default Content Template
 *
 * This template is the default content template. It is used to display the content of a
 * template file, when no more specific content-*.php file is available.
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
 $title_before = '<h1 class="title">';
 $title_after = '</h1>';

 $page_link_args = apply_filters( 'woothemes_pagelinks_args', array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) );

 woo_post_before();
?>
<article <?php post_class(); ?>>
<?php
	woo_post_inside_before();
?>
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
				
				<div class="subRtContact">
					<div class="contactContent">
						<?php echo (types_render_field("contact-header", array())); ?>
					</div>
				</div>	
			</div> <!-- subTop -->	
			
			<div class="fix"></div>
			
			<div class="subTxt">	
				<hr size="1px" />
					    
					    <?php
					    	the_content();
					    	if ( $woo_options['woo_post_content'] == 'content' || is_singular() ) wp_link_pages( $page_link_args );
					    ?>
				
			
			</div> <!-- subTxt -->
		</div> <!-- subFull -->
		
	</section><!-- /.entry -->
	<div class="fix"></div>
	
<?php
	woo_post_inside_after();
?>
</article><!-- /.post -->
<?php
	woo_post_after();
/*
	$comm = $woo_options[ 'woo_comments' ];
	if ( ( $comm == 'page' || $comm == 'both' ) && is_page() ) { comments_template(); }
*/
?>