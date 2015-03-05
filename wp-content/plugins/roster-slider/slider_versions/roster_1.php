<?php 
function roster_global_data_processor( $slides, $roster_slider_curr,$out_echo,$set,$data=array() ){
	global $roster_slider;
	$roster_slider_css = roster_get_inline_css($set);
	$html = '';
	$roster_sldr_j = $i = 0;
	$page_close='';
	
	$cycle='1';
	if( $roster_slider['extend']=='1' or ( $roster_slider['extend']=='2' and ($roster_slider_curr['visible'] != $roster_slider_curr['scroll']) ) or $roster_slider_curr['responsive'] == '1'  ){
		$cycle=0;
	}
	
	$coloronhover_class='';
	if($roster_slider_curr['coloronhover'] == '1') {
		$coloronhover_class='roster_img_gray';
	}
	
	if(is_array($data)) extract($data,EXTR_PREFIX_ALL,'data');
	
	$slider_handle='';
	if ( !empty($data_slider_handle) ) {
		$slider_handle=$data_slider_handle;
	}	
	
	foreach($slides as $slide) {
		$id = $post_id = '';
		if (isset ($slide->ID)) {$id = $post_id = $slide->ID;}
		$post_title = stripslashes($slide->post_title);
		$post_title = str_replace('"', '', $post_title);
		//filter hook
		if (isset($id))
		$post_title=apply_filters('roster_post_title',$post_title,$id,$roster_slider_curr,$roster_slider_css);
		$slider_content = $slide->post_content;
		
		$roster_slide_redirect_url = $slide->redirect_url;
		$roster_sslider_nolink = $slide->nolink;
		trim($roster_slide_redirect_url);
		if(!empty($roster_slide_redirect_url) and isset($roster_slide_redirect_url)) {
		   $permalink = $roster_slide_redirect_url;
		}
		else{
		   $permalink = $slide->url;
		}
		if($roster_sslider_nolink=='1'){
		  $permalink='';
		}
			
		if( $cycle=='0' ) $roster_sldr_j++;
		if($i%$roster_slider_curr['visible'] == 0 and $cycle=='1' ){
		$html .= '<div class="roster_slide" '.$roster_slider_css['roster_slide'].'>
			<!-- roster_slide -->';
			$roster_sldr_j++;
		}
		
		$html .= '<div class="roster_slideri" '.$roster_slider_css['roster_slideri'].'>
			<!-- roster_slideri -->';
			
		if($roster_slider_curr['show_content']=='1'){
			if ($roster_slider_curr['content_from'] == "slider_content") {
				$slider_content = $slide->post_content;
				//echo $slider_content;
			}
			if ($roster_slider_curr['content_from'] == "excerpt") {
				$slider_content = $slide->post_excerpt;
				//echo $slider_content;
			}
			
			$slider_content = strip_shortcodes( $slider_content );

			$slider_content = stripslashes($slider_content);
			$slider_content = str_replace(']]>', ']]&gt;', $slider_content);
	
			$slider_content = str_replace("\n","<br />",$slider_content);
			$slider_content = strip_tags($slider_content, $roster_slider_curr['allowable_tags']);
			
			if(!$roster_slider_curr['content_limit'] or $roster_slider_curr['content_limit'] == '' or $roster_slider_curr['content_limit'] == ' ') 
			  $slider_excerpt = substr($slider_content,0,$roster_slider_curr['content_chars']);
			else  			  
			  $slider_excerpt = roster_slider_word_limiter( $slider_content, $limit = $roster_slider_curr['content_limit'] );
			//filter hook
			$slider_excerpt=apply_filters('roster_slide_excerpt',$slider_excerpt,$post_id,$roster_slider_curr,$roster_slider_css);
			$slider_excerpt='<span '.$roster_slider_css['roster_slider_span'].'> '.$slider_excerpt.'</span>';
		}
		else{
		    $slider_excerpt='';
		}
		//filter hook
			$slider_excerpt=apply_filters('roster_slide_excerpt_html',$slider_excerpt,$post_id,$roster_slider_curr,$roster_slider_css);
		
		$roster_fields=$roster_slider_curr['fields'];		
		$fields_html='';
		if($roster_fields and !empty($roster_fields) ){
			$fields=explode( ',', $roster_fields );
			if($fields){
				foreach($fields as $field) {
					if (isset ($field))	$field_val = ( isset($slide->$field) ) ? ( $slide->$field ) : '' ;
					if( $field_val and !empty($field_val) )
						$fields_html .='<div class="roster_'.$field.' roster_fields">'.$field_val.'</div>';
				}
			}
		}

		//Slide link anchor attributes
		$a_attr='';$imglink='';
		if (isset ($slide->roster_link_attr))
		$a_attr=$slide->roster_link_attr;
		if( empty($a_attr) and isset( $roster_slider_curr['a_attr'] ) ) $a_attr=$roster_slider_curr['a_attr'];
		$a_attr_orig=$a_attr;
		if( isset($roster_slider_curr['pphoto'])  and $roster_slider_curr['pphoto'] == '1' ){
			if($roster_slider_curr['pphoto'] == '1') $a_attr.=' rel="prettyPhoto"';
			if(!empty($roster_slide_redirect_url) and isset($roster_slide_redirect_url))
				$imglink=$roster_slide_redirect_url;
			else $imglink='1';
		}
		
		//For media images
		if (isset ($slide->media)) $roster_media = $slide->media;
		if (isset ($slide->media_image)) $roster_media_image = $slide->media_image;
		
		if( ((empty($roster_media) or $roster_media=='' or !($roster_media)) and (empty($roster_media_image) or $roster_media_image=='' or !($roster_media_image)) ) or $data_media!='1' ) {
			$width = $roster_slider_curr['img_width'];
			$height = $roster_slider_curr['img_height'];
			
			if($roster_slider_curr['crop'] == '0'){
			 $extract_size = 'full';
			}
			elseif($roster_slider_curr['crop'] == '1'){
			 $extract_size = 'large';
			}
			elseif($roster_slider_curr['crop'] == '2'){
			 $extract_size = 'medium';
			}
			else{
			 $extract_size = 'thumbnail';
			}
			
			$classes[] = $extract_size;
			$classes[] = 'roster_slider_thumbnail '.$coloronhover_class;
			$classes[] = $data_image_class;
			$class = join( ' ', array_unique( $classes ) );
	
			preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $slide->content_for_image, $matches );

			$img_url=$data_default_image;
			/* If there is a match for the image, return its URL. */
			if(isset($data_order)) $order_of_image=$data_order;
			
			if($order_of_image > 0) $order_of_image=$order_of_image; 
			else $order_of_image = 0;
			
			if ( isset( $matches ) && count($matches[1])<=$order_of_image) $order_of_image=count($matches[1]);
			
			if ( isset( $matches ) && $matches[1][$order_of_image] )
				$img_url = $matches[1][$order_of_image];
			
			$width = ( ( $width ) ? ' width="' . esc_attr( $width ) . '"' : '' );
			$height = ( ( $height ) ? ' height="' . esc_attr( $height ) . '"' : '' );
			
			$img_html = '<img src="' . $img_url . '" class="' . esc_attr( $class ) . '"' . $width . $height . $roster_slider_css['roster_slider_thumbnail'] .' />';
			
			//Prettyphoto Integration	
			$ipermalink=$permalink;
			if($imglink=='1' and $permalink!='') $ipermalink=$img_url;
			elseif($imglink=='') $ipermalink=$permalink;
			else {
				if($permalink!='')$ipermalink=$imglink;
			}
			
			if($permalink!='') {
			  $img_html = '<a href="' . $ipermalink . '" title="'.$post_title.'" '.$a_attr.'>' . $img_html . '</a>';
			}
				
			$roster_large_image=$img_html;
		}
		else{
			if(!empty($roster_media)){
				$roster_large_image=$roster_media;
			}
			else{
				$width = $roster_slider_curr['img_width'];
				$height = $roster_slider_curr['img_height'];
				$width = ( ( $width ) ? ' width="' . esc_attr( $width ) . '"' : '' );
				$height = ( ( $height ) ? ' height="' . esc_attr( $height ) . '"' : '' );
				
				if($roster_slider_curr['crop'] == '0'){
				 $extract_size = 'full';
				}
				elseif($roster_slider_curr['crop'] == '1'){
				 $extract_size = 'large';
				}
				elseif($roster_slider_curr['crop'] == '2'){
				 $extract_size = 'medium';
				}
				else{
				 $extract_size = 'thumbnail';
				}
				
				$classes[] = $extract_size;
				$classes[] = 'roster_slider_thumbnail '.$coloronhover_class;
				$classes[] = $data_image_class;
				$class = join( ' ', array_unique( $classes ) );
				if(!empty($roster_media_image)) {
					$roster_large_image='<img src="'.$roster_media_image.'" class="' . esc_attr( $class ) . '"' . $width . $height . '/>';
					$img_url=$roster_media_image;
				}
				else {
					$roster_large_image='<img src="'.$data_default_image.'" class="' . esc_attr( $class ) . '"' . $width . $height . '/>';
					$img_url=$data_default_image;
				}
				
				//Prettyphoto Integration	
				$ipermalink=$permalink;
				if($imglink=='1' and $permalink!='') $ipermalink=$img_url;
				elseif($imglink=='') $ipermalink=$permalink;
				else {
					if($permalink!='')$ipermalink=$imglink;
				}
				
				if($permalink!='') {
				  $roster_large_image = '<a href="' . $ipermalink . '" title="'.$post_title.'" '.$a_attr.'>' . $roster_large_image . '</a>';
				}
			}
		}
		
		//filter hook
		$roster_large_image=apply_filters('roster_large_image',$roster_large_image,$post_id,$roster_slider_curr,$roster_slider_css);
		$html .= $roster_large_image;
		
		$page_close='';
		if( ($i%$roster_slider_curr['visible'] == ($roster_slider_curr['visible']-1) ) and $cycle=='1' ){$page_close='</div><!-- /roster_slide -->';}
		  		
		if ($roster_slider_curr['image_only'] == '1') { 
			$html .= '<!-- /roster_slideri -->
			</div>'.$page_close;
		}
		else {
		   if($permalink!='') {
			$slide_title = '<h2 '.$roster_slider_css['roster_slider_h2'].'><a href="'.$permalink.'" '.$roster_slider_css['roster_slider_h2_a'].' '.$a_attr_orig.'>'.$post_title.'</a></h2>';
			//filter hook
		   $slide_title=apply_filters('roster_slide_title_html',$slide_title,$post_id,$roster_slider_curr,$roster_slider_css,$post_title);
			$html .= $slide_title.$slider_excerpt.$fields_html;
			if($roster_slider_curr['show_content']=='1'){
			  $html .= '<p class="more"><a href="'.$permalink.'" '.$roster_slider_css['roster_slider_p_more'].' '.$a_attr_orig.'>'.$roster_slider_curr['more'].'</a></p>';
			}
			 $html .= '	<!-- /roster_slideri -->
			</div>'.$page_close; }
		   else{
		   $slide_title = '<h2 '.$roster_slider_css['roster_slider_h2'].'>'.$post_title.'</h2>';
		   //filter hook
		   $slide_title=apply_filters('roster_slide_title_html',$slide_title,$post_id,$roster_slider_curr,$roster_slider_css,$post_title);
		   $html .= $slide_title.$slider_excerpt.$fields_html.'
				<!-- /roster_slideri -->
			</div>'.$page_close;    }
		}
	  $i++;
	}
	if( ($page_close=='' or empty($page_close)) and $slides and $cycle=='1' ){$html=$html.'</div><!-- /roster_slide -->';}
	//filter hook
	$html=apply_filters('roster_extract_html',$html,$roster_sldr_j,$slides,$roster_slider_curr);
	if($out_echo == '1') {
	   echo $html;
	}
	$r_array = array( $roster_sldr_j, $html);
	$r_array=apply_filters('roster_r_array',$r_array,$slides, $roster_slider_curr,$set);
	return $r_array;
}
function roster_global_posts_processor( $posts, $roster_slider_curr,$out_echo,$set,$data=array() ){
	global $roster_slider,$default_roster_slider_settings;
	$roster_slider_css = roster_get_inline_css($set);
	$html = '';
	$roster_sldr_j = $i = 0;
	$page_close='';
	$cycle='1';
	
	foreach($default_roster_slider_settings as $key=>$value){
		if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
	}
	
	if( $roster_slider['extend']=='1' or ( $roster_slider['extend']=='2' and ($roster_slider_curr['visible'] != $roster_slider_curr['scroll']) ) or $roster_slider_curr['responsive'] == '1'  ){
		$cycle=0;
	}
	
	$coloronhover_class='';
	if($roster_slider_curr['coloronhover'] == '1') {
		$coloronhover_class='roster_img_gray';
	}
	
	foreach($posts as $post) {
		$id = $post_id = $post->ID;
		$post_title = get_post_meta($id, 'SlideTitle', true);
		if(empty($post_title)) {
			$post_title = stripslashes($post->post_title);
			$post_title = str_replace('"', '', $post_title);
		}
		//filter hook
		if (isset($post_id)) $post_title=apply_filters('roster_post_title',$post_title,$post_id,$roster_slider_curr,$roster_slider_css);
		$slider_content = $post->post_content;
		
		$roster_slide_redirect_url = get_post_meta($post_id, 'roster_slide_redirect_url', true);
		$roster_sslider_nolink = get_post_meta($post_id,'roster_sslider_nolink',true);
		trim($roster_slide_redirect_url);
		if(!empty($roster_slide_redirect_url) and isset($roster_slide_redirect_url)) {
		   $permalink = $roster_slide_redirect_url;
		}
		else{
		   $permalink = get_permalink($post_id);
		}
		if($roster_sslider_nolink=='1'){
		  $permalink='';
		}
			
		if( $cycle=='0' ) $roster_sldr_j++;
		if($i%$roster_slider_curr['visible'] == 0 and $cycle=='1' ){
		$html .= '<div class="roster_slide" '.$roster_slider_css['roster_slide'].'>
			<!-- roster_slide -->';
			$roster_sldr_j++;
		}
		
		$html .= '<div class="roster_slideri" '.$roster_slider_css['roster_slideri'].'>
			<!-- roster_slideri -->';
			
		if($roster_slider_curr['show_content']=='1'){
			if ($roster_slider_curr['content_from'] == "slider_content") {
				$slider_content = get_post_meta($post_id, 'slider_content', true);
			}
			if ($roster_slider_curr['content_from'] == "excerpt") {
				$slider_content = $post->post_excerpt;
			}

			$slider_content = strip_shortcodes( $slider_content );

			$slider_content = stripslashes($slider_content);
			$slider_content = str_replace(']]>', ']]&gt;', $slider_content);
	
			$slider_content = str_replace("\n","<br />",$slider_content);
			$slider_content = strip_tags($slider_content, $roster_slider_curr['allowable_tags']);
			
			if(!$roster_slider_curr['content_limit'] or $roster_slider_curr['content_limit'] == '' or $roster_slider_curr['content_limit'] == ' ') 
			  $slider_excerpt = substr($slider_content,0,$roster_slider_curr['content_chars']);
			else 
			  $slider_excerpt = roster_slider_word_limiter( $slider_content, $limit = $roster_slider_curr['content_limit'] );
			//filter hook
			$slider_excerpt=apply_filters('roster_slide_excerpt',$slider_excerpt,$post_id,$roster_slider_curr,$roster_slider_css);
			$slider_excerpt='<span '.$roster_slider_css['roster_slider_span'].'> '.$slider_excerpt.'</span>';
		}
		else{
		    $slider_excerpt='';
		}
		//filter hook
			$slider_excerpt=apply_filters('roster_slide_excerpt_html',$slider_excerpt,$post_id,$roster_slider_curr,$roster_slider_css);
		
		$roster_fields=$roster_slider_curr['fields'];		
		$fields_html='';
		if($roster_fields and !empty($roster_fields) ){
			$fields=explode( ',', $roster_fields );
			if($fields){
				foreach($fields as $field) {
					$field_val = get_post_meta($post_id, $field, true);
					if( $field_val and !empty($field_val) )
						$fields_html .='<div class="roster_'.$field.' roster_fields">'.$field_val.'</div>';
				}
			}
		}

//All images
		$roster_media = get_post_meta($post_id,'roster_media',true);
		$custom_key = '';
		if ((isset ($roster_slider_curr['img_pick'][0])) && (isset ($roster_slider_curr['img_pick'][1]))) {
			if($roster_slider_curr['img_pick'][0] == '1'){
			 $custom_key = array($roster_slider_curr['img_pick'][1]);
			}
		}
		
		$the_post_thumbnail = false;
		if(isset ($roster_slider_curr['img_pick'][2])){
			if($roster_slider_curr['img_pick'][2] == '1') $the_post_thumbnail = true;
		}
		
		$attachment = false;
		$order_of_image = '1';
		if (isset ($roster_slider_curr['img_pick'][3])) {
			if($roster_slider_curr['img_pick'][3] == '1'){
			 $attachment = true;
			 $order_of_image = $roster_slider_curr['img_pick'][4];
			}
		}
		
		$image_scan = false;
		if (isset ($roster_slider_curr['img_pick'][5])) {
			if($roster_slider_curr['img_pick'][5] == '1'){
				 $image_scan = true;
			}
		}

		if($roster_slider_curr['img_size'] == '1'){
		 $gti_width = $roster_slider_curr['img_width'];
		}
		else {
		 $gti_width = false;
		}
		
		if($roster_slider_curr['crop'] == '0'){
		 $extract_size = 'full';
		}
		elseif($roster_slider_curr['crop'] == '1'){
		 $extract_size = 'large';
		}
		elseif($roster_slider_curr['crop'] == '2'){
		 $extract_size = 'medium';
		}
		else{
		 $extract_size = 'thumbnail';
		}
		
		//Slide link anchor attributes
		$a_attr='';$imglink='';
		$a_attr=get_post_meta($post_id,'roster_link_attr',true);
		if( empty($a_attr) and isset( $roster_slider_curr['a_attr'] ) ) $a_attr=$roster_slider_curr['a_attr'];
		$a_attr_orig=$a_attr;
		if( isset($roster_slider_curr['pphoto'])  and $roster_slider_curr['pphoto'] == '1' ){
			if($roster_slider_curr['pphoto'] == '1') $a_attr.=' rel="prettyPhoto"';
			if(!empty($roster_slide_redirect_url) and isset($roster_slide_redirect_url))
				$imglink=$roster_slide_redirect_url;
			else $imglink='1';
		}
		$default_image=(isset($roster_slider_curr['default_image']))?($roster_slider_curr['default_image']):('false');
		$img_args = array(
			'custom_key' => $custom_key,
			'post_id' => $post_id,
			'attachment' => $attachment,
			'size' => $extract_size,
			'the_post_thumbnail' => $the_post_thumbnail,
			'default_image' => $default_image,
			'order_of_image' => $order_of_image,
			'link_to_post' => false,
			'image_class' => 'roster_slider_thumbnail '.$coloronhover_class,
			'image_scan' => $image_scan,
			'width' => $gti_width,
			'height' => false,
			'echo' => false,
			'permalink' => $permalink,
			'style'=> $roster_slider_css['roster_slider_thumbnail'],
			'a_attr'=> $a_attr,
			'imglink'=>$imglink
		);
		
		if( empty($roster_media) or $roster_media=='' or !($roster_media) ) {  
			$roster_large_image=roster_sslider_get_the_image($img_args);
		}
		else{
			$roster_large_image=$roster_media;
		}
		//filter hook
		$roster_large_image=apply_filters('roster_large_image',$roster_large_image,$post_id,$roster_slider_curr,$roster_slider_css);
		$html .= $roster_large_image;
		$bio = get_post_meta($post->ID,'wpcf-bio-title',TRUE);
		
		$page_close='';
		if( ($i%$roster_slider_curr['visible'] == ($roster_slider_curr['visible']-1) ) and $cycle=='1' ){$page_close='</div><!-- /roster_slide -->';}
		  		
		if ($roster_slider_curr['image_only'] == '1') { 
			$html .= '<!-- /roster_slideri -->
			</div>'.$page_close;
		}
		else {
		   if($permalink!='') {
			$slide_title = '<h2 '.$roster_slider_css['roster_slider_h2'].'><br><a href="'.$permalink.'" '.$roster_slider_css['roster_slider_h2_a'].' '.$a_attr_orig.'>'.$post_title.'</a></h2><span class="slide-bio-title">'.$bio.'</span><hr />';
			//filter hook
		   $slide_title=apply_filters('roster_slide_title_html',$slide_title,$post_id,$roster_slider_curr,$roster_slider_css,$post_title);
			$html .= $slide_title.$slider_excerpt.$fields_html;
			if($roster_slider_curr['show_content']=='1'){
			  $html .= '<p class="more"><a href="'.$permalink.'" '.$roster_slider_css['roster_slider_p_more'].' '.$a_attr_orig.'>'.$roster_slider_curr['more'].'</a></p>';
			}
			 $html .= '	<!-- /roster_slideri -->
			</div>'.$page_close; }
		   else{
		   $slide_title = '<h2 '.$roster_slider_css['roster_slider_h2'].'>'.$post_title.'</h2>';
		   //filter hook
		   $slide_title=apply_filters('roster_slide_title_html',$slide_title,$post_id,$roster_slider_curr,$roster_slider_css,$post_title);
		   $html .= $slide_title.$slider_excerpt.$fields_html.'
				<!-- /roster_slideri -->
			</div>'.$page_close;    }
		}
	  $i++;
	}
	if( ($page_close=='' or empty($page_close)) and $posts and $cycle=='1' ){$html=$html.'</div><!-- /roster_slide -->';}
	//filter hook
	$html=apply_filters('roster_extract_html',$html,$roster_sldr_j,$posts,$roster_slider_curr);
	if($out_echo == '1') {
	   echo $html;
	}
	$r_array = array( $roster_sldr_j, $html);
	$r_array=apply_filters('roster_r_array',$r_array,$posts, $roster_slider_curr,$set);
	return $r_array;
}

function get_global_roster_slider($slider_handle,$r_array,$roster_slider_curr,$set,$echo='1',$data=array() ){
	global $roster_slider,$default_roster_slider_settings;
	$roster_sldr_j = $r_array[0];
	$roster_slider_css = roster_get_inline_css($set);
	$html='';
	$slider_id=0;
	$nav_w=0;
	
	foreach($default_roster_slider_settings as $key=>$value){
		if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
	}
	
	if (isset ($data['slider_id'])) {
		if( is_array($data)) $slider_id=$data['slider_id'];
		else $slider_id='';
	}
	if ( is_array($data) && isset($data['title'])){
		if($data['title']!='' )$sldr_title=$data['title'];
		else {
			if($roster_slider_curr['title_from']=='1') $sldr_title = get_roster_slider_name($slider_id);
			else $sldr_title = $roster_slider_curr['title_text'];
		}
	}
	else{
		if($roster_slider_curr['title_from']=='1') $sldr_title = get_roster_slider_name($slider_id);
		else $sldr_title = $roster_slider_curr['title_text']; 
	}
	//filter hook
	$sldr_title=apply_filters('roster_slider_title',$sldr_title,$slider_handle,$roster_slider_curr,$set);
	
	$on_document_ready='jQuery(document).ready(function() {';
	$on_window_load='jQuery(window).on("load", function() {';	
	if ( ($roster_slider_curr['image_only']=='1') and $roster_slider_curr['responsive'] == '1' )
		$function_on=$on_window_load;
	else
		$function_on=$on_document_ready;
	
	$roster_media_queries='';
	$margin=(($roster_slider_curr['prev_next'] != 1)? '24' : '0');
	$set_width_s=(($roster_slider_curr['prev_next'] != 1)? '83' : '100');
	$set_width_m=(($roster_slider_curr['prev_next'] != 1)? '90' : '100');
	$set_width_l=(($roster_slider_curr['prev_next'] != 1)? '95' : '100');
	$o_visible=$roster_slider_curr['visible'];$o_responsive='';$o_width='';$o_scroll_items=$roster_slider_curr['scroll'];
	if( isset($roster_slider_curr['scroll']) ) $num_scroll_items=$roster_slider_curr['scroll'];
	else $num_scroll_items='1';
    if( $roster_slider_curr['responsive'] == '1' ) {
		$num_scroll_items='1';
		if($roster_slider_curr['image_only']=='1') $ht_css='height:auto !important;max-height:'.$roster_slider_curr['height'].'px !important;';
		else $ht_css='';
		
		if ($roster_slider_curr['prev_next'] != 1){$nav_w=$roster_slider_curr['navw'];}
		if(!$nav_w or empty($nav_w) ) $nav_w=0;
		if(isset($roster_slider_curr['width']) and $roster_slider_curr['width']>0)
			$max_width = ($roster_slider_curr['width'] - 2*$nav_w) . 'px' ;
		else
			$max_width = "100%";
		
		$roster_media_queries='.roster_slider_set'.$set.'.roster_slider{width:'.$set_width_l.'% !important;max-width:'.$max_width.';padding:0 '. $margin .'px !important;}.roster_slider_set'.$set.' .roster_slideri{'.$ht_css.'}.roster_slider_set'.$set.' .roster_slider_thumbnail{max-width:100% !important;}@media only screen and (max-width: 479px) {.roster_slider_set'.$set.'.roster_slider{width:'.$set_width_s.'% !important;}}@media only screen and (min-width: 480px) and (max-width: 959px) {.roster_slider_set'.$set.'.roster_slider{width:'.$set_width_m.'% !important;}}';
		//filter hook
		$roster_media_queries=apply_filters('roster_media_queries',$roster_media_queries,$roster_slider_curr,$set);
		
		$o_visible='{	min: 1,	max: '.$roster_slider_curr['visible'] .'}';
		$o_responsive='responsive: true,';
		$o_width='width: '.$roster_slider_curr['iwidth'].',';
		if($o_scroll_items>1) $o_scroll_items='null';
		
		//JS
		wp_enqueue_script( 'roster', roster_slider_plugin_url( 'js/roster.js' ),array('jquery'), ROSTER_SLIDER_VER, false);
	}
	
	$html.='<script type="text/javascript"> '.
	( (!isset($roster_slider_curr['fouc']) or $roster_slider_curr['fouc']=='0' ) ? 
	'jQuery("html").addClass("roster_slider_fouc"); '.$function_on.'
	   jQuery(".roster_slider_fouc .roster_slider").css({"display" : "block"});
	});' : '' );
	
	if(!empty($roster_media_queries)){
			$html.='jQuery(document).ready(function() {jQuery("head").append("<style type=\"text/css\">'. $roster_media_queries .'</style>");});';
	}
	
	if( $roster_slider['extend']=='1' or ( $roster_slider['extend']=='2' and ($roster_slider_curr['visible'] != $roster_slider_curr['scroll']) ) or $roster_slider_curr['responsive'] == '1' ){ 
	$html.=$function_on.'
				jQuery("#'.$slider_handle.'").rosterSlider({
						'.$o_responsive.'
						items: 	{
							'.$o_width.'
							visible     : '.$o_visible.'
						},
				'.( ($roster_slider_curr['prev_next'] != 1) ? 
						'next:   "#'.$slider_handle.'_next", prev:   "#'.$slider_handle.'_prev",' : '' ).'
						auto:'.( ($roster_slider_curr['autostep'] == '1') ? ($roster_slider_curr['time'] * 1000) : 'false' ).' ,
						scroll: {
							items:'.$o_scroll_items.',
							fx: "scroll",
							easing: "'.$roster_slider_curr['easing'].'",
							duration: '. ($roster_slider_curr['speed'] * 100) .',
							pauseOnHover: true
						}
					});	
					jQuery("#'.$slider_handle.'").touchwipe({
						wipeLeft: function() {
							jQuery("#'.$slider_handle.'").trigger("next", 1);
						},
						wipeRight: function() {
							jQuery("#'.$slider_handle.'").trigger("prev", 1);
						},
						preventDefaultEvents: false
					});
				});';
	 } else { 
	 $slider_pause_handle=str_replace("-", "_", $slider_handle).'_pause';
     $html.='function '.$slider_pause_handle.'() { jQuery("#'.$slider_handle.'").cycle("pause"); };
		jQuery(document).ready(function() {
			jQuery("#'.$slider_handle.'").cycle({
				fx: "'. $roster_slider_curr['transition'] .'",
				easing:"'. $roster_slider_curr['easing'] .'",
				speed:"'. ($roster_slider_curr['speed'] * 100 ) .'",
				timeout: '. ( ($roster_slider_curr['autostep'] == '1') ? ($roster_slider_curr['time'] * 1000) : 0 ).',
				'.( ($roster_slider_curr['continue'] != '1') ?
				'onPrevNextEvent: '.$slider_pause_handle.',
				onPagerEvent: '.$slider_pause_handle.',' : '' ) .
				( ($roster_slider_curr['prev_next'] != 1) ?  
					'next:   "#'.$slider_handle.'_next", prev:   "#'.$slider_handle.'_prev",' : '' ). 
				( ($roster_slider_curr['navnum'] == "1" or $roster_slider_curr['navnum'] == "2") ? 
					'pager: "#'.$slider_handle.'_nav",' : '' ).'
				pause: 1
			});
			jQuery("#'.$slider_handle.'").touchwipe({
				wipeLeft: function() {
					jQuery("#'.$slider_handle.'").cycle("next");
				},
				wipeRight: function() {
					jQuery("#'.$slider_handle.'").cycle("prev");
				},
				preventDefaultEvents: false
			});
		});';
	} 
	
	if($roster_slider_curr['pphoto'] == '1') {
		wp_enqueue_script( 'jquery.prettyPhoto', roster_slider_plugin_url( 'js/jquery.prettyPhoto.js' ),
							array('jquery'), ROSTER_SLIDER_VER, false);
		wp_enqueue_style( 'prettyPhoto_css', roster_slider_plugin_url( 'css/prettyPhoto.css' ),
				false, ROSTER_SLIDER_VER, 'all');
		$lightbox_script='jQuery(document).ready(function(){
			jQuery("a[rel^=\'prettyPhoto\']").prettyPhoto({deeplinking: false,social_tools:false});
		});';
		//filter hook
		$lightbox_script=apply_filters('roster_lightbox_inline',$lightbox_script);
		$html.=$lightbox_script;
	}	

	//action hook
	do_action('roster_global_script',$slider_handle,$roster_slider_curr);
	$html.='</script> <noscript><p><strong>'. $roster_slider['noscript'] .'</strong></p></noscript>';
	
	$html.='<div class="roster_slider roster_slider_set'. $set .'" '.$roster_slider_css['roster_slider'].'>'.
		( (!empty($sldr_title)) ? '<div class="sldr_title" '.$roster_slider_css['sldr_title'].'>'. $sldr_title .'</div>' : '' ).  
		( ($roster_slider_curr['navnum'] == "2") ? '<div id="'.$slider_handle.'_nav" class="roster_nav"></div>' : '' ) . '
		<div id="'. $slider_handle.'" class="roster_slider_instance">
				'.$r_array[1].'
		</div> '. 
		( ($roster_slider_curr['prev_next'] != 1) ? '<div id="'. $slider_handle.'_next" class="roster_next" '. $roster_slider_css['roster_next'] .'></div><div id="'. $slider_handle.'_prev" class="roster_prev" '. $roster_slider_css['roster_prev'].'></div>' : '' ).
		( ($roster_slider_curr['navnum'] == "1") ? '<div id="'.$slider_handle.'_nav" class="roster_nav"></div>' : '' ) . '
	</div>';

	$html=apply_filters('roster_slider_html',$html,$r_array,$roster_slider_curr,$set);
	if($echo == '1')  {echo $html; }
	else { return $html; }
}

