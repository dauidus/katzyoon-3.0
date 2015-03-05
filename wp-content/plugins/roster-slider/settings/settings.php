<?php // Hook for adding admin menus
if ( is_admin() ){ // admin actions
  add_action('admin_menu', 'roster_slider_settings');
  add_action( 'admin_init', 'register_roster_settings' ); 
} 

//Create Set & Export Settings
function roster_process_set_requests(){
	global $default_roster_slider_settings;
	$scounter=get_option('roster_slider_scounter');
	
	$cntr='';
	if(isset($_GET['scounter'])) $cntr = $_GET['scounter'];
	
	if(isset($_POST['create_set'])){
		if ($_POST['create_set']=='Create New Settings Set') {
		  $scounter++;
		  update_option('roster_slider_scounter',$scounter);
		  $options='roster_slider_options'.$scounter;
		  update_option($options,$default_roster_slider_settings);
		  $current_url = admin_url('admin.php?page=roster-slider-settings');
		  $current_url = add_query_arg('scounter',$scounter,$current_url);
		  wp_redirect( $current_url );
		  exit;
		}
	}

	//Export Settings
	if(isset($_POST['export'])){
		if ($_POST['export']=='Export') {
			@ob_end_clean();
			
			// required for IE, otherwise Content-Disposition may be ignored
			if(ini_get('zlib.output_compression'))
			ini_set('zlib.output_compression', 'Off');
			
			header('Content-Type: ' . "text/x-csv");
			header('Content-Disposition: attachment; filename="roster-settings-set-'.$cntr.'.csv"');
			header("Content-Transfer-Encoding: binary");
			header('Accept-Ranges: bytes');

			/* The three lines below basically make the
			download non-cacheable */
			header("Cache-control: private");
			header('Pragma: private');
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

			$exportTXT='';$i=0;
			$slider_options='roster_slider_options'.$cntr;
			$slider_curr=get_option($slider_options);
			foreach($slider_curr as $key=>$value){
				if($i>0) $exportTXT.="\n";
				if(!is_array($value)){
					$exportTXT.=$key.",".$value;
				}
				else {
					$exportTXT.=$key.',';
					$j=0;
					if($value) {
						foreach($value as $v){
							if($j>0) $exportTXT.="|";
							$exportTXT.=$v;
							$j++;
						}
					}
				}
				$i++;
			}
			$exportTXT.="\n";
			$exportTXT.="slider_name,roster";
			print($exportTXT); 
			exit();
		}
	}	
}
add_action('load-roster-slider_page_roster-slider-settings','roster_process_set_requests');

//function for adding settings page to wp-admin
function roster_slider_settings() {
    // Add a new submenu under Options:
	add_menu_page( 'Roster Slider', 'Roster Slider', 'manage_options','roster-slider-admin', 'roster_slider_create_multiple_sliders', roster_slider_plugin_url( 'images/roster_slider_icon.gif' ) );
	add_submenu_page('roster-slider-admin', 'Roster Sliders', 'Sliders', 'manage_options', 'roster-slider-admin', 'roster_slider_create_multiple_sliders');
	add_submenu_page('roster-slider-admin', 'Roster Slider Settings', 'Settings', 'manage_options', 'roster-slider-settings', 'roster_slider_settings_page');
}
include('sliders.php');
// This function displays the page content for the Roster Slider Options submenu
function roster_slider_settings_page() {
global $roster_slider,$default_roster_slider_settings;
$scounter=get_option('roster_slider_scounter');
if (isset($_GET['scounter']))$cntr = $_GET['scounter'];
else $cntr = '';

$new_settings_msg='';

//Reset Settings
if (isset ($_POST['roster_reset_settings_submit'])) {
	if ( $_POST['roster_reset_settings']!='n' ) {
	  $roster_reset_settings=$_POST['roster_reset_settings'];
	  $options='roster_slider_options'.$cntr;
	  $optionsvalue=get_option($options);
	  if( $roster_reset_settings == 'g' ){
		$new_settings_value=$default_roster_slider_settings;
		$new_settings_value['setname']=$optionsvalue['setname'];
		update_option($options,$new_settings_value);
	  }
	  else{
		if( $roster_reset_settings == '1' ){
			$new_settings_value=get_option('roster_slider_options');
			$new_settings_value['setname']=$optionsvalue['setname'];
			update_option($options,	$new_settings_value );
		}
		else{
			$new_option_name='roster_slider_options'.$roster_reset_settings;
			$new_settings_value=get_option($new_option_name);
			$new_settings_value['setname']=$optionsvalue['setname'];
			update_option($options,	$new_settings_value );
		}
	  }
	}
}

//Import Settings
if (isset ($_POST['import'])) {
	if ($_POST['import']=='Import') {
		global $wpdb;
		$imported_settings_message='';
		$csv_mimetypes = array('text/csv','text/plain','application/csv','text/comma-separated-values','application/excel',
	'application/vnd.ms-excel','application/vnd.msexcel','text/anytext','application/octet-stream','application/txt');
		if ($_FILES['settings_file']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['settings_file']['tmp_name']) && in_array($_FILES['settings_file']['type'], $csv_mimetypes) ) { 
			$imported_settings=file_get_contents($_FILES['settings_file']['tmp_name']); 
			$settings_arr=explode("\n",$imported_settings);
			$slider_settings=array();
			foreach($settings_arr as $settings_field){
				$s=explode(',',$settings_field);
				$inner=explode('|',$s[1]);
				if(count($inner)>1)	$slider_settings[$s[0]]=$inner;
				else $slider_settings[$s[0]]=$s[1];
			}
			$options='roster_slider_options'.$cntr;
			
			if( $slider_settings['slider_name'] == 'roster' )	{
				update_option($options,$slider_settings);
				$new_settings_msg='<div id="message" class="updated fade" style="clear:left;"><h3>'.__('Settings imported successfully ','roster-slider').'</h3></div>';
				$imported_settings_message='<div style="clear:left;color:#006E2E;"><h3>'.__('Settings imported successfully ','roster-slider').'</h3></div>';
			}
			else {
				$new_settings_msg=$imported_settings_message='<div id="message" class="error fade" style="clear:left;"><h3>'.__('Settings imported do not match to Roster Slider Settings. Please check the file.','roster-slider').'</h3></div>';
				$imported_settings_message='<div style="clear:left;color:#ff0000;"><h3>'.__('Settings imported do not match to Roster Slider Settings. Please check the file.','roster-slider').'</h3></div>';
			}
		}
		else{
			$new_settings_msg=$imported_settings_message='<div id="message" class="error fade" style="clear:left;"><h3>'.__('Error in File, Settings not imported. Please check the file being imported. ','roster-slider').'</h3></div>';
			$imported_settings_message='<div style="clear:left;color:#ff0000;"><h3>'.__('Error in File, Settings not imported. Please check the file being imported. ','roster-slider').'</h3></div>';
		}
	}
}
//Delete Set
if (isset ($_POST['delete_set'])) {
	if ($_POST['delete_set']=='Delete this Set' and isset($cntr) and !empty($cntr)) {
	  $options='roster_slider_options'.$cntr;
	  delete_option($options);
	  $cntr='';
	}
}

$group='roster-slider-group'.$cntr;
$roster_slider_options='roster_slider_options'.$cntr;

$roster_slider_curr=get_option($roster_slider_options);
if(!isset($cntr) or empty($cntr) or !$roster_slider_curr ){$curr = 'Default';}
else{$curr = $cntr;}
foreach($default_roster_slider_settings as $key=>$value){
	if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
}
?>

<div class="wrap" style="clear:both;">
<h2 style="float:left;"><?php _e('Roster Slider Settings ','roster-slider'); echo $curr; ?> </h2>
<form style="float:left;margin:10px 20px" action="" method="post">
<?php if(isset($cntr) and !empty($cntr)){ ?>
<input type="submit" class="button-primary" value="Delete this Set" name="delete_set"  onclick="return confirmSettingsDelete()" />
<?php } ?>
</form>
<div class="svilla_cl"></div>
<?php echo $new_settings_msg;?>
<?php 
if ($roster_slider_curr['disable_preview'] != '1'){
?>
<div id="settings_preview"><h2 style="clear:left;"><?php _e('Preview','roster-slider'); ?></h2> 
<?php 
if ($roster_slider_curr['preview'] == "0")
	get_roster_slider($roster_slider_curr['slider_id'],$cntr);
elseif($roster_slider_curr['preview'] == "1")
	get_roster_slider_category($roster_slider_curr['catg_slug'],$cntr);
else
	get_roster_slider_recent($cntr);
?> </div>
<?php } ?>

<div id="roster_settings" style="float:left;width:70%;">
<form method="post" action="options.php" id="roster_slider_form">
<?php settings_fields($group); ?>

<?php
if(!isset($cntr) or empty($cntr)){}
else{?>
	<table class="form-table">
		<tr valign="top">
		<th scope="row"><h3><?php _e('Setting Set Name','roster-slider'); ?></h3></th>
		<td><h3><input type="text" name="<?php echo $roster_slider_options;?>[setname]" id="roster_slider_setname" class="regular-text" value="<?php echo $roster_slider_curr['setname']; ?>" /></h3></td>
		</tr>
	</table>
<?php }
?>

