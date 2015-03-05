<?php
/**
 * Post Content Template
 *
 * This template is the default page content template. It is used to display the content of the
 * `single.php` template file, contextually, as well as in archive lists or search results.
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
 $title_before = '<' . $heading_tag . ' class="bio-title">';
 $title_after = '</' . $heading_tag . '>';

 $page_link_args = apply_filters( 'woothemes_pagelinks_args', array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) );

 woo_post_before();
?>
<article <?php post_class(); ?>>
<?php
	woo_post_inside_before();
?>
	
	<div style="margin-top:-25px; padding-bottom:15px; font-weight:600;">
		<a href="<?php bloginfo('url'); ?>/our-team/">&lt; Back</a>
	</div>
	<header style="background: #2c2e6e;">
		<?php the_title( $title_before, $title_after ); ?>
		<h2 class="bio-head-title"><?php echo (types_render_field("bio-title", array())); ?></h2>
	</header>


			
			<div class="fix"></div>
			
			<div id="bio-sidebar">
				<?php the_post_thumbnail(); ?>
				<div id="bio-sidebar-inner">
					T: <?php echo (types_render_field("phone", array())); ?><br>
					F: <?php $fax = (types_render_field("fax", array()));
		                	if ($fax != "") {
			                	echo $fax; 
			                	echo '<br>';
		                	} ?>
					<?php $email = (types_render_field("email", array()));
		                	if ($email != "") {
			                	echo $email; 
			                	echo '<br><br>';
		                	} ?>					
				 <?php $vcard = (types_render_field("vcard", array()));
		                	if ($vcard != "") {
			                	print '<a href="';
			                	echo $vcard; 
			                	print '">Download vCard</a>';
			                	echo '<br>';
		                	} ?>
		         <?php $linkedin = (types_render_field("linkedin-url", array()));
		                	if ($linkedin != "") {
			                	print '<a target="_blank" href=';
			                	echo (types_render_field("linkedin-url", array())); 
			                	print '><img src="'. get_stylesheet_directory_uri() .'/images/linkedin.png">Linkedin</a>';
			                	echo '<br>';
		                	} ?>
		         <br>
		         <?php $additional_image = (types_render_field("additional-image", array()));
		               $additional_image_link = (types_render_field("additional-image-link", array()));
		                	if ($additional_image != "") {
			                	echo types_render_field("additional-image", array());
			                	echo types_render_field("additional-image-link", array()); 
			                	echo '<br>';
		                	} ?>
		         <br>
		         
		         	
				</div>	
			</div>	
							    
		    <div id="bio-content">
		    	<?php the_content(); ?>
		    </div>
			
			

	</section><!-- /.entry -->
	<div class="fix"></div>
	

</article><!-- /.post -->
<?php
	/*woo_post_after();
	$comm = get_option( 'woo_comments' );
	if ( ( $comm == 'page' || $comm == 'both' ) && is_page() ) { comments_template(); }*/
?>