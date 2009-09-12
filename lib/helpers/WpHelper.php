<?php
class WpHelper{
	
	/**
	    $optionsgourp=name of the optionsgroup you want to generate a form, for.
		$optionNames=array('Label'=>'key');
		if $optionNames is not supplied the function will retrieve the options it self and use the optionkey as label.
	 */
	static function simpleOptionsForm($optiongroup,$options=false){		
		if(!$options){
			$options=array();
			global $new_whitelist_options;
			$options=$new_whitelist_options[$optiongroup];
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
			?>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo $key ?>"><?php if(is_int($label)) echo str_replace('_',' ',$key); else echo $label ?></label></th>
					<td>
					<?php 
						if($type):
							if($type=='textarea'):?>
								<textarea id="<?php echo $key ?>" name="<?php echo $key ?>"><?php echo get_option($key); ?></textarea>
					<?php 	elseif($type=='checkbox'):?>
								<input type="checkbox" id="<?php echo $key ?>" name="<?php echo $key ?>" value="1" <?php echo get_option($key)?'checked=\"checked\"':''; ?> />					
					<?php	elseif(strpos($type,'dropdown')!==false):?>
					<?php 		$selected=get_option($key);	?>
								<select id="<?php echo $key ?>" name="<?php echo $key ?>">
					<?php 		foreach($dropdown as $text => $value):?>
					<?php 			
									if($selected==$value)
										$selected=' selected=\"selected\" ';?>
									<option  <?php echo $selected ?>value="<?php echo $value; ?>">
										<?php if(is_int($text)): echo $value; else: echo $text; endif; 	echo '  '.$text.' '.$value;?>
									</option>
					<?php 		endforeach;?>
								</select>
					<?php 	endif;?>
					<?php else:?>
								<input type="text" id="<?php echo $key ?>" name="<?php echo $key ?>" value="<?php echo get_option($key); ?>" />
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
}
?>