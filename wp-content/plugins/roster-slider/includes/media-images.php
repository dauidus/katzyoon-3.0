<?php
//For media files
function roster_slider_media_lib_edit($form_fields, $post){
global $wp_version;
if ( version_compare( $wp_version, '3.5', '<' ) ) : // Using WordPress less than 3.5
	global $roster_slider;
	if (current_user_can( $roster_slider['user_level'] )) {
		$remove_post_type_arr=$roster_slider['remove_metabox'];
		if(!isset($remove_post_type_arr) or !is_array($remove_post_type_arr) ) $remove_post_type_arr=array();
		if(!in_array('attachment',$remove_post_type_arr)){
			if ( substr($post->post_mime_type, 0, 5) == 'image') {
				$post_id = $post->ID;
				$extra = "";

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
				
					  
			  $form_fields['roster-slider'] = array(
					  'label'      => __('Check the box and select the slider','roster-slider'),
					  'input'      => 'html',
					  'html'       => "<input type='checkbox' style='margin-top:6px;' name='attachments[".$post->ID."][roster-slider]' value='roster-slider' ".$extra." /> &nbsp; <strong>".__( 'Add this Image to ', 'roster-slider' )."</strong>",
					  'value'      => 'roster-slider'
				   );
			  
			  $sname_html='';
		 
			  foreach ($sliders as $slider) { 
				 if(in_array($slider['slider_id'],$post_slider_arr)){$selected = 'selected';} else{$selected='';}
				 $sname_html =$sname_html.'<option value="'.$slider['slider_id'].'" '.$selected.'>'.$slider['slider_name'].'</option>';
			  } 
			  $form_fields['roster_slider_name[]'] = array(
					  'label'      => __(''),
					  'input'      => 'html',
					  'html'       => '<select name="attachments['.$post->ID.'][roster_slider_name][]" multiple="multiple" size="3">'.$sname_html.'</select>',
					  'value'      => ''
				   );
			 
			 $roster_link_attr=get_post_meta($post_id, 'roster_link_attr', true);
			 $roster_sslider_link= get_post_meta($post_id, 'roster_slide_redirect_url', true);  
			 $roster_sslider_nolink=get_post_meta($post_id, 'roster_sslider_nolink', true);
			 if($roster_sslider_nolink=='1'){$checked= "checked";} else {$checked= "";}
			 $form_fields['roster_link_attr'] = array(
					  'label'      => __('Slide Link (anchor) attributes','roster-slider'),
					  'input'      => 'html',
					  'html'       => "<input type='text' style='clear:left;' class='text urlfield' name='attachments[".$post->ID."][roster_link_attr]' value='" . esc_attr($roster_link_attr) . "' /><br /><small>".__( '(e.g. target="_blank" rel="external nofollow")', 'roster-slider' )."</small>",
					  'value'      => $roster_link_attr
				   );
			 $form_fields['roster_sslider_link'] = array(
					  'label'      => __('Roster Slide Link URL','roster-slider'),
					  'input'      => 'html',
					  'html'       => "<input type='text' style='clear:left;' class='text urlfield' name='attachments[".$post->ID."][roster_sslider_link]' value='" . esc_attr($roster_sslider_link) . "' /><br /><small>".__( '(If left empty, it will be by default linked to attachment permalink.)', 'roster-slider' )."</small>",
					  'value'      => $roster_sslider_link
				   );
			 $form_fields['roster_sslider_nolink'] = array(
					  'label'      => __('Do not link this slide to any page(url)','roster-slider'),
					  'input'      => 'html',
					  'html'       => "<input type='checkbox' name='attachments[".$post->ID."][roster_sslider_nolink]' value='1' ".$checked." />",
					  'value'      => 'roster-slider'
				   );
		  }
		  else {
			 unset( $form_fields['roster-slider'] );
			 unset( $form_fields['roster_slider_name[]'] );
			 unset( $form_fields['roster_sslider_link'] );
			 unset( $form_fields['roster_sslider_nolink'] );
			 unset( $form_fields['roster_link_attr'] );
		  }
		} //attachment post type
	} //current user can
endif; //less than WP 3.5
return $form_fields;
}

add_filter('attachment_fields_to_edit', 'roster_slider_media_lib_edit', 10, 2);

function roster_slider_media_lib_save($post, $attachment){
global $wp_version;
if ( version_compare( $wp_version, '3.5', '<' ) ) : // Using WordPress less than 3.5
	global $roster_slider;
	if (current_user_can( $roster_slider['user_level'] )) {
		$remove_post_type_arr=$roster_slider['remove_metabox'];
		if(!isset($remove_post_type_arr) or !is_array($remove_post_type_arr) ) $remove_post_type_arr=array();
		if(!in_array('attachment',$remove_post_type_arr)){
			global $wpdb, $table_prefix;
			$table_name = $table_prefix.ROSTER_SLIDER_TABLE;
			$post_id=$post['ID'];
			
			if(isset($attachment['roster-slider']) and !isset($attachment['roster_slider_name'])) {
			  $slider_id = '1';
			  if(is_post_on_any_roster_slider($post_id)){
				 $sql = "DELETE FROM $table_name where post_id = '$post_id'";
				 $wpdb->query($sql);
			  }
			  
			  if(isset($attachment['roster-slider']) and $attachment['roster-slider'] == "roster-slider" and !roster_slider($post_id,$slider_id)) {
				$dt = date('Y-m-d H:i:s');
				$sql = "INSERT INTO $table_name (post_id, date, slider_id) VALUES ('$post_id', '$dt', '$slider_id')";
				$wpdb->query($sql);
			  }
			}
			if(isset($attachment['roster-slider']) and $attachment['roster-slider'] == "roster-slider" and isset($attachment['roster_slider_name'])){
			  $slider_id_arr = $attachment['roster_slider_name'];
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
			
			$roster_link_attr = get_post_meta($post_id,'roster_link_attr',true);
			$link_attr=htmlentities($_POST['roster_link_attr'],ENT_QUOTES);
			if($roster_link_attr != $link_attr) {
			  update_post_meta($post_id, 'roster_link_attr', $link_attr);	
			}
		
			$roster_sslider_link = get_post_meta($post_id,'roster_slide_redirect_url',true);
			$link=$attachment['roster_sslider_link'];
			if($roster_sslider_link != $link) {
			  update_post_meta($post_id, 'roster_slide_redirect_url', $link);	
			}
			
			$roster_sslider_nolink = get_post_meta($post_id,'roster_sslider_nolink',true);
			if($roster_sslider_nolink != $attachment['roster_sslider_nolink']) {
			  update_post_meta($post_id, 'roster_sslider_nolink', $attachment['roster_sslider_nolink']);	
			}
		} //attachment post type
	} //current user can
endif; //less than WP 3.5
return $post;
} 

add_filter('attachment_fields_to_save', 'roster_slider_media_lib_save', 10, 2);
?>