<div id="slider_tabs">
        <ul class="ui-tabs">
            <li style="font-weight:bold;font-size:12px;"><a href="#basic">Basic Settings</a></li>
            <li style="font-weight:bold;font-size:12px;"><a href="#slider_content">Slider Content</a></li>
            <li style="font-weight:bold;font-size:12px;"><a href="#slider_nav">Navigation Settings</a></li>
			<li style="font-weight:bold;font-size:12px;"><a href="#responsive">Responsiveness</a></li>
			<li style="font-weight:bold;font-size:12px;"><a href="#preview">Preview Settings</a></li>
			<li style="font-weight:bold;font-size:12px;"><a href="#cssvalues">Generated CSS</a></li>
        </ul>

<div id="basic">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Basic Settings','roster-slider'); ?></h2> 
<p><?php _e('Customize the looks of the Slider box wrapping the content slides from here','roster-slider'); ?></p> 

<table class="form-table">

<?php $notran='';if($roster_slider['extend']=='1') $notran='style="display:none;"'; 
$noscroll='';if($roster_slider['extend']=='0') $noscroll='style="display:none;"'; ?> 
<tr valign="top" <?php echo $notran;?> >
<th scope="row"><?php _e('Slide Transition Effect','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[transition]" >
<option value="scrollHorz" <?php if ($roster_slider_curr['transition'] == "scrollHorz"){ echo "selected";}?> ><?php _e('Scroll Horizontally','roster-slider'); ?></option>
<option value="scrollVert" <?php if ($roster_slider_curr['transition'] == "scrollVert"){ echo "selected";}?> ><?php _e('Scroll Vertically','roster-slider'); ?></option>
<option value="turnUp" <?php if ($roster_slider_curr['transition'] == "turnUp"){ echo "selected";}?> ><?php _e('Turn Up','roster-slider'); ?></option>
<option value="turnDown" <?php if ($roster_slider_curr['transition'] == "turnDown"){ echo "selected";}?> ><?php _e('Turn Down','roster-slider'); ?></option>
<option value="fade" <?php if ($roster_slider_curr['transition'] == "fade"){ echo "selected";}?> ><?php _e('Fade','roster-slider'); ?></option>
<option value="uncover" <?php if ($roster_slider_curr['transition'] == "uncover"){ echo "selected";}?> ><?php _e('Uncover Slide','roster-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide Easing Effect','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[easing]" >
<option value="swing" <?php if ($roster_slider_curr['easing'] == "swing"){ echo "selected";}?> ><?php _e('swing','roster-slider'); ?></option>
<option value="easeInQuad" <?php if ($roster_slider_curr['easing'] == "easeInQuad"){ echo "selected";}?> ><?php _e('easeInQuad','roster-slider'); ?></option>
<option value="easeOutQuad" <?php if ($roster_slider_curr['easing'] == "easeOutQuad"){ echo "selected";}?> ><?php _e('easeOutQuad','roster-slider'); ?></option>
<option value="easeInOutQuad" <?php if ($roster_slider_curr['easing'] == "easeInOutQuad"){ echo "selected";}?> ><?php _e('easeInOutQuad','roster-slider'); ?></option>
<option value="easeInCubic" <?php if ($roster_slider_curr['easing'] == "easeInCubic"){ echo "selected";}?> ><?php _e('easeInCubic','roster-slider'); ?></option>
<option value="easeOutCubic" <?php if ($roster_slider_curr['easing'] == "easeOutCubic"){ echo "selected";}?> ><?php _e('easeOutCubic','roster-slider'); ?></option>
<option value="easeInOutCubic" <?php if ($roster_slider_curr['easing'] == "easeInOutCubic"){ echo "selected";}?> ><?php _e('easeInOutCubic','roster-slider'); ?></option>
<option value="easeInQuart" <?php if ($roster_slider_curr['easing'] == "easeInQuart"){ echo "selected";}?> ><?php _e('easeInQuart','roster-slider'); ?></option>
<option value="easeOutQuart" <?php if ($roster_slider_curr['easing'] == "easeOutQuart"){ echo "selected";}?> ><?php _e('easeOutQuart','roster-slider'); ?></option>
<option value="easeInOutQuart" <?php if ($roster_slider_curr['easing'] == "easeInOutQuart"){ echo "selected";}?> ><?php _e('easeInOutQuart','roster-slider'); ?></option>
<option value="easeInQuint" <?php if ($roster_slider_curr['easing'] == "easeInQuint"){ echo "selected";}?> ><?php _e('easeInQuint','roster-slider'); ?></option>
<option value="easeOutQuint" <?php if ($roster_slider_curr['easing'] == "easeOutQuint"){ echo "selected";}?> ><?php _e('easeOutQuint','roster-slider'); ?></option>
<option value="easeInOutQuint" <?php if ($roster_slider_curr['easing'] == "easeInOutQuint"){ echo "selected";}?> ><?php _e('easeInOutQuint','roster-slider'); ?></option>
<option value="easeInSine" <?php if ($roster_slider_curr['easing'] == "easeInSine"){ echo "selected";}?> ><?php _e('easeInSine','roster-slider'); ?></option>
<option value="easeOutSine" <?php if ($roster_slider_curr['easing'] == "easeOutSine"){ echo "selected";}?> ><?php _e('easeOutSine','roster-slider'); ?></option>
<option value="easeInOutSine" <?php if ($roster_slider_curr['easing'] == "easeInOutSine"){ echo "selected";}?> ><?php _e('easeInOutSine','roster-slider'); ?></option>
<option value="easeInExpo" <?php if ($roster_slider_curr['easing'] == "easeInExpo"){ echo "selected";}?> ><?php _e('easeInExpo','roster-slider'); ?></option>
<option value="easeOutExpo" <?php if ($roster_slider_curr['easing'] == "easeOutExpo"){ echo "selected";}?> ><?php _e('easeOutExpo','roster-slider'); ?></option>
<option value="easeInOutExpo" <?php if ($roster_slider_curr['easing'] == "easeInOutExpo"){ echo "selected";}?> ><?php _e('easeInOutExpo','roster-slider'); ?></option>
<option value="easeInCirc" <?php if ($roster_slider_curr['easing'] == "easeInCirc"){ echo "selected";}?> ><?php _e('easeInCirc','roster-slider'); ?></option>
<option value="easeOutCirc" <?php if ($roster_slider_curr['easing'] == "easeOutCirc"){ echo "selected";}?> ><?php _e('easeOutCirc','roster-slider'); ?></option>
<option value="easeInOutCirc" <?php if ($roster_slider_curr['easing'] == "easeInOutCirc"){ echo "selected";}?> ><?php _e('easeInOutCirc','roster-slider'); ?></option>
<option value="easeInElastic" <?php if ($roster_slider_curr['easing'] == "easeInElastic"){ echo "selected";}?> ><?php _e('easeInElastic','roster-slider'); ?></option>
<option value="easeOutElastic" <?php if ($roster_slider_curr['easing'] == "easeOutElastic"){ echo "selected";}?> ><?php _e('easeOutElastic','roster-slider'); ?></option>
<option value="easeInOutElastic" <?php if ($roster_slider_curr['easing'] == "easeInOutElastic"){ echo "selected";}?> ><?php _e('easeInOutElastic','roster-slider'); ?></option>
<option value="easeInBack" <?php if ($roster_slider_curr['easing'] == "easeInBack"){ echo "selected";}?> ><?php _e('easeInBack','roster-slider'); ?></option>
<option value="easeOutBack" <?php if ($roster_slider_curr['easing'] == "easeOutBack"){ echo "selected";}?> ><?php _e('easeOutBack','roster-slider'); ?></option>
<option value="easeInOutBack" <?php if ($roster_slider_curr['easing'] == "easeInOutBack"){ echo "selected";}?> ><?php _e('easeInOutBack','roster-slider'); ?></option>
<option value="easeInBounce" <?php if ($roster_slider_curr['easing'] == "easeInBounce"){ echo "selected";}?> ><?php _e('easeInBounce','roster-slider'); ?></option>
<option value="easeOutBounce" <?php if ($roster_slider_curr['easing'] == "easeOutBounce"){ echo "selected";}?> ><?php _e('easeOutBounce','roster-slider'); ?></option>
<option value="easeInOutBounce" <?php if ($roster_slider_curr['easing'] == "easeInOutBounce"){ echo "selected";}?> ><?php _e('easeInOutBounce','roster-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Speed of Transition','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[speed]" id="roster_slider_speed" class="small-text" value="<?php echo $roster_slider_curr['speed']; ?>" /><br /><small style="color:#FF0000"><?php _e(' (IMP!! Enter value > 0)','roster-slider'); ?></small>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('The duration of Slide Animation in milliseconds. Lower value indicates fast animation. Enter numeric values like 5 or 7.','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Enable autosliding of slides','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[autostep]" >
<option value="1" <?php if ($roster_slider_curr['autostep'] == "1"){ echo "selected";}?> ><?php _e('Yes','roster-slider'); ?></option>
<option value="0" <?php if ($roster_slider_curr['autostep'] == "0"){ echo "selected";}?> ><?php _e('No','roster-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Time between Transitions','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[time]" id="roster_slider_time" class="small-text" value="<?php echo $roster_slider_curr['time']; ?>" /><br /><small style="color:#FF0000"><?php _e(' (IMP!! Enter value > 0)','roster-slider'); ?></small>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('Enter number of secs you want the slider to stop before sliding to next slide.','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Number of Posts to be shown in the Roster Slider','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[no_posts]" id="roster_slider_no_posts" class="small-text" value="<?php echo $roster_slider_curr['no_posts']; ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Number of Items Visible in One Set','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[visible]" id="roster_slider_visible" class="small-text" value="<?php echo $roster_slider_curr['visible']; ?>" /><small style="color:#FF0000"><?php _e('(Caution: Do not enter 0)','roster-slider'); ?></small></td>
</tr>

<tr valign="top" <?php echo $noscroll;?> >
<th scope="row"><?php _e('Number of Items to Scroll in one transition','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[scroll]" id="roster_slider_visible" class="small-text" value="<?php echo $roster_slider_curr['scroll']; ?>" /><small style="color:#FF0000"><?php _e('(Caution: Do not enter 0)','roster-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Complete Slider Width','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[width]" id="roster_slider_width" class="small-text" value="<?php echo $roster_slider_curr['width']; ?>" />&nbsp;<?php _e('px','roster-slider'); ?><small><?php _e('(If set to 0, will take the container\'s width)','roster-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide (Item) Width','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[iwidth]" id="roster_slider_iwidth" class="small-text" value="<?php echo $roster_slider_curr['iwidth']; ?>" />&nbsp;<?php _e('px','roster-slider'); ?><small style="color:#FF0000"><?php _e(' (IMP!! Enter numeric value > 0)','roster-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide (Item) Height','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[height]" id="roster_slider_height" class="small-text" value="<?php echo $roster_slider_curr['height']; ?>" />&nbsp;<?php _e('px','roster-slider'); ?><small style="color:#FF0000"><?php _e(' (IMP!! Enter numeric value > 0)','roster-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide Background Color','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[bg_color]" id="color_value_1" value="<?php echo $roster_slider_curr['bg_color']; ?>" />&nbsp; <img id="color_picker_1" src="<?php echo roster_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','roster-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_1"></div> &nbsp; &nbsp; &nbsp; 
<label for="roster_slider_bg"><input name="<?php echo $roster_slider_options;?>[bg]" type="checkbox" id="roster_slider_bg" value="1" <?php checked('1', $roster_slider_curr['bg']); ?>  /><?php _e(' Use Transparent Background','roster-slider'); ?></label> </td>
</tr>
 
<tr valign="top">
<th scope="row"><?php _e('Slide Border Thickness','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[border]" id="roster_slider_border" class="small-text" value="<?php echo $roster_slider_curr['border']; ?>" />&nbsp;<?php _e('px (put 0 if no border is required)','roster-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide Border Color','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[brcolor]" id="color_value_6" value="<?php echo $roster_slider_curr['brcolor']; ?>" />&nbsp; <img id="color_picker_6" src="<?php echo roster_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','roster-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_6"></div></td>
</tr>

</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Miscellaneous','roster-slider'); ?></h2> 

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Retain these html tags','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[allowable_tags]" class="regular-text code" value="<?php echo $roster_slider_curr['allowable_tags']; ?>" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Continue Reading Text','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[more]" class="regular-text code" value="<?php echo $roster_slider_curr['more']; ?>" /></td>
</tr>

<tr valign="top" <?php echo $notran;?> >
<th scope="row"><?php _e('Continue sliding after user clicks the navigation','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[continue]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['continue']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('by default the Slider will stop sliding once the user clicks the previous next navigation buttons or the navigation numbers. Put a tick in this checkbox if you want the slider to keep on sliding though user interferes the sliding by clicking the previous next navigation.','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide Link (\'a\' element) attributes  ','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[a_attr]" class="regular-text code" value="<?php echo htmlentities( $roster_slider_curr['a_attr'] , ENT_QUOTES); ?>" />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('eg. target="_blank" rel="external nofollow"','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Use PrettyPhoto (Lightbox) for Slide Images','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[pphoto]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['pphoto']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('If checked, when user clicks the slide image, it will appear in a modal lightbox','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Custom fields to display for post/pages','roster-slider'); ?></th>
<td><textarea name="<?php echo $roster_slider_options;?>[fields]"  rows="5" cols="44" class="regular-text code"><?php echo $roster_slider_curr['fields']; ?></textarea><span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('Separate different fields using commas eg. description,customfield2','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Randomize Slides in Slider','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[rand]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['rand']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('check this if you want the slides added to appear in random order.','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<?php if(!isset($cntr) or empty($cntr)){?>

<tr valign="top">
<th scope="row"><?php _e('Minimum User Level to add Post to the Slider','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[user_level]" >
<option value="manage_options" <?php if ($roster_slider_curr['user_level'] == "manage_options"){ echo "selected";}?> ><?php _e('Administrator','roster-slider'); ?></option>
<option value="edit_others_posts" <?php if ($roster_slider_curr['user_level'] == "edit_others_posts"){ echo "selected";}?> ><?php _e('Editor and Admininstrator','roster-slider'); ?></option>
<option value="publish_posts" <?php if ($roster_slider_curr['user_level'] == "publish_posts"){ echo "selected";}?> ><?php _e('Author, Editor and Admininstrator','roster-slider'); ?></option>
<option value="edit_posts" <?php if ($roster_slider_curr['user_level'] == "edit_posts"){ echo "selected";}?> ><?php _e('Contributor, Author, Editor and Admininstrator','roster-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Text to display in the JavaScript disabled browser','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[noscript]" class="regular-text code" value="<?php echo $roster_slider_curr['noscript']; ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Add Shortcode Support','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[shortcode]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['shortcode']); ?>  />&nbsp;<?php _e('check this if you want to use Roster Slider Shortcode i.e [rosterslider]','roster-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Roster Slider Styles to Use on Other than Post/Pages','roster-slider'); ?> <small><?php _e('(i.e. for index.php,category.php,archive.php etc)','roster-slider'); ?></small></th>
<td><select name="<?php echo $roster_slider_options;?>[stylesheet]" >
<?php 
$directory = ROSTER_SLIDER_CSS_DIR;
if ($handle = opendir($directory)) {
    while (false !== ($file = readdir($handle))) { 
     if($file != '.' and $file != '..') { ?>
      <option value="<?php echo $file;?>" <?php if ($roster_slider_curr['stylesheet'] == $file){ echo "selected";}?> ><?php echo $file;?></option>
 <?php  } }
    closedir($handle);
}
?>
</select>
</td>
</tr>
<?php } ?>

<?php if(!isset($cntr) or empty($cntr)){?>
<tr valign="top">
<th scope="row"><?php _e('Multiple Slider Feature','roster-slider'); ?></th>
<td><label for="roster_slider_multiple"> 
<input name="<?php echo $roster_slider_options;?>[multiple_sliders]" type="checkbox" id="roster_slider_multiple" value="1" <?php checked("1", $roster_slider_curr['multiple_sliders']); ?> /> 
 <?php _e('Enable Multiple Slider Function on Edit Post/Page','roster-slider'); ?></label></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Create "SliderVilla Slides" Custom Post Type','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[custom_post]" >
<option value="0" <?php if ($roster_slider_curr['custom_post'] == "0"){ echo "selected";}?> ><?php _e('No','roster-slider'); ?></option>
<option value="1" <?php if ($roster_slider_curr['custom_post'] == "1"){ echo "selected";}?> ><?php _e('Yes','roster-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Remove Roster Slider Metabox on','roster-slider'); ?></th>
<td>
<select name="<?php echo $roster_slider_options;?>[remove_metabox][]" multiple="multiple" size="3" style="min-height:6em;">
<?php 
$args=array(
  'public'   => true
); 
$output = 'objects'; // names or objects, note names is the default
$post_types=get_post_types($args,$output); $remove_post_type_arr=$roster_slider_curr['remove_metabox'];
if(!isset($remove_post_type_arr) or !is_array($remove_post_type_arr) ) $remove_post_type_arr=array();
		foreach($post_types as $post_type) { ?>
                  <option value="<?php echo $post_type->name;?>" <?php if(in_array($post_type->name,$remove_post_type_arr)){echo 'selected';} ?>><?php echo $post_type->labels->name;?></option>
                <?php } ?>
</select>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('You can select single/multiple post types using Ctrl+Mouse Click. To deselect a single post type, use Ctrl+Mouse Click','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<?php } ?>

<tr valign="top">
<th scope="row"><?php _e('Enable FOUC','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[fouc]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['fouc']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('check this if you would not want to disable Flash of Unstyled Content in the slider when the page is loaded.','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<?php if(!isset($cntr) or empty($cntr)){?>

<tr valign="top">
<th scope="row"><?php _e('Custom Styles','roster-slider'); ?></th>
<td><textarea name="<?php echo $roster_slider_options;?>[css]"  rows="5" cols="44" class="regular-text code"><?php echo $roster_slider_curr['css']; ?></textarea>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('custom css styles that you would want to be applied to the slider elements.','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Custom Styles load thru JS','roster-slider'); ?></th>
<td><textarea name="<?php echo $roster_slider_options;?>[css_js]"  rows="5" cols="44" class="regular-text code"><?php echo $roster_slider_curr['css_js']; ?></textarea>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('Custom css loading thru jQuery on document load that you would want to be applied to the slider elements. Use this field only if necessary!','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Feature Support Needed','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[extend]" style="width:75%">
<option value="0" <?php if ($roster_slider_curr['extend'] == "0"){ echo "selected";}?> ><?php _e('Scroll Value will be same as Visible items value','roster-slider'); ?></option>
<option value="1" <?php if ($roster_slider_curr['extend'] == "1"){ echo "selected";}?> ><?php _e('Scroll Value will differ from Visible items value','roster-slider'); ?></option>
<option value="2" <?php if ($roster_slider_curr['extend'] == "2"){ echo "selected";}?> ><?php _e('Multiple Settings, Scroll value can be same or different than Visible item value','roster-slider'); ?></option>
</select>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('this will decide if one extra script will be loaded to header or not.','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Include Fix for (Tribe) Events Calendar','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[tribe_events_fix]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['tribe_events_fix']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('Put a tick in this checkbox if you use (Tribe) Events Calendar','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Show Promotionals on Admin Page','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[support]" >
<option value="1" <?php if ($roster_slider_curr['support'] == "1"){ echo "selected";}?> ><?php _e('Yes','roster-slider'); ?></option>
<option value="0" <?php if ($roster_slider_curr['support'] == "0"){ echo "selected";}?> ><?php _e('No','roster-slider'); ?></option>
</select>
</td>
</tr>
<?php } ?>

</table>
</div>
</div> <!--Basic Tab Ends-->

<div id="slider_content">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Slider Title','roster-slider'); ?></h2> 
<p><?php _e('Customize the looks of the main title of the Slideshow from here','roster-slider'); ?></p> 
<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Default Title Text','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[title_text]" id="roster_slider_title_text" value="<?php echo htmlentities($roster_slider_curr['title_text'], ENT_QUOTES); ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Pick Slider Title From','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[title_from]" >
<option value="0" <?php if ($roster_slider_curr['title_from'] == "0"){ echo "selected";}?> ><?php _e('Default Title Text','roster-slider'); ?></option>
<option value="1" <?php if ($roster_slider_curr['title_from'] == "1"){ echo "selected";}?> ><?php _e('Slider Name','roster-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[title_font]" id="roster_slider_title_font" >
<option value="Arial,Helvetica,sans-serif" <?php if ($roster_slider_curr['title_font'] == "Arial,Helvetica,sans-serif"){ echo "selected";}?> >Arial,Helvetica,sans-serif</option>
<option value="Verdana,Geneva,sans-serif" <?php if ($roster_slider_curr['title_font'] == "Verdana,Geneva,sans-serif"){ echo "selected";}?> >Verdana,Geneva,sans-serif</option>
<option value="Tahoma,Geneva,sans-serif" <?php if ($roster_slider_curr['title_font'] == "Tahoma,Geneva,sans-serif"){ echo "selected";}?> >Tahoma,Geneva,sans-serif</option>
<option value="Trebuchet MS,sans-serif" <?php if ($roster_slider_curr['title_font'] == "Trebuchet MS,sans-serif"){ echo "selected";}?> >Trebuchet MS,sans-serif</option>
<option value="'Century Gothic','Avant Garde',sans-serif" <?php if ($roster_slider_curr['title_font'] == "'Century Gothic','Avant Garde',sans-serif"){ echo "selected";}?> >'Century Gothic','Avant Garde',sans-serif</option>
<option value="'Arial Narrow',sans-serif" <?php if ($roster_slider_curr['title_font'] == "'Arial Narrow',sans-serif"){ echo "selected";}?> >'Arial Narrow',sans-serif</option>
<option value="'Arial Black',sans-serif" <?php if ($roster_slider_curr['title_font'] == "'Arial Black',sans-serif"){ echo "selected";}?> >'Arial Black',sans-serif</option>
<option value="'Gills Sans MT','Gills Sans',sans-serif" <?php if ($roster_slider_curr['title_font'] == "'Gills Sans MT','Gills Sans',sans-serif"){ echo "selected";} ?> >'Gills Sans MT','Gills Sans',sans-serif</option>
<option value="'Times New Roman',Times,serif" <?php if ($roster_slider_curr['title_font'] == "'Times New Roman',Times,serif"){ echo "selected";}?> >'Times New Roman',Times,serif</option>
<option value="Georgia,serif" <?php if ($roster_slider_curr['title_font'] == "Georgia,serif"){ echo "selected";}?> >Georgia,serif</option>
<option value="Garamond,serif" <?php if ($roster_slider_curr['title_font'] == "Garamond,serif"){ echo "selected";}?> >Garamond,serif</option>
<option value="'Century Schoolbook','New Century Schoolbook',serif" <?php if ($roster_slider_curr['title_font'] == "'Century Schoolbook','New Century Schoolbook',serif"){ echo "selected";}?> >'Century Schoolbook','New Century Schoolbook',serif</option>
<option value="'Bookman Old Style',Bookman,serif" <?php if ($roster_slider_curr['title_font'] == "'Bookman Old Style',Bookman,serif"){ echo "selected";}?> >'Bookman Old Style',Bookman,serif</option>
<option value="'Comic Sans MS',cursive" <?php if ($roster_slider_curr['title_font'] == "'Comic Sans MS',cursive"){ echo "selected";}?> >'Comic Sans MS',cursive</option>
<option value="'Courier New',Courier,monospace" <?php if ($roster_slider_curr['title_font'] == "'Courier New',Courier,monospace"){ echo "selected";}?> >'Courier New',Courier,monospace</option>
<option value="'Copperplate Gothic Bold',Copperplate,fantasy" <?php if ($roster_slider_curr['title_font'] == "'Copperplate Gothic Bold',Copperplate,fantasy"){ echo "selected";}?> >'Copperplate Gothic Bold',Copperplate,fantasy</option>
<option value="Impact,fantasy" <?php if ($roster_slider_curr['title_font'] == "Impact,fantasy"){ echo "selected";}?> >Impact,fantasy</option>
<option value="sans-serif" <?php if ($roster_slider_curr['title_font'] == "sans-serif"){ echo "selected";}?> >sans-serif</option>
<option value="serif" <?php if ($roster_slider_curr['title_font'] == "serif"){ echo "selected";}?> >serif</option>
<option value="cursive" <?php if ($roster_slider_curr['title_font'] == "cursive"){ echo "selected";}?> >cursive</option>
<option value="monospace" <?php if ($roster_slider_curr['title_font'] == "monospace"){ echo "selected";}?> >monospace</option>
<option value="fantasy" <?php if ($roster_slider_curr['title_font'] == "fantasy"){ echo "selected";}?> >fantasy</option>
</select>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('This value will be fallback font if Google web font value is specified below','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Google Web Font','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[title_fontg]" id="roster_slider_title_fontg" value="<?php echo htmlentities($roster_slider_curr['title_fontg'], ENT_QUOTES); ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Color','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[title_fcolor]" id="color_value_2" value="<?php echo $roster_slider_curr['title_fcolor']; ?>" />&nbsp; <img id="color_picker_2" src="<?php echo roster_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','roster-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_2"></div></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Size','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[title_fsize]" id="roster_slider_title_fsize" class="small-text" value="<?php echo $roster_slider_curr['title_fsize']; ?>" />&nbsp;<?php _e('px','roster-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Style','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[title_fstyle]" id="roster_slider_title_fstyle" >
<option value="bold" <?php if ($roster_slider_curr['title_fstyle'] == "bold"){ echo "selected";}?> ><?php _e('Bold','roster-slider'); ?></option>
<option value="bold italic" <?php if ($roster_slider_curr['title_fstyle'] == "bold italic"){ echo "selected";}?> ><?php _e('Bold Italic','roster-slider'); ?></option>
<option value="italic" <?php if ($roster_slider_curr['title_fstyle'] == "italic"){ echo "selected";}?> ><?php _e('Italic','roster-slider'); ?></option>
<option value="normal" <?php if ($roster_slider_curr['title_fstyle'] == "normal"){ echo "selected";}?> ><?php _e('Normal','roster-slider'); ?></option>
</select>
</td>
</tr>
</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Post Title','roster-slider'); ?></h2> 
<p><?php _e('Customize the looks of the title of each of the sliding post here','roster-slider'); ?></p> 
<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Font','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[ptitle_font]" id="roster_slider_ptitle_font" >
<option value="Arial,Helvetica,sans-serif" <?php if ($roster_slider_curr['ptitle_font'] == "Arial,Helvetica,sans-serif"){ echo "selected";}?> >Arial,Helvetica,sans-serif</option>
<option value="Verdana,Geneva,sans-serif" <?php if ($roster_slider_curr['ptitle_font'] == "Verdana,Geneva,sans-serif"){ echo "selected";}?> >Verdana,Geneva,sans-serif</option>
<option value="Tahoma,Geneva,sans-serif" <?php if ($roster_slider_curr['ptitle_font'] == "Tahoma,Geneva,sans-serif"){ echo "selected";}?> >Tahoma,Geneva,sans-serif</option>
<option value="Trebuchet MS,sans-serif" <?php if ($roster_slider_curr['ptitle_font'] == "Trebuchet MS,sans-serif"){ echo "selected";}?> >Trebuchet MS,sans-serif</option>
<option value="'Century Gothic','Avant Garde',sans-serif" <?php if ($roster_slider_curr['ptitle_font'] == "'Century Gothic','Avant Garde',sans-serif"){ echo "selected";}?> >'Century Gothic','Avant Garde',sans-serif</option>
<option value="'Arial Narrow',sans-serif" <?php if ($roster_slider_curr['ptitle_font'] == "'Arial Narrow',sans-serif"){ echo "selected";}?> >'Arial Narrow',sans-serif</option>
<option value="'Arial Black',sans-serif" <?php if ($roster_slider_curr['ptitle_font'] == "'Arial Black',sans-serif"){ echo "selected";}?> >'Arial Black',sans-serif</option>
<option value="'Gills Sans MT','Gills Sans',sans-serif" <?php if ($roster_slider_curr['ptitle_font'] == "'Gills Sans MT','Gills Sans',sans-serif"){ echo "selected";} ?> >'Gills Sans MT','Gills Sans',sans-serif</option>
<option value="'Times New Roman',Times,serif" <?php if ($roster_slider_curr['ptitle_font'] == "'Times New Roman',Times,serif"){ echo "selected";}?> >'Times New Roman',Times,serif</option>
<option value="Georgia,serif" <?php if ($roster_slider_curr['ptitle_font'] == "Georgia,serif"){ echo "selected";}?> >Georgia,serif</option>
<option value="Garamond,serif" <?php if ($roster_slider_curr['ptitle_font'] == "Garamond,serif"){ echo "selected";}?> >Garamond,serif</option>
<option value="'Century Schoolbook','New Century Schoolbook',serif" <?php if ($roster_slider_curr['ptitle_font'] == "'Century Schoolbook','New Century Schoolbook',serif"){ echo "selected";}?> >'Century Schoolbook','New Century Schoolbook',serif</option>
<option value="'Bookman Old Style',Bookman,serif" <?php if ($roster_slider_curr['ptitle_font'] == "'Bookman Old Style',Bookman,serif"){ echo "selected";}?> >'Bookman Old Style',Bookman,serif</option>
<option value="'Comic Sans MS',cursive" <?php if ($roster_slider_curr['ptitle_font'] == "'Comic Sans MS',cursive"){ echo "selected";}?> >'Comic Sans MS',cursive</option>
<option value="'Courier New',Courier,monospace" <?php if ($roster_slider_curr['ptitle_font'] == "'Courier New',Courier,monospace"){ echo "selected";}?> >'Courier New',Courier,monospace</option>
<option value="'Copperplate Gothic Bold',Copperplate,fantasy" <?php if ($roster_slider_curr['ptitle_font'] == "'Copperplate Gothic Bold',Copperplate,fantasy"){ echo "selected";}?> >'Copperplate Gothic Bold',Copperplate,fantasy</option>
<option value="Impact,fantasy" <?php if ($roster_slider_curr['ptitle_font'] == "Impact,fantasy"){ echo "selected";}?> >Impact,fantasy</option>
<option value="sans-serif" <?php if ($roster_slider_curr['ptitle_font'] == "sans-serif"){ echo "selected";}?> >sans-serif</option>
<option value="serif" <?php if ($roster_slider_curr['ptitle_font'] == "serif"){ echo "selected";}?> >serif</option>
<option value="cursive" <?php if ($roster_slider_curr['ptitle_font'] == "cursive"){ echo "selected";}?> >cursive</option>
<option value="monospace" <?php if ($roster_slider_curr['ptitle_font'] == "monospace"){ echo "selected";}?> >monospace</option>
<option value="fantasy" <?php if ($roster_slider_curr['ptitle_font'] == "fantasy"){ echo "selected";}?> >fantasy</option>
</select>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('This value will be fallback font if Google web font value is specified below','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Google Web Font','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[ptitle_fontg]" id="roster_slider_ptitle_fontg" value="<?php echo htmlentities($roster_slider_curr['ptitle_fontg'], ENT_QUOTES); ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Color','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[ptitle_fcolor]" id="color_value_3" value="<?php echo $roster_slider_curr['ptitle_fcolor']; ?>" />&nbsp; <img id="color_picker_3" src="<?php echo roster_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','roster-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_3"></div></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Size','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[ptitle_fsize]" id="roster_slider_ptitle_fsize" class="small-text" value="<?php echo $roster_slider_curr['ptitle_fsize']; ?>" />&nbsp;<?php _e('px','roster-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Style','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[ptitle_fstyle]" id="roster_slider_ptitle_fstyle" >
<option value="bold" <?php if ($roster_slider_curr['ptitle_fstyle'] == "bold"){ echo "selected";}?> ><?php _e('Bold','roster-slider'); ?></option>
<option value="bold italic" <?php if ($roster_slider_curr['ptitle_fstyle'] == "bold italic"){ echo "selected";}?> ><?php _e('Bold Italic','roster-slider'); ?></option>
<option value="italic" <?php if ($roster_slider_curr['ptitle_fstyle'] == "italic"){ echo "selected";}?> ><?php _e('Italic','roster-slider'); ?></option>
<option value="normal" <?php if ($roster_slider_curr['ptitle_fstyle'] == "normal"){ echo "selected";}?> ><?php _e('Normal','roster-slider'); ?></option>
</select>
</td>
</tr>
</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Thumbnail Image','roster-slider'); ?></h2> 
<p><?php _e('Customize the looks of the thumbnail image for each of the sliding post here','roster-slider'); ?></p> 
<table class="form-table">

<tr valign="top"> 
<th scope="row"><?php _e('Image Pick Preferences','roster-slider'); ?> <small><?php _e('(The first one is having priority over second, the second having priority on third and so on)','roster-slider'); ?></small></th> 
<td><fieldset><legend class="screen-reader-text"><span><?php _e('Image Pick Sequence','roster-slider'); ?> <small><?php _e('(The first one is having priority over second, the second having priority on third and so on)','roster-slider'); ?></small> </span></legend> 
<input name="<?php echo $roster_slider_options;?>[img_pick][0]" type="checkbox" value="1" <?php if (isset ($roster_slider_curr['img_pick'][0])) checked('1', $roster_slider_curr['img_pick'][0]); ?>  /> <?php _e('Use Custom Field/Key','roster-slider'); ?> &nbsp; &nbsp; 
<input type="text" name="<?php echo $roster_slider_options;?>[img_pick][1]" class="text" value="<?php if (isset ($roster_slider_curr['img_pick'][1])) echo $roster_slider_curr['img_pick'][1]; ?>" /> <?php _e('Name of the Custom Field/Key','roster-slider'); ?>
<br />
<input name="<?php echo $roster_slider_options;?>[img_pick][2]" type="checkbox" value="1" <?php if (isset ($roster_slider_curr['img_pick'][2])) checked('1', $roster_slider_curr['img_pick'][2]); ?>  /> <?php _e('Use Featured Post/Thumbnail (Wordpress 3.0 +  feature)','roster-slider'); ?>&nbsp; <br />
<input name="<?php echo $roster_slider_options;?>[img_pick][3]" type="checkbox" value="1" <?php if (isset ($roster_slider_curr['img_pick'][3])) checked('1', $roster_slider_curr['img_pick'][3]); ?>  /> <?php _e('Consider Images attached to the post','roster-slider'); ?> &nbsp; &nbsp; 
<input type="text" name="<?php echo $roster_slider_options;?>[img_pick][4]" class="small-text" value="<?php if (isset ($roster_slider_curr['img_pick'][4])) echo $roster_slider_curr['img_pick'][4]; ?>" /> <?php _e('Order of the Image attachment to pick','roster-slider'); ?> &nbsp; <br />
<input name="<?php echo $roster_slider_options;?>[img_pick][5]" type="checkbox" value="1" <?php if (isset ($roster_slider_curr['img_pick'][5])) checked('1', $roster_slider_curr['img_pick'][5]); ?>  /> <?php _e('Scan images from the post, in case there is no attached image to the post','roster-slider'); ?>&nbsp; 
</fieldset></td> 
</tr> 

<tr valign="top">
<th scope="row"><?php _e('Align to','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[img_align]" id="roster_slider_img_align" >
<option value="left" <?php if ($roster_slider_curr['img_align'] == "left"){ echo "selected";}?> ><?php _e('Left','roster-slider'); ?></option>
<option value="right" <?php if ($roster_slider_curr['img_align'] == "right"){ echo "selected";}?> ><?php _e('Right','roster-slider'); ?></option>
<option value="none" <?php if ($roster_slider_curr['img_align'] == "none"){ echo "selected";}?> ><?php _e('Center','roster-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Wordpress Image Extract Size','roster-slider'); ?>
</th>
<td><select name="<?php echo $roster_slider_options;?>[crop]" id="roster_slider_img_crop" >
<option value="0" <?php if ($roster_slider_curr['crop'] == "0"){ echo "selected";}?> ><?php _e('Full','roster-slider'); ?></option>
<option value="1" <?php if ($roster_slider_curr['crop'] == "1"){ echo "selected";}?> ><?php _e('Large','roster-slider'); ?></option>
<option value="2" <?php if ($roster_slider_curr['crop'] == "2"){ echo "selected";}?> ><?php _e('Medium','roster-slider'); ?></option>
<option value="3" <?php if ($roster_slider_curr['crop'] == "3"){ echo "selected";}?> ><?php _e('Thumbnail','roster-slider'); ?></option>
</select>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('This is for fast page load, in case you choose \'Custom Size\' setting from below, you would not like to extract \'full\' size image from the media library. In this case you can use, \'medium\' or \'thumbnail\' image. This is because, for every image upload to the media gallery WordPress creates four sizes of the same image. So you can choose which to load in the slider and then specify the actual size.','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top"> 
<th scope="row"><?php _e('Image Size','roster-slider'); ?></th> 
<td><fieldset><legend class="screen-reader-text"><span><?php _e('Image Size','roster-slider'); ?></span></legend> 
<input name="<?php echo $roster_slider_options;?>[img_size]" type="radio" value="0" <?php checked('0', $roster_slider_curr['img_size']); ?>  /> <?php _e('Original Size','roster-slider'); ?> <small><?php _e('(In this case, the size would be equal to the extracted image (full/large/medium/thumbnail) from the above settings','roster-slider'); ?></small><br />
<input name="<?php echo $roster_slider_options;?>[img_size]" type="radio" value="1" <?php checked('1', $roster_slider_curr['img_size']); ?>  /> <?php _e('Custom Size:','roster-slider'); ?>&nbsp; 
<label for="<?php echo $roster_slider_options;?>[img_width]"><?php _e('Width','roster-slider'); ?></label>
<input type="text" name="<?php echo $roster_slider_options;?>[img_width]" class="small-text" value="<?php echo $roster_slider_curr['img_width']; ?>" />&nbsp;<?php _e('px','roster-slider'); ?> &nbsp;&nbsp; 
</fieldset></td> 
</tr> 

<tr valign="top">
<th scope="row"><?php _e('Maximum Height of the Image','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[img_height]" class="small-text" value="<?php echo $roster_slider_curr['img_height']; ?>" />&nbsp;<?php _e('px','roster-slider'); ?> &nbsp;&nbsp; <?php _e('(This is necessary in order to keep the maximum image height in control)','roster-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Border Thickness','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[img_border]" id="roster_slider_img_border" class="small-text" value="<?php echo $roster_slider_curr['img_border']; ?>" />&nbsp;<?php _e('px  (put 0 if no border is required)','roster-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Border Color','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[img_brcolor]" id="color_value_4" value="<?php echo $roster_slider_curr['img_brcolor']; ?>" />&nbsp; <img id="color_picker_4" src="<?php echo roster_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','roster-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_4"></div></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Make pure Image Slider','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[image_only]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['image_only']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('check this to convert Roster Slider to Image Slider with no content','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Grayscale image, show original image on hover','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[coloronhover]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['coloronhover']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('check this to show original colored image when user hovers on the image. Effect visible on Chrome 19+, IE 6-9, Firefox 10+, Safari 6+ . Not applicable to IE10','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Default Image','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[default_image]" id="roster_slider_default_image" class="regular-text code" value="<?php echo $roster_slider_curr['default_image']; ?>" />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('Enter the url of the default image i.e. the image to be displayed if there is no image available for the slide. By default, the url is <br />','roster-slider');echo '<span style="color:#0000ff;">'.$roster_slider_curr['default_image'].'</span>';?>
	</div>
</span>
</td>
</tr>

</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Slide Content','roster-slider'); ?></h2> 
<p><?php _e('Customize the looks of the content of each of the sliding post here','roster-slider'); ?></p> 
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Show content in slides below title','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[show_content]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['show_content']); ?>  /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Font','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[content_font]" id="roster_slider_content_font" >
<option value="Arial,Helvetica,sans-serif" <?php if ($roster_slider_curr['content_font'] == "Arial,Helvetica,sans-serif"){ echo "selected";}?> >Arial,Helvetica,sans-serif</option>
<option value="Verdana,Geneva,sans-serif" <?php if ($roster_slider_curr['content_font'] == "Verdana,Geneva,sans-serif"){ echo "selected";}?> >Verdana,Geneva,sans-serif</option>
<option value="Tahoma,Geneva,sans-serif" <?php if ($roster_slider_curr['content_font'] == "Tahoma,Geneva,sans-serif"){ echo "selected";}?> >Tahoma,Geneva,sans-serif</option>
<option value="Trebuchet MS,sans-serif" <?php if ($roster_slider_curr['content_font'] == "Trebuchet MS,sans-serif"){ echo "selected";}?> >Trebuchet MS,sans-serif</option>
<option value="'Century Gothic','Avant Garde',sans-serif" <?php if ($roster_slider_curr['content_font'] == "'Century Gothic','Avant Garde',sans-serif"){ echo "selected";}?> >'Century Gothic','Avant Garde',sans-serif</option>
<option value="'Arial Narrow',sans-serif" <?php if ($roster_slider_curr['content_font'] == "'Arial Narrow',sans-serif"){ echo "selected";}?> >'Arial Narrow',sans-serif</option>
<option value="'Arial Black',sans-serif" <?php if ($roster_slider_curr['content_font'] == "'Arial Black',sans-serif"){ echo "selected";}?> >'Arial Black',sans-serif</option>
<option value="'Gills Sans MT','Gills Sans',sans-serif" <?php if ($roster_slider_curr['content_font'] == "'Gills Sans MT','Gills Sans',sans-serif"){ echo "selected";} ?> >'Gills Sans MT','Gills Sans',sans-serif</option>
<option value="'Times New Roman',Times,serif" <?php if ($roster_slider_curr['content_font'] == "'Times New Roman',Times,serif"){ echo "selected";}?> >'Times New Roman',Times,serif</option>
<option value="Georgia,serif" <?php if ($roster_slider_curr['content_font'] == "Georgia,serif"){ echo "selected";}?> >Georgia,serif</option>
<option value="Garamond,serif" <?php if ($roster_slider_curr['content_font'] == "Garamond,serif"){ echo "selected";}?> >Garamond,serif</option>
<option value="'Century Schoolbook','New Century Schoolbook',serif" <?php if ($roster_slider_curr['content_font'] == "'Century Schoolbook','New Century Schoolbook',serif"){ echo "selected";}?> >'Century Schoolbook','New Century Schoolbook',serif</option>
<option value="'Bookman Old Style',Bookman,serif" <?php if ($roster_slider_curr['content_font'] == "'Bookman Old Style',Bookman,serif"){ echo "selected";}?> >'Bookman Old Style',Bookman,serif</option>
<option value="'Comic Sans MS',cursive" <?php if ($roster_slider_curr['content_font'] == "'Comic Sans MS',cursive"){ echo "selected";}?> >'Comic Sans MS',cursive</option>
<option value="'Courier New',Courier,monospace" <?php if ($roster_slider_curr['content_font'] == "'Courier New',Courier,monospace"){ echo "selected";}?> >'Courier New',Courier,monospace</option>
<option value="'Copperplate Gothic Bold',Copperplate,fantasy" <?php if ($roster_slider_curr['content_font'] == "'Copperplate Gothic Bold',Copperplate,fantasy"){ echo "selected";}?> >'Copperplate Gothic Bold',Copperplate,fantasy</option>
<option value="Impact,fantasy" <?php if ($roster_slider_curr['content_font'] == "Impact,fantasy"){ echo "selected";}?> >Impact,fantasy</option>
<option value="sans-serif" <?php if ($roster_slider_curr['content_font'] == "sans-serif"){ echo "selected";}?> >sans-serif</option>
<option value="serif" <?php if ($roster_slider_curr['content_font'] == "serif"){ echo "selected";}?> >serif</option>
<option value="cursive" <?php if ($roster_slider_curr['content_font'] == "cursive"){ echo "selected";}?> >cursive</option>
<option value="monospace" <?php if ($roster_slider_curr['content_font'] == "monospace"){ echo "selected";}?> >monospace</option>
<option value="fantasy" <?php if ($roster_slider_curr['content_font'] == "fantasy"){ echo "selected";}?> >fantasy</option>
</select>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('This value will be fallback font if Google web font value is specified below','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Google Web Font','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[content_fontg]" id="roster_slider_content_fontg" value="<?php echo htmlentities($roster_slider_curr['content_fontg'], ENT_QUOTES); ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Color','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[content_fcolor]" id="color_value_5" value="<?php echo $roster_slider_curr['content_fcolor']; ?>" />&nbsp; <img id="color_picker_5" src="<?php echo roster_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="Pick the color of your choice','roster-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_5"></div></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Size','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[content_fsize]" id="roster_slider_content_fsize" class="small-text" value="<?php echo $roster_slider_curr['content_fsize']; ?>" />&nbsp;<?php _e('px','roster-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Style','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[content_fstyle]" id="roster_slider_content_fstyle" >
<option value="bold" <?php if ($roster_slider_curr['content_fstyle'] == "bold"){ echo "selected";}?> ><?php _e('Bold','roster-slider'); ?></option>
<option value="bold italic" <?php if ($roster_slider_curr['content_fstyle'] == "bold italic"){ echo "selected";}?> ><?php _e('Bold Italic','roster-slider'); ?></option>
<option value="italic" <?php if ($roster_slider_curr['content_fstyle'] == "italic"){ echo "selected";}?> ><?php _e('Italic','roster-slider'); ?></option>
<option value="normal" <?php if ($roster_slider_curr['content_fstyle'] == "normal"){ echo "selected";}?> ><?php _e('Normal','roster-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Pick content From','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[content_from]" id="roster_slider_content_from" >
<option value="slider_content" <?php if ($roster_slider_curr['content_from'] == "slider_content"){ echo "selected";}?> ><?php _e('Slider Content Custom field','roster-slider'); ?></option>
<option value="excerpt" <?php if ($roster_slider_curr['content_from'] == "excerpt"){ echo "selected";}?> ><?php _e('Post Excerpt','roster-slider'); ?></option>
<option value="content" <?php if ($roster_slider_curr['content_from'] == "content"){ echo "selected";}?> ><?php _e('From Content','roster-slider'); ?></option>
</select>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Maximum content size (in words)','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[content_limit]" id="roster_slider_content_limit" class="small-text" value="<?php echo $roster_slider_curr['content_limit']; ?>" />&nbsp;<?php _e('words','roster-slider'); ?>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('if specified will override the \'Maximum Content Size in Chracters\' setting below','roster-slider'); ?>
	</div>
</span>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Maximum content size (in characters)','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[content_chars]" id="roster_slider_content_chars" class="small-text" value="<?php echo $roster_slider_curr['content_chars']; ?>" />&nbsp;<?php _e('characters','roster-slider'); ?> </td>
</tr>

</table>

</div>
</div> <!-- slider_content tab ends-->

<div id="slider_nav">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Navigational Arrows','roster-slider'); ?></h2> 

<table class="form-table">
<tr valign="top"> 
<th scope="row"><?php _e('Hide Prev/Next navigation arrows','roster-slider'); ?></th> 
<td><label for="roster_slider_prev_next"> 
<input name="<?php echo $roster_slider_options;?>[prev_next]" type="checkbox" id="roster_slider_prev_next" value="1" <?php checked("1", $roster_slider_curr['prev_next']); ?> /> 
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Prev-Next Buttons Folder','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[buttons]" >
<?php 
$directory = ROSTER_SLIDER_CSS_DIR.$roster_slider['stylesheet'].'/buttons/';
if ($handle = opendir($directory)) {
    while (false !== ($file = readdir($handle))) { 
     if($file != '.' and $file != '..') { ?>
      <option value="<?php echo $file;?>" <?php if ($roster_slider_curr['buttons'] == $file){ echo "selected";}?> ><?php echo $file;?></option>
 <?php  } }
    closedir($handle);
}
?>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Navigation Button Distance from Top of the Slider','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[navtop]" id="roster_slider_navtop" class="small-text" value="<?php echo $roster_slider_curr['navtop']; ?>" />% <small><?php _e(' (%)','roster-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Navigation Button Width','roster-slider'); ?></th>
<td><input type="text" name="<?php echo $roster_slider_options;?>[navw]" id="roster_slider_navw" class="small-text" value="<?php echo $roster_slider_curr['navw']; ?>" />px</td>
</tr>

</table>

<p class="submit" <?php echo $notran;?>>
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div <?php echo $notran;?>>
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Navigational Numbers','roster-slider'); ?></h2> 

<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Show Navigation Numbers','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[navnum]" >
<option value="0" <?php if ($roster_slider_curr['navnum'] == "0"){ echo "selected";}?> ><?php _e('No','roster-slider'); ?></option>
<option value="1" <?php if ($roster_slider_curr['navnum'] == "1"){ echo "selected";}?> ><?php _e('Bottom of Slider','roster-slider'); ?></option>
<option value="2" <?php if ($roster_slider_curr['navnum'] == "2"){ echo "selected";}?> ><?php _e('Top of Slider','roster-slider'); ?></option>
</select>
</td>
</tr>
</table>

</div></div>

</div><!-- slider_nav tab ends-->

<div id="responsive">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Responsive Design Settings','roster-slider'); ?></h2> 

<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Enable Responsive Design','roster-slider'); ?></th>
<td><input name="<?php echo $roster_slider_options;?>[responsive]" type="checkbox" value="1" <?php checked('1', $roster_slider_curr['responsive']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('check this if you want to enable the responsive layout for Roster (you should be using Responsive/Fluid WordPress theme for this feature to work!)','roster-slider'); ?>
	</div>
</span>
</td>
</tr>
</table>
</div>

</div> <!--#responsive-->

<div id="preview">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:0;">
<h2><?php _e('Preview on Settings Panel','roster-slider'); ?></h2> 

<table class="form-table">

<tr valign="top"> 
<th scope="row"><label for="roster_slider_disable_preview"><?php _e('Disable Preview Section','roster-slider'); ?></label></th> 
<td> 
<input name="<?php echo $roster_slider_options;?>[disable_preview]" type="checkbox" id="roster_slider_disable_preview" value="1" <?php checked("1", $roster_slider_curr['disable_preview']); ?> />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('If disabled, the \'Preview\' of Slider on this Settings page will be removed.','roster-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Roster Template Tag for Preview','roster-slider'); ?></th>
<td><select name="<?php echo $roster_slider_options;?>[preview]" >
<option value="2" <?php if ($roster_slider_curr['preview'] == "2"){ echo "selected";}?> ><?php _e('Recent Posts Slider','roster-slider'); ?></option>
<option value="1" <?php if ($roster_slider_curr['preview'] == "1"){ echo "selected";}?> ><?php _e('Category Slider','roster-slider'); ?></option>
<option value="0" <?php if ($roster_slider_curr['preview'] == "0"){ echo "selected";}?> ><?php _e('Custom Slider with Slider ID','roster-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top"> 
<th scope="row"><?php _e('Preview Slider Params','roster-slider'); ?></th> 
<td><fieldset><legend class="screen-reader-text"><span><?php _e('Preview Slider Params','roster-slider'); ?></span></legend> 
<label for="<?php echo $roster_slider_options;?>[slider_id]"><?php _e('Slider ID in case of Custom Slider','roster-slider'); ?></label>
<input type="text" name="<?php echo $roster_slider_options;?>[slider_id]" class="small-text" value="<?php echo $roster_slider_curr['slider_id']; ?>" /> 
<br />  <br />
<label for="<?php echo $roster_slider_options;?>[catg_slug]"><?php _e('Category Slug in case of Category Slider','roster-slider'); ?></label>
<input type="text" name="<?php echo $roster_slider_options;?>[catg_slug]" class="regular-text code" style="width:100px;" value="<?php echo $roster_slider_curr['catg_slug']; ?>" /> 
</fieldset></td> 
</tr> 

</table>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Shortcode','roster-slider'); ?></h2> 
<p><?php _e('Paste the below shortcode on Page/Post Edit Panel to get the slider as shown in the above Preview','roster-slider'); ?></p><br />
<?php if($cntr=='') $s_set='1'; else $s_set=$cntr;
if ($roster_slider_curr['preview'] == "0")
	echo '[rosterslider id="'.$roster_slider_curr['slider_id'].'" set="'.$s_set.'"]';
elseif($roster_slider_curr['preview'] == "1")
	echo '[rostercategory catg_slug="'.$roster_slider_curr['catg_slug'].'" set="'.$s_set.'"]';
else
	echo '[rosterrecent set="'.$s_set.'"]';
?>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Template Tag','roster-slider'); ?></h2> 
<p><?php _e('Paste the below template tag in your theme template file like index.php or page.php at required location to get the slider as shown in the above Preview','roster-slider'); ?></p><br />
<?php 
if ($roster_slider_curr['preview'] == "0")
	echo '<code>&lt;?php if(function_exists("get_roster_slider")){get_roster_slider($slider_id="'.$roster_slider_curr['slider_id'].'",$set="'.$s_set.'");}?&gt;</code>';
elseif($roster_slider_curr['preview'] == "1")
	echo '<code>&lt;?php if(function_exists("get_roster_slider_category")){get_roster_slider_category($catg_slug="'.$roster_slider_curr['catg_slug'].'",$set="'.$s_set.'");}?&gt;</code>';
else
	echo '<code>&lt;?php if(function_exists("get_roster_slider_recent")){get_roster_slider_recent($set="'.$s_set.'");}?&gt;</code>';
?>
</div>

</div><!-- preview tab ends-->

<div id="cssvalues">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('CSS Generated thru these settings','roster-slider'); ?></h2> 
<p><?php _e('Save Changes for the settings first and then view this data. You can use this CSS in your \'custom\' stylesheets if you use other than \'default\' value for the Stylesheet folder.','roster-slider'); ?></p> 
<?php $roster_slider_css = roster_get_inline_css($cntr,$echo='1'); ?>
<div style="font-family:monospace;font-size:13px;background:#ddd;">
.roster_slider_set<?php echo $cntr;?>{<?php echo $roster_slider_css['roster_slider'];?>} <br />
.roster_slider_set<?php echo $cntr;?> .sldr_title{<?php echo $roster_slider_css['sldr_title'];?>} <br />
.roster_slider_set<?php echo $cntr;?> .roster_slideri{<?php echo $roster_slider_css['roster_slideri'];?>} <br />
.roster_slider_set<?php echo $cntr;?> .roster_slider_thumbnail{<?php echo $roster_slider_css['roster_slider_thumbnail'];?>} <br />
.roster_slider_set<?php echo $cntr;?> .roster_slideri h2{<?php echo $roster_slider_css['roster_slider_h2'];?>} <br />
.roster_slider_set<?php echo $cntr;?> .roster_slideri h2 a{<?php echo $roster_slider_css['roster_slider_h2_a'];?>} <br />
.roster_slider_set<?php echo $cntr;?> .roster_slideri span{<?php echo $roster_slider_css['roster_slider_span'];?>} <br />
.roster_slider_set<?php echo $cntr;?> .roster_slideri p.more{<?php echo $roster_slider_css['roster_slider_p_more'];?>} <br />
.roster_slider_set<?php echo $cntr;?> .roster_next{<?php echo $roster_slider_css['roster_next'];?>} <br />
.roster_slider_set<?php echo $cntr;?> .roster_prev{<?php echo $roster_slider_css['roster_prev'];?>} 
</div>
</div>
</div> <!--#cssvalues-->

<div class="svilla_cl"></div><div class="svilla_cr"></div>
</div> <!--end of tabs -->

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

<input type="hidden" name="<?php echo $roster_slider_options;?>[active_tab]" id="roster_activetab" value="<?php echo $roster_slider_curr['active_tab']; ?>" />
</form>

<!--Form to reset Settings set-->
<form style="float:left;" action="" method="post">
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Reset Settings to','roster-slider'); ?></th>
<td><select name="roster_reset_settings" id="roster_slider_reset_settings" >
<option value="n" selected ><?php _e('None','roster-slider'); ?></option>
<option value="g" ><?php _e('Global Default','roster-slider'); ?></option>

<?php 
for($i=1;$i<=$scounter;$i++){
	if ($i==1){
	  echo '<option value="'.$i.'" >'.__('Default Settings Set','roster-slider').'</option>';
	}
	else {
	  if($settings_set=get_option('roster_slider_options'.$i)){
		echo '<option value="'.$i.'" >'.$settings_set['setname'].' (ID '.$i.')</option>';
	  }
	}
}
?>

</select>
</td>
</tr>
</table>

<p class="submit">
<input name="roster_reset_settings_submit" type="submit" class="button-primary" value="<?php _e('Reset Settings') ?>" />
</p>
</form>

<div class="svilla_cl"></div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:0;" id="import">
<?php if (isset ($imported_settings_message))echo $imported_settings_message;?>
<h3><?php _e('Import Settings Set by uploading a Settings File','roster-slider'); ?></h3>
<form style="margin-right:10px;font-size:14px;" action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
<input type="file" name="settings_file" id="settings_file" style="font-size:13px;width:50%;padding:0 5px;" />
<input type="submit" value="Import" name="import"  onclick="return confirmSettingsImport()" title="<?php _e('Import Settings from a file','roster-slider'); ?>" class="button-primary" />
</form>
</div>

</div> <!--end of float left -->

<div id="poststuff" class="metabox-holder has-right-sidebar" style="float:left;width:28%;max-width:350px;min-width:inherit;"> 
<?php $url = roster_sslider_admin_url( array( 'page' => 'roster-slider-admin' ) );?>
<form style="margin-right:10px;font-size:14px;width:100%;" action="" method="post">
<a href="<?php echo $url; ?>" title="<?php _e('Go to Sliders page where you can re-order the slide posts, delete the slides from the slider etc.','roster-slider'); ?>" class="svilla_button svilla_gray_button"><?php _e('Go to Sliders Admin','roster-slider'); ?></a>
<input type="submit" class="svilla_button" style="font-size:13px;" value="Create New Settings Set" name="create_set"  onclick="return confirmSettingsCreate()" />
<input type="submit" value="Export" name="export" title="<?php _e('Export this Settings Set to a file','roster-slider'); ?>" class="svilla_button" />
<a href="#import" title="<?php _e('Go to Import Settings Form','roster-slider'); ?>" class="svilla_button">Import</a>
</form>
<div class="svilla_cl"></div>

<div class="postbox" style="margin:10px 0;"> 
			  <h3 class="hndle"><span></span><?php _e('Available Settings Sets','roster-slider'); ?></h3> 
			  <div class="inside">
<?php 
for($i=1;$i<=$scounter;$i++){
   if ($i==1){
      echo '<h4><a href="'.roster_sslider_admin_url( array( 'page' => 'roster-slider-settings' ) ).'" title="(Settings Set ID '.$i.')">Default Settings (ID '.$i.')</a></h4>';
   }
   else {
      if($settings_set=get_option('roster_slider_options'.$i)){
		echo '<h4><a href="'.roster_sslider_admin_url( array( 'page' => 'roster-slider-settings' ) ).'&scounter='.$i.'" title="(Settings Set ID '.$i.')">'.$settings_set['setname'].' (ID '.$i.')</a></h4>';
	  }
   }
}
?>
</div></div>

<div class="postbox"> 
<div style="background:#eee;line-height:200%"><a style="text-decoration:none;font-weight:bold;font-size:100%;color:#990000" href="http://guides.slidervilla.com/roster-slider/" title="Click here to read how to use the plugin and frequently asked questions about the plugin" target="_blank"> ==> Usage Guide and General FAQs</a></div>
</div>

<?php if ($roster_slider['support'] == "1"){ ?>
    
     		<div class="postbox"> 
			  <h3 class="hndle"><span></span><?php _e('Recommended Themes','roster-slider'); ?></h3> 
			  <div class="inside">
                     <div style="margin:10px 5px">
                        <a href="http://slidervilla.com/go/elegantthemes/" title="Recommended WordPress Themes" target="_blank"><img src="<?php echo roster_slider_plugin_url('images/elegantthemes.gif');?>" alt="Recommended WordPress Themes" style="width:100%;" /></a>
                        <p><a href="http://slidervilla.com/go/elegantthemes/" title="Recommended WordPress Themes" target="_blank">Elegant Themes</a> are attractive, compatible, affordable, SEO optimized WordPress Themes and have best support in community.</p>
                        <p><strong>Beautiful themes, Great support!</strong></p>
                        <p><a href="http://slidervilla.com/go/elegantthemes/" title="Recommended WordPress Themes" target="_blank">For more info visit ElegantThemes</a></p>
                     </div>
               </div></div>
<?php } ?>          
			<div class="postbox"> 
			<div class="postbox"> 
			  <h3 class="hndle"><span><?php _e('About this Plugin:','roster-slider'); ?></span></h3> 
			  <div class="inside">
                <ul>
                <li><a href="http://slidervilla.com/roster/" title="<?php _e('Roster Slider Homepage','roster-slider'); ?>
" ><?php _e('Plugin Homepage','roster-slider'); ?></a></li>
				<li><a href="http://support.slidervilla.com/" title="<?php _e('Support Forum','roster-slider'); ?>
" ><?php _e('Support Forum','roster-slider'); ?></a></li>
				<li><a href="http://guides.slidervilla.com/roster-slider/" title="<?php _e('Usage Guide','roster-slider'); ?>
" ><?php _e('Usage Guide','roster-slider'); ?></a></li>
				<li><strong>Version: 1.7</strong></li>
                </ul> 
              </div> 
			</div> 

                 
 </div> <!--end of poststuff --> 

<div style="clear:left;"></div>
<div style="clear:right;"></div>

</div> <!--end of float wrap -->
<?php	
}
function register_roster_settings() { // whitelist options
  $scounter=get_option('roster_slider_scounter');
  for($i=1;$i<=$scounter;$i++){
	   if ($i==1){
		  register_setting( 'roster-slider-group', 'roster_slider_options' );
	   }
	   else {
	      $group='roster-slider-group'.$i;
		  $options='roster_slider_options'.$i;
		  register_setting( $group, $options );
	   }
  }
}
?>