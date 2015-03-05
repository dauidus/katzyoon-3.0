<?php
/*
Plugin Name: Roster Slider
Plugin URI: http://slidervilla.com/roster/
Description: Roster Slider adds a horizontal content and image slideshow with customizable background and slide intervals to any location of your blog.
Version: 1.7	
Author: Tejaswini Deshpande
Author URI: http://slidervilla.com/
Wordpress version supported: 3.0 and above
*/
/*  Copyright 2010-2013  Slider Villa  (email : tedeshpa@gmail.com)
*/
//defined global variables and constants here
global $roster_slider,$default_roster_slider_settings;
$roster_slider = get_option('roster_slider_options');
$default_roster_slider_settings = array('speed'=>'10', 
                           'time'=>'3',
	                       'no_posts'=>'9',
						   'visible'=>'3',
						   'scroll'=>'3',
						   'bg_color'=>'#eeeced', 
						   'height'=>'180',
						   'width'=>'540',
						   'iwidth'=>'120',
						   'border'=>'1',
						   'brcolor'=>'#dddddd',
						   'prev_next'=>'0',
						   'goto_slide'=>'1',
						   'title_text'=>'Featured Articles',
						   'title_from'=>'0',
						   'title_font'=>'Trebuchet MS,sans-serif',
						   'title_fontg'=>'',
						   'title_fsize'=>'18',
						   'title_fstyle'=>'bold',
						   'title_fcolor'=>'#3F4C6B',
						   'ptitle_font'=>'Georgia,serif',
						   'ptitle_fontg'=>'',
						   'ptitle_fsize'=>'12',
						   'ptitle_fstyle'=>'normal',
						   'ptitle_fcolor'=>'#0067B7',
						   'img_align'=>'none',
						   'img_height'=>'90',
						   'img_width'=>'110',
						   'img_border'=>'0',
						   'img_brcolor'=>'#D8E7EE',
						   'show_content'=>'0',
						   'content_font'=>'Verdana,Geneva,sans-serif',
						   'content_fontg'=>'',
						   'content_fsize'=>'11',
						   'content_fstyle'=>'normal',
						   'content_fcolor'=>'#222222',
						   'content_from'=>'content',
						   'content_chars'=>'',
						   'bg'=>'0',
						   'image_only'=>'0',
						   'allowable_tags'=>'',
						   'more'=>'Read More',
						   'a_attr'=>'',
						   'img_size'=>'1',
						   'img_pick'=>array('1','slider_thumbnail','1','1','1','1'), //use custom field/key, name of the key, use post featured image, pick the image attachment, attachment order,scan images
						   'user_level'=>'edit_others_posts',
						   'crop'=>'0',
						   'transition'=>'scrollHorz',
						   'easing'=>'swing',
						   'autostep'=>'1',
						   'multiple_sliders'=>'1',
						   'content_limit'=>'25',
						   'stylesheet'=>'default',
						   'shortcode'=>'1',
						   'rand'=>'0',
						   'fields'=>'',
						   'extend'=>'0',
						   'support'=>'1',
						   'fouc'=>'0',
						   'buttons'=>'default',
						   'navtop'=>'45',
						   'navw'=>'24',
						   'navnum'=>'0',
						   'css'=>'',
						   'custom_post'=>'0',
						   'preview'=>'2',
						   'slider_id'=>'1',
						   'catg_slug'=>'',
						   'setname'=>'Set',
						   'continue'=>'0',
						   'disable_preview'=>'0',
						   'remove_metabox'=>array(),
						   'pphoto'=>'0',
						   'css_js'=>'',
						   'responsive'=>'',
						   'tribe_events_fix'=>'0',
						   'coloronhover'=>'0',
						   'default_image'=>roster_slider_plugin_url( 'images/default_image.png' ),
						   'noscript'=>'This page is having a slideshow that uses Javascript. Your browser either doesn\'t support Javascript or you have it turned off. To see this page as it is meant to appear please use a Javascript enabled browser.'
			              );