function roster_carousel_posts_on_slider($max_posts, $offset=0, $slider_id = '1',$out_echo = '1',$set='', $data=array()) {
    global $roster_slider,$default_roster_slider_settings; 
	$roster_slider_options='roster_slider_options'.$set;
    $roster_slider_curr=get_option($roster_slider_options);
	if(!isset($roster_slider_curr) or !is_array($roster_slider_curr) or empty($roster_slider_curr)){$roster_slider_curr=$roster_slider;$set='';}
	foreach($default_roster_slider_settings as $key=>$value){
		if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
	}	
	global $wpdb, $table_prefix;
	$table_name = $table_prefix.ROSTER_SLIDER_TABLE;
	$post_table = $table_prefix."posts";
	$rand = $roster_slider_curr['rand'];
	if(isset($rand) and $rand=='1'){
	  $orderby = 'RAND()';
	}
	else {
	  $orderby = 'a.slide_order ASC, a.date DESC';
	}
	
	$posts = $wpdb->get_results("SELECT * FROM 
	                             $table_name a LEFT OUTER JOIN $post_table b 
								 ON a.post_id = b.ID 
								 WHERE (b.post_status = 'publish' OR (b.post_type='attachment' AND b.post_status = 'inherit')) AND a.slider_id = '$slider_id'  
	                             ORDER BY ".$orderby." LIMIT $offset, $max_posts", OBJECT);
	
	$r_array=roster_global_posts_processor( $posts, $roster_slider_curr, $out_echo, $set );
	return $r_array;
}

function get_roster_slider($slider_id='',$set='',$offset=0, $title='') {
	global $roster_slider,$default_roster_slider_settings; 
 	$roster_slider_options='roster_slider_options'.$set;
    $roster_slider_curr=get_option($roster_slider_options);
	if(!isset($roster_slider_curr) or !is_array($roster_slider_curr) or empty($roster_slider_curr)){$roster_slider_curr=$roster_slider;$set='';}
	foreach($default_roster_slider_settings as $key=>$value){
		if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
	}
	$data=array();
	$data['title']=$title;
	 if($roster_slider['multiple_sliders'] == '1' and is_singular() and (empty($slider_id) or !isset($slider_id))){
		global $post;
		$post_id = $post->ID;
		$slider_id = get_roster_slider_for_the_post($post_id);
	 }
	if(empty($slider_id) or !isset($slider_id))  $slider_id = '1';
	if( !$offset or empty($offset) or !is_numeric($offset)  ) $offset=0;
	if(!empty($slider_id)){
		$data['slider_id']=$slider_id;
		$r_array = roster_carousel_posts_on_slider($roster_slider_curr['no_posts'], $offset, $slider_id, '0', $set, $data); 
		$slider_handle='roster_slider_'.$slider_id;
		get_global_roster_slider($slider_handle,$r_array,$roster_slider_curr,$set,$echo='1',$data);
	} //end of not empty slider_id condition
}

//For displaying category specific posts in chronologically reverse order
function roster_carousel_posts_on_slider_category($max_posts='5', $catg_slug='', $offset=0, $out_echo = '1', $set='') {
    global $roster_slider,$default_roster_slider_settings; 
	$roster_slider_options='roster_slider_options'.$set;
    $roster_slider_curr=get_option($roster_slider_options);
	if(!isset($roster_slider_curr) or !is_array($roster_slider_curr) or empty($roster_slider_curr)){$roster_slider_curr=$roster_slider;$set='';}
	foreach($default_roster_slider_settings as $key=>$value){
		if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
	}

	global $wpdb, $table_prefix;
	
	if (!empty($catg_slug)) {
		$category = get_category_by_slug($catg_slug); 
		$slider_cat = $category->term_id;
	}
	else {
		$category = get_the_category();
		$slider_cat = $category[0]->cat_ID;
	}
	
	$rand = $roster_slider_curr['rand'];
	if(isset($rand) and $rand=='1') $orderby = '&orderby=rand';
	else $orderby = '';
	
	//extract the posts
	$posts = get_posts('numberposts='.$max_posts.'&offset='.$offset.'&category='.$slider_cat.$orderby);
	
	$r_array=roster_global_posts_processor( $posts, $roster_slider_curr, $out_echo,$set );
	return $r_array;
}

function get_roster_slider_category($catg_slug='', $set='', $offset=0,$title='') {
    global $roster_slider,$default_roster_slider_settings; 
 	$roster_slider_options='roster_slider_options'.$set;
    $roster_slider_curr=get_option($roster_slider_options);
	if(!isset($roster_slider_curr) or !is_array($roster_slider_curr) or empty($roster_slider_curr)){$roster_slider_curr=$roster_slider;$set='';}
	foreach($default_roster_slider_settings as $key=>$value){
		if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
	}
	$data=array();
	$data['title']=$title;
	if( !$offset or empty($offset) or !is_numeric($offset)  ) $offset=0;
    $r_array = roster_carousel_posts_on_slider_category($roster_slider_curr['no_posts'], $catg_slug, $offset, '0', $set); 
	$slider_handle='roster_slider_'.$catg_slug;
	get_global_roster_slider($slider_handle,$r_array,$roster_slider_curr,$set,$echo='1',$data);
} 

//For displaying recent posts in chronologically reverse order
function roster_carousel_posts_on_slider_recent($max_posts='5', $offset=0, $out_echo = '1', $set='') {
     global $roster_slider,$default_roster_slider_settings;
	$roster_slider_options='roster_slider_options'.$set;
    $roster_slider_curr=get_option($roster_slider_options);
	if(!isset($roster_slider_curr) or !is_array($roster_slider_curr) or empty($roster_slider_curr)){$roster_slider_curr=$roster_slider;$set='';}
	foreach($default_roster_slider_settings as $key=>$value){
		if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
	}
	$rand = $roster_slider_curr['rand'];
	if(isset($rand) and $rand=='1')	  $orderby = '&orderby=rand';
	else  $orderby = '';
	//extract posts data
	$posts = get_posts('numberposts='.$max_posts.'&offset='.$offset.$orderby);
	$r_array=roster_global_posts_processor( $posts, $roster_slider_curr, $out_echo,$set );
	return $r_array;
}

function get_roster_slider_recent($set='',$offset=0,$title='') {
	global $roster_slider,$default_roster_slider_settings;
 	$roster_slider_options='roster_slider_options'.$set;
    $roster_slider_curr=get_option($roster_slider_options);
	if(!isset($roster_slider_curr) or !is_array($roster_slider_curr) or empty($roster_slider_curr)){$roster_slider_curr=$roster_slider;$set='';}
	foreach($default_roster_slider_settings as $key=>$value){
		if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
	}
	$data=array();
	$data['title']=$title;
	if( !$offset or empty($offset) or !is_numeric($offset)  ) $offset=0;
	$r_array = roster_carousel_posts_on_slider_recent($roster_slider_curr['no_posts'], $offset, '0', $set);
	$slider_handle='roster_slider_recent';
	get_global_roster_slider($slider_handle,$r_array,$roster_slider_curr,$set,$echo='1',$data);
} 

require_once (dirname (__FILE__) . '/shortcodes_1.php');
require_once (dirname (__FILE__) . '/widgets_1.php');

function roster_slider_enqueue_scripts() {
global $roster_slider;
	if($roster_slider['extend']!='1') {
		wp_enqueue_script( 'jquery.cycle', roster_slider_plugin_url( 'js/jquery.cycle.js' ),
			array('jquery'), ROSTER_SLIDER_VER, false); 
	}
	if($roster_slider['extend']!='0') {
		wp_enqueue_script( 'roster', roster_slider_plugin_url( 'js/roster.js' ),
			array('jquery'), ROSTER_SLIDER_VER, false); 
	}		
	wp_enqueue_script( 'easing', roster_slider_plugin_url( 'js/jquery.easing.js' ),
		array('jquery'), ROSTER_SLIDER_VER, false); 
	wp_enqueue_script( 'jquery.touchwipe', roster_slider_plugin_url( 'js/jquery.touchwipe.js' ),
		array('jquery'), ROSTER_SLIDER_VER, false);
}

add_action( 'init', 'roster_slider_enqueue_scripts' );

function roster_slider_enqueue_styles() {	
  global $post, $roster_slider, $wp_registered_widgets,$wp_widget_factory;
  if(is_singular()) {
	 $roster_slider_style = get_post_meta($post->ID,'_roster_slider_style',true);
	 if((is_active_widget(false, false, 'roster_sslider_wid', true) or isset($roster_slider['shortcode']) ) and (!isset($roster_slider_style) or empty($roster_slider_style))){
	   $roster_slider_style='default';
	 }
	 if (!isset($roster_slider_style) or empty($roster_slider_style) ) {
	     wp_enqueue_style( 'roster_slider_headcss', roster_slider_plugin_url( 'css/skins/'.$roster_slider['stylesheet'].'/style.css' ),
		false, ROSTER_SLIDER_VER, 'all');
	 }
     else {
	     wp_enqueue_style( 'roster_slider_headcss', roster_slider_plugin_url( 'css/skins/'.$roster_slider_style.'/style.css' ),
		false, ROSTER_SLIDER_VER, 'all');
	}
  }
  else {
     $roster_slider_style = $roster_slider['stylesheet'];
	wp_enqueue_style( 'roster_slider_headcss', roster_slider_plugin_url( 'css/skins/'.$roster_slider_style.'/style.css' ),
		false, ROSTER_SLIDER_VER, 'all');
  }
}
add_action( 'wp', 'roster_slider_enqueue_styles' );

//admin settings
function roster_slider_admin_scripts() {
global $roster_slider;
  if ( is_admin() ){ // admin actions
  // Settings page only
	if ( isset($_GET['page']) && ('roster-slider-admin' == $_GET['page'] or 'roster-slider-settings' == $_GET['page'] )  ) {
	wp_register_script('jquery', false, false, false, false);
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'roster_slider_admin_js', roster_slider_plugin_url( 'js/admin.js' ),
		array('jquery'), ROSTER_SLIDER_VER, false);
	wp_enqueue_style( 'roster_slider_admin_css', roster_slider_plugin_url( 'css/admin.css' ),
		false, ROSTER_SLIDER_VER, 'all');
	
	if($roster_slider['extend']!='1') {
		wp_enqueue_script( 'jquery.cycle', roster_slider_plugin_url( 'js/jquery.cycle.js' ),
			array('jquery'), ROSTER_SLIDER_VER, false); 
	}
	if($roster_slider['extend']!='0') {
		wp_enqueue_script( 'roster', roster_slider_plugin_url( 'js/roster.js' ),
			array('jquery'), ROSTER_SLIDER_VER, false); 
	} 
	wp_enqueue_script( 'easing', roster_slider_plugin_url( 'js/jquery.easing.js' ),
		false, ROSTER_SLIDER_VER, false);
	wp_enqueue_style( 'roster_slider_admin_head_css', roster_slider_plugin_url( 'css/skins/'.$roster_slider['stylesheet'].'/style.css' ),false, ROSTER_SLIDER_VER, 'all');
	}
  }
}

