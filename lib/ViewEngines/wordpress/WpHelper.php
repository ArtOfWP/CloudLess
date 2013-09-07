<?php
class WpHelper{
	static function seperatedTabbedOptionsForm($id,$optiongroups,$tabs){
		?>
		<div id="<?php echo $id.'tabs'?>" class="ui-widget">
			<?php $tabtitles=array_keys($tabs)?>
			<ul class="ui-corner-none" style="border:none;background:none;">
			<?php foreach($tabtitles as $tabtitle):?>
			<li class="tab" style="border:solid 1px #CCCCCC"><?php Html::a($tabtitle,"#".strtolower(str_replace(" ","-",$tabtitle))) ?></li>
			<?php endforeach;?>
			</ul>
			<?php 
				$i=0;
				foreach($tabs as $tabtitle => $options):
					$optiongroup=$optiongroups[$i];
					$values=get_option($optiongroup);
					$i++;
			?>
			<div id="<?php echo strtolower(str_replace(" ","-",$tabtitle))?>" class="ui-widget-content ui-corner-top" style="border:solid 1px #797979">
			<form method="post" action="options.php">
			<?php settings_fields($optiongroup); ?>
			<table class="form-table">
			<?php foreach($options as $key => $option):	
				$type=false;
				$dropdown=false;
				if(is_array($option)){
					$label=$option[0];
					$type=$option[1];		
					if($type==null){
						$type=array_keys($option);
						$type=$type[1];
						$temp=$option[$type];
						if(is_array($temp)){
							$dropdown=$temp;
						}
					}
				}else
					$label=$option;
				$form_id=$values?$optiongroup.'-'.$key:$key;
				$form_name=$values?$optiongroup.'['.$key.']':$key;
			?>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo $form_id ?>"><?php if(is_int($label)) echo str_replace('_',' ',$values?$values[$key]:$key ); else echo $label ?></label></th>
					<td>
					<?php 
						if($type):
							if($type=='textarea'):?>
								<textarea id="<?php echo $form_id  ?>" name="<?php echo $form_name ?>"><?php echo $values?$values[$key]:get_option($key); ?></textarea>
					<?php 	elseif($type=='checkbox'):?>
								<input type="checkbox" id="<?php echo $form_id  ?>" name="<?php echo $form_name ?>" value="1" <?php echo $values?isset($values[$key])&&$values[$key]:get_option($key)?'checked="checked"':''; ?> />
					<?php	elseif(strpos($type,'dropdown')!==false):?>
					<?php 		$selected=$values?$values[$key]:get_option($key);	?>
								<select id="<?php echo $form_id  ?>" name="<?php echo $form_name ?>">
					<?php 		foreach($dropdown as $text => $value):?>
					<?php 			
									if($selected==$value)
										$selected=' selected="selected" ';?>
									<option  <?php echo $selected ?>value="<?php echo $value; ?>">
										<?php if(is_int($text)): echo $value; else: echo $text; endif; 	echo '  '.$text.' '.$value;?>
									</option>
					<?php 		endforeach;?>
								</select>
					<?php 	endif;?>
					<?php else:?>
								<input type="text" id="<?php echo $form_id  ?>" name="<?php echo $form_name ?>" value="<?php echo $values?$values[$key]:get_option($key); ?>" />
					<?php endif;?>
					</td>
				</tr>
			<?php endforeach;?>
			</table>
								<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e("Save $tabtitle Changes"); ?>" />
			</p>	
			</form>		
			</div>
			<?php endforeach;?>		
		</div>								
		<?php 
		Html::registerFooterScript("jQuery(function(){jQuery(\"#$id"."tabs\").tabs();});");
		?>
			<?php 
	}
	/**
	    $optiongroup=name of the optionsgroup you want to generate a form, for. If isarray optiongroup is the name of the option with the array.
		$options=array('Label'=>'key');
		if $optionNames is not supplied the function will retrieve the options it self and use the optionkey as label.
	 */
	static function tabbedOptionsForm($optiongroup,$tabs,$saveAllTabs=true){		
		$values=get_option($optiongroup);
		?>
		<?php if($saveAllTabs):?>
		<form method="post" action="options.php">
		<?php settings_fields($optiongroup); ?>		
		
		<?php endif;?>
		<div id="<?php echo $optiongroup.'tabs'?>" class="ui-widget">
			<?php $tabtitles=array_keys($tabs)?>
			<ul class="ui-corner-none" style="border:none;background:none;">
			<?php foreach($tabtitles as $tabtitle):?>
			<li class="tab" style="border:solid 1px #CCCCCC"><?php Html::a($tabtitle,"#".strtolower(str_replace(" ","-",$tabtitle))) ?></li>
			<?php endforeach;?>
			</ul>
			<?php foreach($tabs as $tabtitle => $options):?>
			<div id="<?php echo strtolower(str_replace(" ","-",$tabtitle))?>" class="ui-widget-content ui-corner-top" style="border:solid 1px #797979">
			<?php if(!$saveAllTabs):?>
			<form method="post" action="options.php">
			<?php settings_fields($optiongroup); ?>			
			<?php endif;?>			
			<table class="form-table">
			<?php foreach($options as $key => $option):	
				$type=false;
				$dropdown=false;
				if(is_array($option)){
					$label=$option[0];
					$type=$option[1];		
					if($type==null){
						$type=array_keys($option);
						$type=$type[1];
						$temp=$option[$type];
						if(is_array($temp)){
							$dropdown=$temp;
						}
					}
				}else
					$label=$option;
				$form_id=$values?$optiongroup.'-'.$key:$key;
				$form_name=$values?$optiongroup.'['.$key.']':$key;
			?>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo $form_id ?>"><?php if(is_int($label)) echo str_replace('_',' ',$values?$values[$key]:$key ); else echo $label ?></label></th>
					<td>
					<?php 
						if($type):
							if($type=='textarea'):?>
								<textarea id="<?php echo $form_id  ?>" name="<?php echo $form_name ?>"><?php echo $values?$values[$key]:get_option($key); ?></textarea>
					<?php 	elseif($type=='checkbox'):?>
								<input type="checkbox" id="<?php echo $form_id  ?>" name="<?php echo $form_name ?>" value="1" <?php echo $values?isset($values[$key])&&$values[$key]:get_option($key)?'checked="checked"':''; ?> />
					<?php	elseif(strpos($type,'dropdown')!==false):?>
					<?php 		$selected=$values?$values[$key]:get_option($key);	?>
								<select id="<?php echo $form_id  ?>" name="<?php echo $form_name ?>">
					<?php 		foreach($dropdown as $text => $value):?>
					<?php 			
									if($selected==$value)
										$selected=' selected="selected" ';?>
									<option  <?php echo $selected ?>value="<?php echo $value; ?>">
										<?php if(is_int($text)): echo $value; else: echo $text; endif; 	echo '  '.$text.' '.$value;?>
									</option>
					<?php 		endforeach;?>
								</select>
					<?php 	endif;?>
					<?php else:?>
								<input type="text" id="<?php echo $form_id  ?>" name="<?php echo $form_name ?>" value="<?php echo $values?$values[$key]:get_option($key); ?>" />
					<?php endif;?>
					</td>
				</tr>
			<?php endforeach;?>
			</table>
								<p class="submit">
			<input type="submit" class="button-primary" value="<?php $saveAllTabs?_e('Save All Changes'):_e('Save Changes'); ?>" />
			</p>	
			<?php if(!$saveAllTabs):?>
			</form>
			<?php endif;?>			
			</div>
			<?php endforeach;?>		
		</div>					
			<?php if($saveAllTabs):?>
			</form>
			<?php endif;
			Html::registerFooterScript("jQuery(document).ready(function() {jQuery(function(){jQuery(\"#$optiongroup"."tabs\").tabs();});});",true);
	}
	/**
	    $optiongroup=name of the optionsgroup you want to generate a form, for. If isarray optiongroup is the name of the option with the array.
		$options=array('Label'=>'key');
		if $optionNames is not supplied the function will retrieve the options it self and use the optionkey as label.
	 */
	static function simpleOptionsForm($optiongroup,$options=false,$isarray=false){		
		if(!$options){
			global $new_whitelist_options;
			$options=$new_whitelist_options[$optiongroup];
		}
		$values=false;
		if($isarray){
			$values=get_option($optiongroup);
            if(empty($values)){
                $temp=Container::instance()->fetch($optiongroup.'Options');
                if($temp)
                    $values=$temp->getArray();
            }
        }
		?>
		<form method="post" action="options.php">
			<?php settings_fields($optiongroup); ?>
			<table class="form-table">
			<?php foreach($options as $key => $option):	
				$type=false;
				$dropdown=false;
				if(is_array($option)){
					$label=$option[0];
					$type=$option[1];		
					if($type==null){
						$type=array_keys($option);
						$type=$type[1];
						$temp=$option[$type];
						if(is_array($temp)){
							$dropdown=$temp;
						}
					}
				}else
					$label=$option;
				$form_id=$optiongroup.'-'.$key;
				$form_name=$optiongroup.'['.$key.']';
			?>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo $form_id ?>"><?php if(is_int($label)) echo str_replace('_',' ',$values?$values[$key]:$key ); else echo $label ?></label></th>
					<td>
					<?php 
						if($type):
							if($type=='textarea'):?>
								<textarea id="<?php echo $form_id  ?>" name="<?php echo $form_name  ?>"><?php echo $values?$values[$key]:get_option($key); ?></textarea>
					<?php 	elseif($type=='checkbox'):?>
								<input type="checkbox" id="<?php echo $form_id  ?>" name="<?php echo $form_name  ?>" value="1" <?php echo $values?$values[$key]:get_option($key)?'checked="checked"':''; ?> />					
					<?php	elseif(strpos($type,'dropdown')!==false):?>
					<?php 		$selected=$values?$values[$key]:get_option($key);	?>
								<select id="<?php echo $form_id  ?>" name="<?php echo $form_name  ?>">
					<?php 		foreach($dropdown as $text => $value):?>
					<?php 			
									if($selected==$value)
										$selected=' selected="selected" ';?>
									<option  <?php echo $selected ?>value="<?php echo $value; ?>">
										<?php if(is_int($text)): echo $value; else: echo $text; endif; 	echo '  '.$text.' '.$value;?>
									</option>
					<?php 		endforeach;?>
								</select>
					<?php 	endif;?>
					<?php else:?>
								<input type="text" id="<?php echo $form_id  ?>" name="<?php echo $form_name  ?>" value="<?php echo $values?$values[$key]:get_option($key); ?>" />
					<?php endif;?>
					</td>
				</tr>
			<?php endforeach;?>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form><?php 
	}
	static function registerSettings($optiongroup,$optionkeys){
		foreach($optionkeys as $key)
			register_setting($optiongroup,$key);
	}
	static function ullinklist($id=false,$listitems,$title=false,$linkmethod=false,$textmethod=false,$path){
		$list='<ul';
		if($id)
			$list.=' id="'.$id.'"';
		$list.='>';
		if($title)
			$list.=$title;
		foreach($listitems as $listitem){
			$link;
			$text;
			if($linkmethod)
				$link=$listitem->$linkmethod();
			if($textmethod)
				$text=$listitem->$textmethod();
			$list.='<li><a href="'.$path.$link.'">'.$text.'</a></li>';
		}
		$list.='</ul>';
		return $list;
	}
	static function insertPage($title,$slug,$content,$status='draft',$author=false){
		global $user_ID;
      	get_currentuserinfo();
      	$user=$author?$author:$user_ID;      	
      	$defaults = array(
      		'post_title' => $title,
			'post_name'=>$slug,
			'post_content' => $content,      	
			'post_status' => $status,
			'post_type' => 'page',
			'post_author' => $user,
      		'comment_status'=>'closed',
			'import_id' => 0);
      	
      	wp_insert_post($defaults);
	}
	static function loadstyles($styles){
	}
	static function registerStyle($handle, $src=false, $deps=false, $ver=false, $media=false){
		wp_register_style($handle, $src, $deps, $ver, $media);
	}
	static function enqueueStyle($handle, $src=false, $deps=false, $ver=false, $media=false){
		wp_enqueue_style($handle, $src, $deps, $ver, $media);
	}
	static function registerScript($handle, $src=false, $deps=false, $ver=false, $in_footer=false){
		wp_register_style($handle, $src, $deps, $ver, $media);
	}	
	static function enqueueScript($handle, $src=false, $deps=false, $ver=false, $in_footer=false){
		wp_enqueue_style($handle, $src, $deps, $ver, $in_footer);
	}		
}