define('ROSTER_SLIDER_TABLE','roster_slider'); //Slider TABLE NAME
define('ROSTER_SLIDER_META','roster_slider_meta'); //Meta TABLE NAME
define('ROSTER_SLIDER_POST_META','roster_slider_postmeta'); //Meta TABLE NAME
define('ROSTER_SLIDER_VER','1.7',false);//Current Version of Roster Slider
if ( ! defined( 'ROSTER_SLIDER_PLUGIN_BASENAME' ) )
	define( 'ROSTER_SLIDER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
if ( ! defined( 'ROSTER_SLIDER_CSS_DIR' ) ){
	define( 'ROSTER_SLIDER_CSS_DIR', WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'/css/skins/' );
}
// Create Text Domain For Translations
load_plugin_textdomain('roster-slider', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');

function install_roster_slider() {
	global $wpdb, $table_prefix;
	$table_name = $table_prefix.ROSTER_SLIDER_TABLE;
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
					id int(5) NOT NULL AUTO_INCREMENT,
					post_id int(11) NOT NULL,
					date datetime NOT NULL,
					slider_id int(5) NOT NULL DEFAULT '1',
					slide_order int(5) NOT NULL DEFAULT '0',
					UNIQUE KEY id(id)
				);";
		$rs = $wpdb->query($sql);
	}

   	$meta_table_name = $table_prefix.ROSTER_SLIDER_META;
	if($wpdb->get_var("show tables like '$meta_table_name'") != $meta_table_name) {
		$sql = "CREATE TABLE $meta_table_name (
					slider_id int(5) NOT NULL AUTO_INCREMENT,
					slider_name varchar(100) NOT NULL default '',
					UNIQUE KEY slider_id(slider_id)
				);";
		$rs2 = $wpdb->query($sql);
		
		$sql = "INSERT INTO $meta_table_name (slider_id,slider_name) VALUES('1','Roster Slider');";
		$rs3 = $wpdb->query($sql);
	}
	
	$slider_postmeta = $table_prefix.ROSTER_SLIDER_POST_META;
	if($wpdb->get_var("show tables like '$slider_postmeta'") != $slider_postmeta) {
		$sql = "CREATE TABLE $slider_postmeta (
					post_id int(11) NOT NULL,
					slider_id int(5) NOT NULL default '1',
					UNIQUE KEY post_id(post_id)
				);";
		$rs4 = $wpdb->query($sql);
	}
   // Roster Slider Settings and Options
   $default_slider = array();
   global $default_roster_slider_settings;
   $default_slider = $default_roster_slider_settings;
   
   	      	   $default_scounter='1';
	   $scounter=get_option('roster_slider_scounter');
	   if(!isset($scounter) or $scounter=='' or empty($scounter)){
	      update_option('roster_slider_scounter',$default_scounter);
		  $scounter=$default_scounter;
	   }
	   
	   for($i=1;$i<=$scounter;$i++){
	       if ($i==1){
		    $roster_slider_options='roster_slider_options';
		   }
		   else{
		    $roster_slider_options='roster_slider_options'.$i;
		   }
		   $roster_slider_curr=get_option($roster_slider_options);
	   				 
		   if(!$roster_slider_curr and $i==1) {
			 $roster_slider_curr = array();
		   }
		
		   if($roster_slider_curr or $i==1) {
			   foreach($default_slider as $key=>$value) {
				  if(!isset($roster_slider_curr[$key])) {
					 $roster_slider_curr[$key] = $value;
				  }
			   }
			   delete_option($roster_slider_options);	  
			   update_option($roster_slider_options,$roster_slider_curr);
		   }
	   } //end for loop
}
register_activation_hook( __FILE__, 'install_roster_slider' );
require_once (dirname (__FILE__) . '/includes/roster-slider-functions.php');
require_once (dirname (__FILE__) . '/includes/sslider-get-the-image-functions.php');

