<?php

class HtmlHelper{
	static function createForm($id,$object,$path=false,$classes=false){
		if(!$path)
			$path=get_bloginfo('url').'/'.get_class($object).'/create';
		HtmlHelper::form($id,$object,$path,POST,'Add new',strtolower(get_class($object)),$classes);
	}
	static function updateForm($id,$object,$path=false,$classes=false){
		if(!$path)
			$path=get_bloginfo('url').'/'.get_class($object).'/update';
		HtmlHelper::form($id,$object,$path,POST,'Save',strtolower(get_class($object)),$classes);
	}	
	static function form($id,$object,$action,$method,$submit='Send',$nonce=false,$classes=false){
		$elements=ObjectUtility::getPropertiesAndValues($object);
		$upload=$method==POST?'enctype="multipart/form-data"':'';
		$theForm='<form id=\''.$id.'\' action=\''.$action.'\' method=\''.$method.'\' '.$upload.'  ><table class=\'form-table\'>';
		$theForm.=HtmlHelper::input('_redirect','hidden','referer');
		if($nonce)
			$theForm.=HtmlHelper::input('_wpnonce','hidden',wp_create_nonce($nonce));
			
		foreach($elements as $id => $value){
			if($id=='Id'){
				if($value>0)
					$theForm.=HtmlHelper::input($id,'hidden',$value);
			}else{
				$settings=ObjectUtility::getCommentDecoration($object,'get'.$id);				
				if(array_key_exists('new',$settings))
					continue;
				$theForm.='<tr valign=\'top\'>';	
				$field=array_key_exists_v('field',$settings);
					$theForm.='<th scope=\'row\'>';
					$theForm.=HtmlHelper::label($id);
					$theForm.='</th><td>';
				if(!$field)
					$field='text';
				if($field=='textarea'){
					$theForm.=HtmlHelper::textarea($id,$value);	
				}
				else if($field=='image'){
					ob_start();
					HtmlHelper::img(WP_PLUGIN_URL.$value);
					$theForm.=ob_get_contents().'<br />';
					ob_end_clean();
					if($value)
						$theForm.=HtmlHelper::input($id.'_hasimage','hidden',$value?'1':'');					
					$theForm.=HtmlHelper::input($id,'file',$value);
				}
				else if($field=='dropdown'){
					$dbfield=array_key_exists_v('dbrelation',$settings);
					if($dbfield){
						$temp = new $dbfield();
						$selects=$temp->findAll();
					}
//					$theForm.="<p>$value</p>";
					$theForm.=HtmlHelper::select($id,$selects,false,$value);
					if($dbfield && array_key_exists_v('addnew',$settings)=='true'){
						ob_start();
						HtmlHelper::a('Add new',Communication::cleanUrl($_SERVER["REQUEST_URI"]).'?page='.strtolower($dbfield).'&action=createnew');
						$theForm.=ob_get_contents();
						ob_end_clean();
					}
				}
				else if($field=='url'){
					$theForm.=HtmlHelper::input($id,'text',$value);
					ob_start();
					HtmlHelper::a('Test link',$value);
					$theForm.='<br />'.ob_get_contents();
					ob_end_clean();
				}
				else
					$theForm.=HtmlHelper::input($id,$field,$value);
				$theForm.='</td></tr>';
			}
		}
		$arrays=ObjectUtility::getArrayPropertiesAndValues($object);		
		foreach($arrays as $id => $value){
			$settings=ObjectUtility::getCommentDecoration($object,$id.'List');
			if(array_key_exists_v('new',$settings))
				continue;
			$field=array_key_exists_v('field',$settings);
			$theForm.='<tr valign=\'top\'>';	
			$theForm.='<th scope=\'row\'>';
			$theForm.=HtmlHelper::label($id);
			$theForm.='</th><td>';
			
			if($field){
				if($field=='text'){
					$seperator=array_key_exists_v('seperator',$settings);
					if(!$seperator)
						$seperator=',';
					$list='';
					if($value!=null)
						$list=implode($seperator,$value);
					$theForm.=HtmlHelper::input($id.'_list','text',$list);
				}else if($field=='multiple'){					
					$dbfield=array_key_exists_v('dbrelation',$settings);
					if($dbfield){
						$temp = new $dbfield();
						$value=$temp->findAll();
					}					
					$theForm.=HtmlHelper::select($id,$value,true);
					if($dbfield){
						ob_start();
						HtmlHelper::a('Add new',Communication::cleanUrl($_SERVER["REQUEST_URI"]).'?page='.strtolower($dbfield).'&action=createnew');
						$theForm.=ob_get_contents();
						ob_end_clean();
					}
				}
			}else{
				$theForm.=HtmlHelper::select($id,$value);
			}
			$theForm.='</td>';
		}
		$theForm.='</table>';
				$theForm.='<p class="submit">';
		if($classes)
			$theForm.=HtmlHelper::input('submit','submit',$submit,array_key_exists_v('submit',$classes));
		else
			$theForm.=HtmlHelper::input('submit','submit',$submit,"button-primary");
		$theForm.='</p></form>';
		echo $theForm;
	}
	static function label($id){
		return '<label for=\''.$id.'\'>'.$id.':</label>';
	}
	static function input($id,$type,$value,$class=false){
		if($class)
			return '<input id=\''.$id.'\' name=\''.$id.'\' type=\''.$type.'\' value=\''.$value.'\' class=\''.$class.'\' >';
		return '<input id=\''.$id.'\' name=\''.$id.'\' type=\''.$type.'\' value=\''.$value.'\' />';
	}
	static function textarea($id,$value,$class=false){
		return '<textarea id=\''.$id.'\' name=\''.$id.'\' rows=\'10\'  cols=\'20\'>'.$value.'</textarea>';
	}
	static function select($id,$array,$multiple=false,$selectedValues=false){
		$select='<select id=\''.$id.'\' name=\''.$id.'\'';
		if($multiple)
			$select.=' multiple=\'multiple\' style=\'height:70px\' size=\'5\'';
		$select.=' >';
		$select.=HtmlHelper::option(0,'None');
		if(is_array($array))	
			foreach($array as $element){
				if(is_string($element) || is_int($element))
					$select.=HtmlHelper::option($element,$element,$selectedValues==$element);				
				else 
					$select.=HtmlHelper::option($element->getId(),$element,$selectedValues==$element.'' );
			}
		$select.='</select>';
		return $select;
	}
	static function option($value,$display,$selected=false){
		if($selected)
			return '<option selected="selected" value=\''.$value.'\'>'.$display.'</option>';		
		return '<option value=\''.$value.'\'>'.$display.'</option>';
	}
	static function deleteButton($text,$value,$path,$nonce){
		$theForm='<form action=\''.urldecode($path).'\' method=\''.POST.'\' >';
		$theForm.=HtmlHelper::input('_redirect','hidden','referer');
		$theForm.=HtmlHelper::input('_wpnonce','hidden',wp_create_nonce($nonce));		
		$theForm.=HtmlHelper::input('_method','hidden','delete');
		$theForm.=HtmlHelper::input('Id','hidden',$value);
		$theForm.=HtmlHelper::input('delete'.$value,'submit',$text,'button-secondary');
		$theForm.='</form>';
		echo $theForm;
	}
	static function viewLink($uri,$text,$id){
		echo '<a href=\''.$uri.'&Id='.$id.'\' class=\'button-secondary\' >'.$text.'</a>';
	}
	static function a($text,$path,$class=false){
		$class=$class?' class=\''.$class.'\' ':'';
		echo '<a href=\''.$path.'\''.$class.'>'.$text.'</a>';
	}
	static function img($src,$alt=false,$class=false){
		$class=$class?" class='$class'":'';
		$alt=$alt?" alt='".$alt."'":'';
		echo "<img $class src='$src' $alt />";
	}
	static function imglink($src,$path,$alt=false,$class=false){
		$class=$class?' class=\''.$class.'\' ':'';
		$alt=$alt?" alt='".$alt."'":'';
		echo "<a href='$path' $class><img src='$src' $alt /></a>";		
	}
	static function table($data,$headlines=false){
		$table='<table class="widefat post fixed">';
		$tbody.='<tbody>';
		foreach($data as $row){
			$class=strtolower(get_class($row));
			$tbody.='<tr>';
			ob_start();
			HtmlHelper::viewLink(admin_url("admin.php?page=$class&action=edit"),'Edit',$row->getId());
			$tbody.='<td style=\'width:50px;vertical-align:middle;\'>'.ob_get_contents().'</td>';
			ob_end_clean();
			if(!$headlines)
				$headlines=ObjectUtility::getProperties($row);
			foreach($headlines as $column){
				$method='get'.$column;
				$tbody.='<td>'.$row->$method().'</td>';
			}
			ob_start();
			HtmlHelper::deleteButton('Delete',$row->getId(),get_bloginfo('url').'/'.$class.'/delete',$class);
			$tbody.='<td style=\'width:50px;\'>'.ob_get_contents().'</td>';
			ob_end_clean();			
			$tbody.='</tr>';
		}
		$tbody.='</tbody>';
		$ths='';
		foreach($headlines as $column){
			$ths.='<th>'.$column.'</th>';
		}
		$table.='<thead><tr><th style=\'width:50px;\'></th>'.$ths.'<th style=\'width:60px;\'></th></tr></thead>';
		$table.='<tfoot><tr><th style=\'width:50px;\'></th>'.$ths.'<th style=\'width:600px;\'></th></tr></tfoot>';
		$table.=$tbody;
		$table.='</table>';
		echo $table;
	}
	static function ActionPath($class,$type){
		echo get_bloginfo('url').'/'.strtolower($class).'/'.strtolower($type);
	}
}
?>