add_action( 'admin_init', 'roster_slider_admin_scripts' );

function roster_slider_admin_head() {
global $roster_slider;
if ( is_admin() ){ // admin actions
   
	// Sliders & Settings page only
    if ( isset($_GET['page']) && ('roster-slider-admin' == $_GET['page'] or 'roster-slider-settings' == $_GET['page']) ) {
	  $sliders = roster_ss_get_sliders(); 
		global $roster_slider;
		$cntr='';
		if(isset($_GET['scounter'])) $cntr = $_GET['scounter'];
		$roster_slider_options='roster_slider_options'.$cntr;
		$roster_slider_curr=get_option($roster_slider_options);
		$active_tab=(isset($roster_slider_curr['active_tab']))?$roster_slider_curr['active_tab']:0;
		if ( isset($_GET['page']) && ('roster-slider-admin' == $_GET['page']) && isset($_POST['active_tab']) ) $active_tab=$_POST['active_tab'];
	?>
		<script type="text/javascript">
            // <![CDATA[
        jQuery(document).ready(function() {
                jQuery(function() {
					jQuery("#slider_tabs").tabs({fx: { opacity: "toggle", duration: 300}, active: <?php echo $active_tab;?> }).addClass( "ui-tabs-vertical-left ui-helper-clearfix" );jQuery( "#slider_tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
				<?php 	if ( isset($_GET['page']) && (( 'roster-slider-settings' == $_GET['page']) or ('roster-slider-admin' == $_GET['page']) ) ) { ?>
					jQuery( "#slider_tabs" ).on( "tabsactivate", function( event, ui ) { jQuery( "#roster_activetab, .roster_activetab" ).val( jQuery( "#slider_tabs" ).tabs( "option", "active" ) ); });
				<?php 	}
				foreach($sliders as $slider){ ?>
                    jQuery("#sslider_sortable_<?php echo $slider['slider_id'];?>").sortable();
                    jQuery("#sslider_sortable_<?php echo $slider['slider_id'];?>").disableSelection();
			    <?php } ?>
                });
        });
		
        function confirmRemove()
        {
            var agree=confirm("This will remove selected Posts/Pages from Slider.");
            if (agree)
            return true ;
            else
            return false ;
        }
        function confirmRemoveAll()
        {
            var agree=confirm("Remove all Posts/Pages from Roster Slider??");
            if (agree)
            return true ;
            else
            return false ;
        }
        function confirmSliderDelete()
        {
            var agree=confirm("Delete this Slider??");
            if (agree)
            return true ;
            else
            return false ;
        }
        function slider_checkform ( form )
        {
          if (form.new_slider_name.value == "") {
            alert( "Please enter the New Slider name." );
            form.new_slider_name.focus();
            return false ;
          }
          return true ;
        }
        </script>
<?php
   } //Sliders page only
   
   // Settings page only
  if ( isset($_GET['page']) && 'roster-slider-settings' == $_GET['page']  ) {
		wp_print_scripts( 'farbtastic' );
		wp_print_styles( 'farbtastic' );
?>
<script type="text/javascript">
	// <![CDATA[
jQuery(document).ready(function() {
		jQuery('#colorbox_1').farbtastic('#color_value_1');
		jQuery('#color_picker_1').click(function () {
           if (jQuery('#colorbox_1').css('display') == "block") {
		      jQuery('#colorbox_1').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_1').fadeIn("slow"); }
        });
		var colorpick_1 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_1 == true) {
    			return; }
				jQuery('#colorbox_1').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_1 = false;
		});
//for second color box
		jQuery('#colorbox_2').farbtastic('#color_value_2');
		jQuery('#color_picker_2').click(function () {
           if (jQuery('#colorbox_2').css('display') == "block") {
		      jQuery('#colorbox_2').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_2').fadeIn("slow"); }
        });
		var colorpick_2 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_2 == true) {
    			return; }
				jQuery('#colorbox_2').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_2 = false;
		});
//for third color box
		jQuery('#colorbox_3').farbtastic('#color_value_3');
		jQuery('#color_picker_3').click(function () {
           if (jQuery('#colorbox_3').css('display') == "block") {
		      jQuery('#colorbox_3').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_3').fadeIn("slow"); }
        });
		var colorpick_3 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_3 == true) {
    			return; }
				jQuery('#colorbox_3').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_3 = false;
		});