//This adds the post to the slider
function roster_add_to_slider($post_id) {
global $roster_slider;

//Tribe Events Calendar Fix
if( isset( $roster_slider['tribe_events_fix'] ) ) $tribe_events_fix=$roster_slider['tribe_events_fix'];
else $tribe_events_fix='0';
if(empty($tribe_events_fix) or $tribe_events_fix != '1') $tribe_events_fix='0';
if($tribe_events_fix == '1') global $roster_prev_post_ID;

 if(isset($_POST['roster-sldr-verify']) and current_user_can( $roster_slider['user_level'] ) ) {
	global $wpdb, $table_prefix, $post;
	
	//Tribe Events Calendar Fix
	if ( $tribe_events_fix == '0' or ($roster_prev_post_ID != $_POST['post_ID']  and  $tribe_events_fix == '1' ) ){
		$table_name = $table_prefix.ROSTER_SLIDER_TABLE;
		
		if(isset($_POST['roster-slider']) and !isset($_POST['roster_slider_name'])) {
		  $slider_id = '1';
		  if(is_post_on_any_roster_slider($post_id)){
			 $sql = "DELETE FROM $table_name where post_id = '$post_id'";
			 $wpdb->query($sql);
		  }
		  
		  if(isset($_POST['roster-slider']) and $_POST['roster-slider'] == "roster-slider" and !roster_slider($post_id,$slider_id)) {
			$dt = date('Y-m-d H:i:s');
			$sql = "INSERT INTO $table_name (post_id, date, slider_id) VALUES ('$post_id', '$dt', '$slider_id')";
			$wpdb->query($sql);
		  }
		}
		if(isset($_POST['roster-slider']) and $_POST['roster-slider'] == "roster-slider" and isset($_POST['roster_slider_name'])){
		  $slider_id_arr = $_POST['roster_slider_name'];
		  $post_sliders_data = roster_ss_get_post_sliders($post_id);
		  
		  foreach($post_sliders_data as $post_slider_data){
			if(!in_array($post_slider_data['slider_id'],$slider_id_arr)) {
			  $sql = "DELETE FROM $table_name where post_id = '$post_id'";
			  $wpdb->query($sql);
			}
		  }

			foreach($slider_id_arr as $slider_id) {
				if(!roster_slider($post_id,$slider_id)) {
					$dt = date('Y-m-d H:i:s');
					$sql = "INSERT INTO $table_name (post_id, date, slider_id) VALUES ('$post_id', '$dt', '$slider_id')";
					$wpdb->query($sql);
				}
			}
		}
		
		$table_name = $table_prefix.ROSTER_SLIDER_POST_META;
		if(isset($_POST['roster_display_slider']) and !isset($_POST['roster_display_slider_name'])) {
		  $slider_id = '1';
		}
		if(isset($_POST['roster_display_slider']) and isset($_POST['roster_display_slider_name'])){
		  $slider_id = $_POST['roster_display_slider_name'];
		}
		if(isset($_POST['roster_display_slider'])){	
			  if(!roster_ss_post_on_slider($post_id,$slider_id)) {
				$sql = "DELETE FROM $table_name where post_id = '$post_id'";
				$wpdb->query($sql);
				$sql = "INSERT INTO $table_name (post_id, slider_id) VALUES ('$post_id', '$slider_id')";
				$wpdb->query($sql);
			  }
		}
		$roster_slider_style = get_post_meta($post_id,'_roster_slider_style',true);
		$post_roster_slider_style=$_POST['_roster_slider_style'];
		if($roster_slider_style != $post_roster_slider_style and ( (isset($post_roster_slider_style) and !empty($post_roster_slider_style)) or (isset($roster_slider_style) and !empty($roster_slider_style)) ) ) {
		  update_post_meta($post_id, '_roster_slider_style', $_POST['_roster_slider_style']);	
		}
		
		$thumbnail_key = $roster_slider['img_pick'][1];
		$roster_sslider_thumbnail = get_post_meta($post_id,$thumbnail_key,true);
		$post_slider_thumbnail=$_POST['roster_sslider_thumbnail'];
		if($roster_sslider_thumbnail != $post_slider_thumbnail ) {
		  update_post_meta($post_id, $thumbnail_key, $_POST['roster_sslider_thumbnail']);	
		}
		
		$roster_link_attr = get_post_meta($post_id,'roster_link_attr',true);
		$link_attr=htmlentities($_POST['roster_link_attr'],ENT_QUOTES);
		if($roster_link_attr != $link_attr) {
		  update_post_meta($post_id, 'roster_link_attr', $link_attr);	
		}
		
		$roster_sslider_link = get_post_meta($post_id,'roster_slide_redirect_url',true);
		$link=$_POST['roster_sslider_link'];
		if($roster_sslider_link != $link) {
		  update_post_meta($post_id, 'roster_slide_redirect_url', $link);	
		}
		
		$roster_sslider_nolink = get_post_meta($post_id,'roster_sslider_nolink',true);
		$post_roster_sslider_nolink = $_POST['roster_sslider_nolink'];
		if($roster_sslider_nolink != $post_roster_sslider_nolink) {
		  update_post_meta($post_id, 'roster_sslider_nolink', $_POST['roster_sslider_nolink']);	
		}
	
	} //Tribe Events Calendar Fix if ends
	
	//Tribe Events Calendar Fix
	if($tribe_events_fix == '1') $roster_prev_post_ID=$_POST['post_ID'];
	
  } //roster-sldr-verify
}

