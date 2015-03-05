<?php

/*-----------------------------------------------------------------------------------*/
/* Start WooThemes Functions - Please refrain from editing this section */
/*-----------------------------------------------------------------------------------*/

// Set path to WooFramework and theme specific functions
$functions_path = get_template_directory() . '/functions/';
$includes_path = get_template_directory() . '/includes/';

// Don't load alt stylesheet from WooFramework
if ( ! function_exists( 'woo_output_alt_stylesheet' ) ) {
	function woo_output_alt_stylesheet () {}
}

// Define the theme-specific key to be sent to PressTrends.
define( 'WOO_PRESSTRENDS_THEMEKEY', 'tnla49pj66y028vef95h2oqhkir0tf3jr' );

// WooFramework
require_once ( $functions_path . 'admin-init.php' );	// Framework Init

if ( get_option( 'woo_woo_tumblog_switch' ) == 'true' ) {
	//Enable Tumblog Functionality and theme is upgraded
	update_option( 'woo_needs_tumblog_upgrade', 'false' );
	update_option( 'tumblog_woo_tumblog_upgraded', 'true' );
	update_option( 'tumblog_woo_tumblog_upgraded_posts_done', 'true' );
	require_once ( $functions_path . 'admin-tumblog-quickpress.php' );  // Tumblog Dashboard Functionality 
}

/*-----------------------------------------------------------------------------------*/
/* Load the theme-specific files, with support for overriding via a child theme.
/*-----------------------------------------------------------------------------------*/

$includes = array(
				'includes/theme-options.php', 			// Options panel settings and custom settings
				'includes/theme-functions.php', 		// Custom theme functions
				'includes/theme-actions.php', 			// Theme actions & user defined hooks
				'includes/theme-comments.php', 			// Custom comments/pingback loop
				'includes/theme-js.php', 				// Load JavaScript via wp_enqueue_script
				'includes/sidebar-init.php', 			// Initialize widgetized areas
				'includes/theme-widgets.php',			// Theme widgets
				'includes/theme-advanced.php',			// Advanced Theme Functions
				'includes/theme-shortcodes.php',	 	// Custom theme shortcodes
				'includes/woo-layout/woo-layout.php',	// Layout Manager
				'includes/woo-meta/woo-meta.php',		// Meta Manager
				'includes/woo-hooks/woo-hooks.php'		// Hook Manager
				);

// Allow child themes/plugins to add widgets to be loaded.
$includes = apply_filters( 'woo_includes', $includes );

foreach ( $includes as $i ) {
	locate_template( $i, true );
}

// Load WooCommerce functions, if applicable.
if ( is_woocommerce_activated() ) {
	locate_template( 'includes/theme-woocommerce.php', true );
}

/*-----------------------------------------------------------------------------------*/
/* You can add custom functions below */
/*-----------------------------------------------------------------------------------*/



// move Page Header above WP editor on Pages 
// Move TinyMCE Editor to the bottom
	add_action( 'add_meta_boxes', 'action_add_meta_boxes', 0 );
	function action_add_meta_boxes() {
		global $_wp_post_type_features;
// pages
			if (isset($_wp_post_type_features['page']['editor']) && $_wp_post_type_features['page']['editor']) {
				unset($_wp_post_type_features['page']['editor']);
				add_meta_box(
					'description_section',
					__('Page Content'),
					'inner_custom_box',
						'page', 'normal', 'low'
				);
			}
// posts (bio pages)			
			if (isset($_wp_post_type_features['our-team-member']['editor']) && $_wp_post_type_features['our-team-member']['editor']) {
				unset($_wp_post_type_features['our-team-member']['editor']);
				add_meta_box(
					'description_section',
					__('Bio Content'),
					'inner_custom_box',
						'our-team-member', 'normal', 'low'
				);
			}
	add_action( 'admin_head', 'action_admin_head'); //white background
	}
	function action_admin_head() {
	?>
	<style type="text/css">
		.wp-editor-container{background-color:#fff;}
	</style>
	<?php
	}
	function inner_custom_box( $post ) {
	echo '<div class="wp-editor-wrap">';
	//the_editor is deprecated in WP3.3, use instead:
	//wp_editor($post->post_content, 'content', array('dfw' => true, 'tabindex' => 1) );
	the_editor($post->post_content);
	echo '</div>';
	}
	
/*------------------------------------------------*/
/* - Add vcf to list of file types to upload
/*------------------------------------------------*/

add_filter('upload_mimes', 'custom_upload_mimes');
function custom_upload_mimes ( $existing_mimes=array() ) {
	// add your extension to the array
	$existing_mimes['vcf'] = 'text/x-vcard';
	return $existing_mimes;
}	
	



/*-----------------------------------------------------------------------------------*/
/* Don't add any code below here or the sky will fall down */
/*-----------------------------------------------------------------------------------*/
?>