//for fourth color box
		jQuery('#colorbox_4').farbtastic('#color_value_4');
		jQuery('#color_picker_4').click(function () {
           if (jQuery('#colorbox_4').css('display') == "block") {
		      jQuery('#colorbox_4').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_4').fadeIn("slow"); }
        });
		var colorpick_4 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_4 == true) {
    			return; }
				jQuery('#colorbox_4').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_4 = false;
		});
//for fifth color box
		jQuery('#colorbox_5').farbtastic('#color_value_5');
		jQuery('#color_picker_5').click(function () {
           if (jQuery('#colorbox_5').css('display') == "block") {
		      jQuery('#colorbox_5').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_5').fadeIn("slow"); }
        });
		var colorpick_5 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_5 == true) {
    			return; }
				jQuery('#colorbox_5').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_5 = false;
		});
//for sixth color box
		jQuery('#colorbox_6').farbtastic('#color_value_6');
		jQuery('#color_picker_6').click(function () {
           if (jQuery('#colorbox_6').css('display') == "block") {
		      jQuery('#colorbox_6').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_6').fadeIn("slow"); }
        });
		var colorpick_6 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_6 == true) {
    			return; }
				jQuery('#colorbox_6').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_6 = false;
		});
		jQuery('#sldr_close').click(function () {
			jQuery('#sldr_message').fadeOut("slow");
		});
});
function confirmSettingsCreate()
        {
            var agree=confirm("Create New Settings Set??");
            if (agree)
            return true ;
            else
            return false ;
}
function confirmSettingsDelete()
        {
            var agree=confirm("Delete this Settings Set??");
            if (agree)
            return true ;
            else
            return false ;
}
</script>
<style type="text/css">
.color-picker-wrap {
		position: absolute;
 		display: none; 
		background: #fff;
		border: 3px solid #ccc;
		padding: 3px;
		z-index: 1000;
	}