//Removes the post from the slider, if you uncheck the checkbox from the edit post screen
function roster_remove_from_slider($post_id) {
	global $wpdb, $table_prefix;
	$table_name = $table_prefix.ROSTER_SLIDER_TABLE;
	
	// authorization
	if (!current_user_can('edit_post', $post_id))
		return $post_id;
	// origination and intention
	if (!wp_verify_nonce($_POST['roster-sldr-verify'], 'RosterSlider'))
		return $post_id;
	
    if(empty($_POST['roster-slider']) and is_post_on_any_roster_slider($post_id)) {
		$sql = "DELETE FROM $table_name where post_id = '$post_id'";
		$wpdb->query($sql);
	}
	
	$display_slider = $_POST['roster_display_slider'];
	$table_name = $table_prefix.ROSTER_SLIDER_POST_META;
	if(empty($display_slider) and roster_ss_slider_on_this_post($post_id)){
	  $sql = "DELETE FROM $table_name where post_id = '$post_id'";
		    $wpdb->query($sql);
	}
} 
  
function roster_delete_from_slider_table($post_id){
    global $wpdb, $table_prefix;
	$table_name = $table_prefix.ROSTER_SLIDER_TABLE;
    if(is_post_on_any_roster_slider($post_id)) {
		$sql = "DELETE FROM $table_name where post_id = '$post_id'";
		$wpdb->query($sql);
	}
	$table_name = $table_prefix.ROSTER_SLIDER_POST_META;
    if(roster_ss_slider_on_this_post($post_id)) {
		$sql = "DELETE FROM $table_name where post_id = '$post_id'";
		$wpdb->query($sql);
	}
}

// Slider checkbox on the admin page

function roster_slider_edit_custom_box(){
   roster_add_to_slider_checkbox();
}

function roster_slider_add_custom_box() {
 global $roster_slider;
 if (current_user_can( $roster_slider['user_level'] )) {
	if( function_exists( 'add_meta_box' ) ) {
	    $post_types=get_post_types();
		if (isset ($roster_slider['remove_metabox']))
		$remove_post_type_arr=$roster_slider['remove_metabox'];
		if(!isset($remove_post_type_arr) or !is_array($remove_post_type_arr) ) $remove_post_type_arr=array();
		foreach($post_types as $post_type) {
			if(!in_array($post_type,$remove_post_type_arr)){
				add_meta_box( 'roster_slider_box', __( 'Roster Slider' , 'roster-slider'), 'roster_slider_edit_custom_box', $post_type, 'advanced' );
			}
		}
		//add_meta_box( $id,   $title,     $callback,   $page, $context, $priority ); 
	} 
 }
}
/* Use the admin_menu action to define the custom boxes */
add_action('admin_menu', 'roster_slider_add_custom_box');

function roster_add_to_slider_checkbox() {
	global $post, $roster_slider;
	if (current_user_can( $roster_slider['user_level'] )) {
		$extra = "";
		
		$post_id = $post->ID;
		
		if(isset($post->ID)) {
			$post_id = $post->ID;
			if(is_post_on_any_roster_slider($post_id)) { $extra = 'checked="checked"'; }
		} 
		
		$post_slider_arr = array();
		
		$post_sliders = roster_ss_get_post_sliders($post_id);
		if($post_sliders) {
			foreach($post_sliders as $post_slider){
			   $post_slider_arr[] = $post_slider['slider_id'];
			}
		}
		
		$sliders = roster_ss_get_sliders();
?>
		<div class="slider_checkbox">
		<table class="form-table">
				
				<tr valign="top">
				<th scope="row"><input type="checkbox" class="sldr_post" name="roster-slider" value="roster-slider" <?php echo $extra;?> />
				<label for="roster-slider"><?php _e('Add this post/page to','roster-slider'); ?> </label></th>
				<td><select name="roster_slider_name[]" multiple="multiple" size="3" >
                <?php foreach ($sliders as $slider) { ?>
                  <option value="<?php echo $slider['slider_id'];?>" <?php if(in_array($slider['slider_id'],$post_slider_arr)){echo 'selected';} ?>><?php echo $slider['slider_name'];?></option>
                <?php } ?>
                </select>
				<input type="hidden" name="roster-sldr-verify" id="roster-sldr-verify" value="<?php echo wp_create_nonce('RosterSlider');?>" />
				</td>
				</tr>
                
         <?php if($roster_slider['multiple_sliders'] == '1') {?>
                <tr valign="top">
				<th scope="row">				
				<label for="roster_display_slider"><?php _e('Display ','roster-slider'); ?></label>
				<select name="roster_display_slider_name">
                <?php foreach ($sliders as $slider) { ?>
                  <option value="<?php echo $slider['slider_id'];?>" <?php if(roster_ss_post_on_slider($post_id,$slider['slider_id'])){echo 'selected';} ?>><?php echo $slider['slider_name'];?></option>
                <?php } ?>
                </select> 
				<?php _e('on this Post/Page','roster-slider'); ?></th>
				<td><input type="checkbox" class="sldr_post" name="roster_display_slider" value="1" <?php if(roster_ss_slider_on_this_post($post_id)){echo "checked";}?> /> 
				<?php _e('(Add the ','roster-slider'); ?><a href="http://guides.slidervilla.com/roster-slider/template-tags/simple-template-tag/" target="_blank"><?php _e('Roster Slider template tag','roster-slider'); ?></a> <?php _e('manually on your page.php/single.php or another page template file)','roster-slider'); ?></td>
				</tr>
          <?php } ?>
	    </div>
        <div>
        <?php
        $roster_slider_style = get_post_meta($post->ID,'_roster_slider_style',true);
        ?>
         <tr valign="top">
		 <th scope="row"><label for="_roster_slider_style"><?php _e('Stylesheet to use if slider is displayed on this Post/Page','roster-slider'); ?> </label></th>
		 <td><select name="_roster_slider_style" >
			<?php 
            $directory = ROSTER_SLIDER_CSS_DIR;
            if ($handle = opendir($directory)) {
                while (false !== ($file = readdir($handle))) { 
                 if($file != '.' and $file != '..') { ?>
                  <option value="<?php echo $file;?>" <?php if (($roster_slider_style == $file) or (empty($roster_slider_style) and $roster_slider['stylesheet'] == $file)){ echo "selected";}?> ><?php echo $file;?></option>
             <?php  } }
                closedir($handle);
            }
            ?>
        </select></td>
		</tr>
        
  <?php         $thumbnail_key = $roster_slider['img_pick'][1];
                $roster_sslider_thumbnail= get_post_meta($post_id, $thumbnail_key, true); 
				$roster_sslider_link= get_post_meta($post_id, 'roster_slide_redirect_url', true);  
				$roster_sslider_nolink=get_post_meta($post_id, 'roster_sslider_nolink', true);
				$roster_link_attr=get_post_meta($post_id, 'roster_link_attr', true);
  ?>
				<tr valign="top">
				<th scope="row"><label for="roster_sslider_thumbnail"><?php _e('Custom Thumbnail Image(url)','roster-slider'); ?></label></th>
                <td><input type="text" name="roster_sslider_thumbnail" class="roster_sslider_thumbnail" value="<?php echo $roster_sslider_thumbnail;?>" size="50" /></td>
				</tr>
				
				<tr valign="top">
                <th scope="row"><label for="roster_link_attr"><?php _e('Slide Link (anchor) attributes ','roster-slider'); ?></label></th>
                <td><input type="text" name="roster_link_attr" class="roster_link_attr" value="<?php echo $roster_link_attr;?>" size="50" /><small><?php _e('e.g. target="_blank" rel="external nofollow"','roster-slider'); ?></small></td>
				</tr>
				
				<tr valign="top">
                <th scope="row"><label for="roster_sslider_link"><?php _e('Slide Link URL ','roster-slider'); ?></label></th>
                <td><input type="text" name="roster_sslider_link" class="roster_sslider_link" value="<?php echo $roster_sslider_link;?>" size="50" /><small><?php _e('If left empty, it will be by default linked to the permalink.','roster-slider'); ?></small> </td>
				</tr>
				
                <tr valign="top">
				<th scope="row"><label for="roster_sslider_nolink"><?php _e('Do not link this slide to any page(url)','roster-slider'); ?> </label></th>
                <td><input type="checkbox" name="roster_sslider_nolink" class="roster_sslider_nolink" value="1" <?php if($roster_sslider_nolink=='1'){echo "checked";}?>  /></td>
				</tr>
				</table>
				
                 </div>
<?php }
}