</style>
<?php
   } //for roster slider option page
 }//only for admin
}
add_action('admin_head', 'roster_slider_admin_head');

//get inline css with style attribute attached/not attached
function roster_get_inline_css($set='',$echo='0'){
    global $roster_slider,$default_roster_slider_settings;
	$roster_slider_options='roster_slider_options'.$set;
    $roster_slider_curr=get_option($roster_slider_options);
	if(!isset($roster_slider_curr) or !is_array($roster_slider_curr) or empty($roster_slider_curr)){$roster_slider_curr=$roster_slider;$set='';}
	foreach($default_roster_slider_settings as $key=>$value){
		if(!isset($roster_slider_curr[$key])) $roster_slider_curr[$key]='';
	}
	global $post;
	$nav_w=0;
	if(is_singular()) {	$roster_slider_style = get_post_meta($post->ID,'_roster_slider_style',true);}
	if((is_singular() and ($roster_slider_style == 'default' or empty($roster_slider_style) or !$roster_slider_style)) or (!is_singular() and $roster_slider['stylesheet'] == 'default')  )	{ $default=true;	}
	else{ $default=false;}
	
	$roster_slider_css=array();
	if($default){
		$style_start= ($echo=='0') ? 'style="':'';
		$style_end= ($echo=='0') ? '"':'';
	//roster_slider
		if ($roster_slider_curr['prev_next'] != 1){$nav_w=$roster_slider_curr['navw'];}
		if(!$nav_w or empty($nav_w) ) $nav_w=0;
		if(isset($roster_slider_curr['width']) and $roster_slider_curr['width']!=0) {
			$roster_slider_css['roster_slider']=$style_start.'width:'. ( $roster_slider_curr['width'] - 2*$nav_w ) .'px;padding:0 '. $nav_w .'px;'.$style_end;
		}
		else{
			$roster_slider_css['roster_slider']=$style_start.'padding:0 '. $nav_w .'px;'.$style_end;
		}
		
	//sldr_title			
		$title_fontg=isset($roster_slider_curr['title_fontg'])?trim($roster_slider_curr['title_fontg']):'';
		if(!empty($title_fontg)) 	{
			wp_enqueue_style( 'roster_title', 'http://fonts.googleapis.com/css?family='.$title_fontg,array(),ROSTER_SLIDER_VER);
			$title_fontg=roster_get_google_font($title_fontg);
			$title_fontg=$title_fontg.',';
		}
		if ($roster_slider_curr['title_fstyle'] == "bold" or $roster_slider_curr['title_fstyle'] == "bold italic" ){$slider_title_font = "bold";} else { $slider_title_font = "normal"; }
		if ($roster_slider_curr['title_fstyle'] == "italic" or $roster_slider_curr['title_fstyle'] == "bold italic" ){$slider_title_style = "italic";} else {$slider_title_style = "normal";}
		$sldr_title = $roster_slider_curr['title_text']; if(!empty($sldr_title)) { $slider_title_margin = "5px 0 10px 0"; } else {$slider_title_margin = "0";} 
		$roster_slider_css['sldr_title']=$style_start.'font-family:'. $title_fontg . ' '.$roster_slider_curr['title_font'].';font-size:'.$roster_slider_curr['title_fsize'].'px;font-weight:'.$slider_title_font.';font-style:'.$slider_title_style.';color:'.$roster_slider_curr['title_fcolor'].';margin:'.$slider_title_margin.$style_end;

	//roster_slideri
		if ($roster_slider_curr['bg'] == '1') { $roster_slideri_bg = "transparent";} else { $roster_slideri_bg = $roster_slider_curr['bg_color']; }
		$roster_slider_css['roster_slideri']=$style_start.'background-color:'.$roster_slideri_bg.';border:'.$roster_slider_curr['border'].'px solid '.$roster_slider_curr['brcolor'].';width:'. $roster_slider_curr['iwidth'].'px;height:'. $roster_slider_curr['height'].'px;'.$style_end;
	
	//roster_slider_h2
		$ptitle_fontg=isset($roster_slider_curr['ptitle_fontg'])?trim($roster_slider_curr['ptitle_fontg']):'';
		if(!empty($ptitle_fontg)) 	{
			wp_enqueue_style( 'roster_ptitle', 'http://fonts.googleapis.com/css?family='.$ptitle_fontg,array(),ROSTER_SLIDER_VER);
			$ptitle_fontg=roster_get_google_font($ptitle_fontg);
			$ptitle_fontg=$ptitle_fontg.',';
		}
		if ($roster_slider_curr['ptitle_fstyle'] == "bold" or $roster_slider_curr['ptitle_fstyle'] == "bold italic" ){$ptitle_fweight = "bold";} else {$ptitle_fweight = "normal";}
		if ($roster_slider_curr['ptitle_fstyle'] == "italic" or $roster_slider_curr['ptitle_fstyle'] == "bold italic"){$ptitle_fstyle = "italic";} else {$ptitle_fstyle = "normal";}
		$roster_slider_css['roster_slider_h2']=$style_start.'clear:none;line-height:'. ($roster_slider_curr['ptitle_fsize'] + 3) .'px;font-family:'. $ptitle_fontg . ' ' . $roster_slider_curr['ptitle_font'].';font-size:'.$roster_slider_curr['ptitle_fsize'].'px;font-weight:'.$ptitle_fweight.';font-style:'.$ptitle_fstyle.';color:'.$roster_slider_curr['ptitle_fcolor'].';margin:0 0 5px 0;'.$style_end;
		
	//roster_slider_h2 a
		$roster_slider_css['roster_slider_h2_a']=$style_start.'font-family:'. $ptitle_fontg . ' ' . $roster_slider_curr['ptitle_font'].';font-size:'.$roster_slider_curr['ptitle_fsize'].'px;font-weight:'.$ptitle_fweight.';font-style:'.$ptitle_fstyle.';color:'.$roster_slider_curr['ptitle_fcolor'].';'.$style_end;

	//roster_slider_span	
		$content_fontg=isset($roster_slider_curr['content_fontg'])?trim($roster_slider_curr['content_fontg']):'';
		if(!empty($content_fontg)) 	{
			wp_enqueue_style( 'roster_content', 'http://fonts.googleapis.com/css?family='.$content_fontg,array(),ROSTER_SLIDER_VER);
			$content_fontg=roster_get_google_font($content_fontg);
			$content_fontg=$content_fontg.',';
		}	
		if ($roster_slider_curr['content_fstyle'] == "bold" or $roster_slider_curr['content_fstyle'] == "bold italic" ){$content_fweight= "bold";} else {$content_fweight= "normal";}
		if ($roster_slider_curr['content_fstyle']=="italic" or $roster_slider_curr['content_fstyle'] == "bold italic"){$content_fstyle= "italic";} else {$content_fstyle= "normal";}
		$roster_slider_css['roster_slider_span']=$style_start.'font-family:'. $content_fontg . ' '.$roster_slider_curr['content_font'].';font-size:'.$roster_slider_curr['content_fsize'].'px;font-weight:'.$content_fweight.';font-style:'.$content_fstyle.';color:'. $roster_slider_curr['content_fcolor'].';'.$style_end;
		
	//roster_slider_thumbnail
		if($roster_slider_curr['img_align'] == "left") {$thumb_margin_right= "10";} else {$thumb_margin_right= "0";}
		if($roster_slider_curr['img_align'] == "right") {$thumb_margin_left = "10";} else {$thumb_margin_left = "0";}
		if($roster_slider_curr['img_size'] == '1'){ $thumb_width= 'width:'. $roster_slider_curr['img_width'].'px;';} else{$thumb_width='';}
		$roster_slider_css['roster_slider_thumbnail']=$style_start.'float:'.$roster_slider_curr['img_align'].';margin:0 '.$thumb_margin_right.'px 0 '.$thumb_margin_left.'px;max-height:'.$roster_slider_curr['img_height'].'px;border:'.$roster_slider_curr['img_border'].'px solid '.$roster_slider_curr['img_brcolor'].';'.$thumb_width.$style_end;
	
	//roster_slider_p_more
		$roster_slider_css['roster_slider_p_more']=$style_start.'color:'.$roster_slider_curr['ptitle_fcolor'].';font-family:'.$roster_slider_curr['content_font'].';font-size:'.$roster_slider_curr['content_fsize'].'px;'.$style_end;
	
	//roster_next
	    $nexturl='css/skins/'.$roster_slider['stylesheet'].'/buttons/'.$roster_slider_curr['buttons'].'/next.png';
		$roster_slider_css['roster_next']=$style_start.'background: transparent url('.roster_slider_plugin_url( $nexturl ) .') no-repeat 0 0;top:'.$roster_slider_curr['navtop'].'%;'.$style_end;
	//roster_prev
	    $prevurl='css/skins/'.$roster_slider['stylesheet'].'/buttons/'.$roster_slider_curr['buttons'].'/prev.png';
		$roster_slider_css['roster_prev']=$style_start.'background: transparent url('.roster_slider_plugin_url( $prevurl ) .') no-repeat 0 0;top:'.$roster_slider_curr['navtop'].'%;'.$style_end;
	}
	return $roster_slider_css;
}

function roster_slider_css() {
global $roster_slider;
$css=$roster_slider['css'];
if($css and !empty($css)){?>
 <style type="text/css"><?php echo $css;?></style>
<?php }
}
add_action('wp_head', 'roster_slider_css');
add_action('admin_head', 'roster_slider_css');
function roster_custom_css_js() {
	global $roster_slider;
	$css=$roster_slider['css_js'];
	$line_breaks = array("\r\n", "\n", "\r");
	$css = str_replace($line_breaks, "", $css);
	if($css and !empty($css)){
		if( ( is_admin() and isset($_GET['page']) and 'roster-slider-settings' == $_GET['page']) or !is_admin() ){	?>
			<script type="text/javascript">jQuery(document).ready(function() { jQuery("head").append("<style type=\"text/css\"><?php echo $css;?></style>"); }) </script>
<?php 	}
	}
}
add_action('wp_footer', 'roster_custom_css_js');
add_action('admin_footer', 'roster_custom_css_js');
?>