//CSS for the checkbox on the admin page
function roster_slider_checkbox_css() {
?><style type="text/css" media="screen">.slider_checkbox{margin: 5px 0 10px 0;padding:3px;font-weight:bold;}.slider_checkbox input,.slider_checkbox select{font-weight:bold;}.slider_checkbox label,.slider_checkbox input,.slider_checkbox select{vertical-align:top;}</style>
<?php
}

add_action('publish_post', 'roster_add_to_slider');
add_action('publish_page', 'roster_add_to_slider');
add_action('edit_post', 'roster_add_to_slider');
add_action('publish_post', 'roster_remove_from_slider');
add_action('edit_post', 'roster_remove_from_slider');
add_action('deleted_post','roster_delete_from_slider_table');

add_action('edit_attachment', 'roster_add_to_slider');
add_action('delete_attachment','roster_delete_from_slider_table');

function roster_slider_plugin_url( $path = '' ) {
	global $wp_version;
	if ( version_compare( $wp_version, '2.8', '<' ) ) { // Using WordPress 2.7
		$folder = dirname( plugin_basename( __FILE__ ) );
		if ( '.' != $folder )
			$path = path_join( ltrim( $folder, '/' ), $path );

		return plugins_url( $path );
	}
	return plugins_url( $path, __FILE__ );
}

function roster_get_string_limit($output, $max_char)
{
    $output = str_replace(']]>', ']]&gt;', $output);
    $output = strip_tags($output);

  	if ((strlen($output)>$max_char) && ($espacio = strpos($output, " ", $max_char )))
	{
        $output = substr($output, 0, $espacio).'...';
		return $output;
   }
   else
   {
      return $output;
   }
}

function roster_slider_get_first_image($post) {
	$first_img = '';
	ob_start();
	ob_end_clean();
	$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
	$first_img = $matches [1] [0];
	return $first_img;
}
add_filter( 'plugin_action_links', 'roster_sslider_plugin_action_links', 10, 2 );

function roster_sslider_plugin_action_links( $links, $file ) {
	if ( $file != ROSTER_SLIDER_PLUGIN_BASENAME )
		return $links;

	$url = roster_sslider_admin_url( array( 'page' => 'roster-slider-settings' ) );

	$settings_link = '<a href="' . esc_attr( $url ) . '">'
		. esc_html( __( 'Settings') ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

//New Custom Post Type
if( $roster_slider['custom_post'] == '1' and !post_type_exists('slidervilla') ){
	add_action( 'init', 'roster_post_type', 11 );
	function roster_post_type() {
			$labels = array(
			'name' => _x('SliderVilla Slides', 'post type general name'),
			'singular_name' => _x('SliderVilla Slide', 'post type singular name'),
			'add_new' => _x('Add New', 'roster'),
			'add_new_item' => __('Add New SliderVilla Slide'),
			'edit_item' => __('Edit SliderVilla Slide'),
			'new_item' => __('New SliderVilla Slide'),
			'all_items' => __('All SliderVilla Slides'),
			'view_item' => __('View SliderVilla Slide'),
			'search_items' => __('Search SliderVilla Slides'),
			'not_found' =>  __('No SliderVilla slides found'),
			'not_found_in_trash' => __('No SliderVilla slides found in Trash'), 
			'parent_item_colon' => '',
			'menu_name' => 'SliderVilla Slides'

			);
			$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true, 
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title','editor','thumbnail','excerpt','custom-fields')
			); 
			register_post_type('slidervilla',$args);
	}

	//add filter to ensure the text SliderVilla, or slidervilla, is displayed when user updates a slidervilla 
	add_filter('post_updated_messages', 'roster_updated_messages');
	function roster_updated_messages( $messages ) {
	  global $post, $post_ID;

	  $messages['roster'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('SliderVilla Slide updated. <a href="%s">View SliderVilla slide</a>'), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.'),
		3 => __('Custom field deleted.'),
		4 => __('SliderVilla Slide updated.'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('SliderVilla Slide restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('SliderVilla Slide published. <a href="%s">View SliderVilla slide</a>'), esc_url( get_permalink($post_ID) ) ),
		7 => __('Roster saved.'),
		8 => sprintf( __('SliderVilla Slide submitted. <a target="_blank" href="%s">Preview SliderVilla slide</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('SliderVilla Slides scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview SliderVilla slide</a>'),
		  // translators: Publish box date format, see http://php.net/date
		  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('SliderVilla Slide draft updated. <a target="_blank" href="%s">Preview SliderVilla slide</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	  );

	  return $messages;
	}
} //if custom_post is true

require_once (dirname (__FILE__) . '/slider_versions/roster_1.php');
require_once (dirname (__FILE__) . '/settings/settings.php');
require_once (dirname (__FILE__) . '/includes/media-images.php');

// Load the update-notification class
add_action('init', 'roster_update_notification');
function roster_update_notification()
{
    require_once (dirname (__FILE__) . '/includes/upgrade.php');
    $roster_upgrade_remote_path = 'http://support.slidervilla.com/sv-updates/roster.php';
    new roster_update_class ( ROSTER_SLIDER_VER, $roster_upgrade_remote_path, ROSTER_SLIDER_PLUGIN_BASENAME );
}
?>