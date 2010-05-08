<?php

class HtmlHelper{
	private static $scripts;
	static function createForm($id,$object,$path=false,$classes=false,$imagepath=''){
		if(!$path)
			$path=get_bloginfo('url').'/'.get_class($object).'/create';
		self::form($id,$object,$path,POST,'Add new',strtolower(get_class($object)),$classes,$imagepath);
	}
	static function updateForm($id,$object,$path=false,$classes=false,$imagepath=''){
		if(!$path)
		$path=get_bloginfo('url').'/'.get_class($object).'/update';
		self::form($id,$object,$path,POST,'Save',strtolower(get_class($object)),$classes,$imagepath);
	}	
	static function form($formid,$object,$action,$method,$submit='Send',$nonce=false,$classes=false,$imagepath=''){
		$imagepath=trim($imagepath,'/');
		$imagepath=$imagepath?$imagepath.'/':'';
		$elements=ObjectUtility::getPropertiesAndValues($object);
		$upload=$method==POST?'enctype="multipart/form-data"':'';
		$theForm="<form id='$formid' action='$action' method='$method' $upload ><table class='form-table'>";
		$theForm.=self::input('_redirect','hidden','referer',false,true);
		if($nonce)
		$theForm.=self::input('_wpnonce','hidden',wp_create_nonce($nonce),false,true);
		$validation=array();
		foreach($elements as $id => $value){
			if($id=='Id'){
				if($value>0)
				$theForm.=self::input($id,'hidden',$value,false,true);
			}else{
				$settings=ObjectUtility::getCommentDecoration($object,'get'.$id);
				if(array_key_exists('new',$settings))
					continue;
				$rules=array_key_exists_v('validation',$settings);
				$required='required';
				if($rules){
					if(stripos($rules,'required')===false)
					$required=false;
					$rules=str_replace('=',':',$rules);
					$rules=str_replace('|',',',$rules);
					$validation[$id]='{'.$rules.'}';
				}
				$field=array_key_exists_v('field',$settings);				
				if($field=='hidden'){
					$theForm.=self::input($id,$field,stripslashes(str_replace('"','',$value)),false,true);
					continue;
				}
				$theForm.="<tr class=\"$id\" valign=\"top\" >";
				$theForm.="<th class=\"$id\"  scope=\"row\">";
					$theForm.=self::label($id,$required,true);
				$theForm.="</th><td class=\"$id\" >";
				if(!$field)
					$field='text';
				if($field=='textarea'){
					$theForm.=self::textarea($id,stripslashes($value),false,true);
				}
				else if($field=='image'){
					if($value){
						$theForm.=self::input($id.'_hasimage','hidden',$value?$value:'',false,true);
						if(strpos($value,'http')===false)
							$source=UPLOADS_URI.$imagepath.$value;
						else
							$source=$value;
						$theForm.=self::imglink($id,$source.'?TB_iframe=true',$source,'Full image','thickbox',true);
						$theForm.='<br />';
					}
					$theForm.=self::input($id,'file',$value,false,true);
				}
				else if($field=='dropdown'){
					$dbfield=array_key_exists_v('dbrelation',$settings);
					if($dbfield){
						$temp = new $dbfield();
						$selects=$temp->findAll();
					}
					$values=array_key_exists_v('values',$settings);
					$theForm.=self::select($id,$selects,false,$value,true);
					if($dbfield && array_key_exists_v('addnew',$settings)=='true'){
						$theForm.=self::a('Add new',PACKAGEURL.'plain.php?controller='.strtolower($dbfield).'&action=createnew&TB_iframe=true&height=230&width=340','thickbox button-secondary',true);
					}
				}
				else if($field=='url'){
					$theForm.=self::input($id,'text',str_replace('"','',$value),'regular-text',true);
					$theForm.='<br />'.self::ax('Test link',$value,false,false,'_blank',true);
				}
				else if($field=='currency'){
					$theForm.=self::currencydropdown($id,$value,true);
				}else if($field=='rating'){
					$selects=array(1=>"Very bad",2=>"Bad",3=>"Average",4=>"Good",5=>"Very good");
					$theForm.="<div id='stars$id' style='display:block;height:20px'>";
					$theForm.=self::select($id,$selects,false,$value,true);
					$theForm.="</div>";
					$stars[]="stars$id";
				}
				else
				$theForm.=self::input($id,$field,stripslashes(str_replace('"','',$value)),false,true);
				$theForm.='</td></tr>';
			}
		}
		$arrays=ObjectUtility::getArrayPropertiesAndValues($object);
		foreach($arrays as $id => $value){
			$settings=ObjectUtility::getCommentDecoration($object,$id.'List');
			if(array_key_exists_v('new',$settings))
			continue;

			$rules=array_key_exists_v('validation',$settings);
			$required='required';
			if($rules){
				if(stripos($rules,'required')===false)
					$required=false;
				$rules=str_replace('=',':',$rules);
				$rules=str_replace('|',',',$rules);
				$validation[$id.'_list']='{'.$rules.'}';
			}

			$field=array_key_exists_v('field',$settings);
			$theForm.='<tr valign=\'top\'>';
			$theForm.='<th scope=\'row\'>';
			$theForm.=self::label($id,$required,true);
			$theForm.='</th><td>';
				
			if($field){
				if($field=='text'){
					$tags[]="#$id";
					$dbfield=array_key_exists_v('dbrelation',$settings);
					$value=array();
					if($dbfield){
						$method=$id.'List';
						$value=$object->$method(); //Repo::findAll($dbfield);
						if(!$value){
							$method=$id.'ListLazy';
							$value=$object->$method();
						}
					}
					$seperator=array_key_exists_v('seperator',$settings);
					if(!$seperator)
						$seperator=',';
					if($value)
						$list=implode($seperator,$value);
					$values=$value;
					$theForm.=self::input($id.'_input','text','',false,true);
					$theForm.="<input id=\"".$id."_add\" name=\"".$id."_add\" value=\"Tag it\" class=\"button-secondary\" type=\"button\" onclick=\"tagit('".$id."_input','".$id."','".$id."_list')\">";
					$theForm.="<ul id=\"$id\">";
					if(is_array($values))
						foreach($values as $value){
							$itemid=str_replace(' ','-',$value);
							$theForm.="<li id=\"$itemid\" class=\"ui-corner-all\"><span>$value<a href=\"javascript:detagit('#$id"."_list','#$itemid','$value')\">x</span></a></li>";					
						}
					$theForm.="</ul>";
					$theForm.=self::input($id.'_list','hidden',$list,false,true);
				}else if($field=='multiple'){
					$dbfield=array_key_exists_v('dbrelation',$settings);
					if($dbfield){
						$value=Repo::findAll($dbfield);
					}
					$theForm.=self::select($id.'_list',$value,true,false,true);
					if($dbfield && array_key_exists_v('addnew',$settings)=='true'){
						$theForm.=self::a('Add new',PACKAGEURL.'plain.php?controller='.strtolower($dbfield).'&action=createnew&TB_iframe=true&height=230&width=340','thickbox button-secondary',true);
					}
					
				}
			}else{
				$theForm.=self::select($id,$value,false,false,true);
			}
			$theForm.='</td>';
		}
		$theForm.='</table>';
		$theForm.='<p class="submit">';
		if($classes)
		$theForm.=self::input('submit','submit',$submit,array_key_exists_v('submit',$classes),true );
		else
		$theForm.=self::input('submit','submit',$submit,"button-primary",true);
		$theForm.='</p></form>';
		$script='';
		if(sizeof($validation)>0){
			$rules=array();
			foreach($validation as $id => $rule)
				$rules[]=$id.':'.$rule;
			$script='
			jQuery(document).ready(function(){jQuery("#'.$formid.'").validate({rules:{'.implode(',',$rules).'}});});
			';
			self::registerFooterScript($script);
		}
		if(isset($stars)){
			foreach($stars as $id)
				$script.="
				jQuery(\"#$id\").stars({inputType: \"select\"});
				";
			self::registerFooterScript($script);
		}
		if(isset($tags)){		
			foreach($tags as $id){
				
				$script.="jQuery('$id"."_input').keydown(function(e) {
					if(e.keyCode == 13){
						e.preventDefault();
						jQuery('$id"."_add').click();
						return false;
					}
				});
				";
			}
				$script.="
				function tagit(input,list,field){
					input='#'+input;
					list='#'+list;
					field='#'+field;
					value=jQuery(input).val();
					if(!value)
						return;
					jQuery(input).val('');
					id=value.replace(' ','-');
					jQuery(list).append('<li id=\"'+id+'\" class=\"ui-corner-all\"><span>'+value+'<a href=\"javascript:detagit(\''+field+'\',\'#'+id+'\',\''+value+'\')\">x</span></a></li>');
					old=jQuery(field).val();
					jQuery(field).val(old+','+value);
				};";
				$script.="
				function detagit(field,id,value){
					jQuery(id).remove();
					old=jQuery(field).val();					
					temp=old.replace(','+value,'');
					temp=temp.replace(value,'');	
					jQuery(field).val(temp);	
				};";
			self::registerFooterScript($script);
		}
		echo $theForm;
	}
	static function label($id,$class=false,$dontprint=false){
		$class=$class?"class='$class' ":'';
		if($dontprint)
			return "<label for='$id' $class >$id:</label>";
		echo "<label for='$id' $class >$id:</label>";
	}
	static function input($id,$type,$value,$class=false,$dontprint=false){
		$class=$class?"class=\"$class\" ":'';
		if($dontprint)
		return "<input id=\"$id\" name=\"$id\" type=\"$type\" value=\"$value\"  $class >";
		echo  "<input id=\"$id\" name=\"$id\" type=\"$type\" value=\"$value\"  $class >";
	}
	static function textarea($id,$value,$class=false,$dontprint=false){
		$class=$class?"class='$class' ":'';
		if($dontprint)
		return "<textarea id=\"$id\" name=\"$id\" rows=\"14\" cols=\"40\" $class>$value</textarea>";
		echo "<textarea id=\"$id\" name=\"$id\" rows=\"14\" cols=\"40\" $class>$value</textarea>";
			
	}
	static function currencydropdown($id,$selectedCurrency,$dontprint=false){
		$currency=array("USD"=>"United States Dollars","CAD"=>"Canada Dollars","EUR"=>"Euro","GBP"=>"United Kingdom Pounds");
		$select="<select id=\"$id\" name=\"$id\" >";
		foreach($currency as $key => $element){
			$select.=self::option(str_replace('"','',$key),$element,$selectedCurrency==$element,true);
		}
		$select.='</select>';
		if($dontprint)
			return $select;
		echo $select;
	}
	static function select($id,$array,$multiple=false,$selectedValues=false,$dontprint=false){
		$select="<select id=\"$id\" name=\"$id\"";
		if($multiple)
		$select.=" multiple=\"multiple\" style=\"height:70px\" size=\"5\"";
		$select.=' >';
		$select.=self::option(0,'None',false,true);
		if(is_array($array))
			foreach($array as $key=>$element){
				if(is_string($element) || is_int($element)){
					$select.=self::option(str_replace('"','',$key),$element,$selectedValues==$key,true);
				}
				else
				$select.=self::option($element->getId(),$element,$selectedValues==$element.'',true );
			}
		$select.='</select>';
		if($dontprint)
			return $select;
		echo $select;
	}
	static function option($value,$display,$selected=false,$dontprint=false){
		$text="<option value=\"$value\">$display</option>";
		if($selected)
		$text="<option selected=\"selected\" value=\"$value\">$display</option>";
		if($dontprint)
		return $text;
		echo $text;

	}
	static function deleteButton($text,$value,$path,$nonce,$dontprint=false){
		$theForm="<form action=\"".urldecode($path)."\" method=\"".POST."\" >";
		$theForm.=self::input('_redirect','hidden','referer',false,true);
		$theForm.=self::input('_wpnonce','hidden',wp_create_nonce($nonce),false,true);
		$theForm.=self::input('_method','hidden','delete',false,true);
		$theForm.=self::input('Id','hidden',$value,false,true);
		$theForm.=str_replace('>'," onclick=\"return confirm('Are you sure you want to delete?')\" >",self::input('delete'.$value,'submit',$text,'button-secondary',true));		
		$theForm.='</form>';
		if($dontprint)
			return $theForm;
		echo $theForm;
	}
	static function viewLink($uri,$text,$id,$dontprint=false){
		if($dontprint)
			return "<a href=\"$uri&Id=$id\" class=\"button-secondary\" >$text</a>";
		echo "<a href=\"$uri&Id=$id\" class=\"button-secondary\" >$text</a>";
	}
	static function ax($text,$path,$class=false,$target=false,$onclick=false,$dontprint=false){
		$class=$class?" class=\"$class\" ":'';
		$target=$target?" target=\"$target\" ":'';
		$onclick=$onclick?" onclick=\"$onclick\" ":'';
		$text=stripslashes($text);
		if($dontprint)
		return "<a href=\"$path\" $class>$text</a>";
		echo "<a href=\"$path\" $class>$text</a>";
	}	
	static function a($text,$path,$class=false,$dontprint=false){
		$class=$class?" class=\"$class\" ":'';
		$text=stripslashes($text);
		if($dontprint)
		return "<a href=\"$path\" $class>$text</a>";
		echo "<a href=\"$path\" $class>$text</a>";
	}
	static function img($src,$alt=false,$class=false,$dontprint=false){
		$class=$class?" class='$class'":'';
		$alt=$alt?" alt='".$alt."'":'';
		if($dontprint)
			return 	"<img $class src='$src' $alt />";
		echo "<img $class src='$src' $alt />";
	}
	static function imglink($id,$src,$path,$alt=false,$class=false,$dontprint=false){
		$class=$class?' class=\''.$class.'\' ':'';
		$alt=$alt?" alt='".$alt."'":'';
		if($dontprint)
			return "<a id=\"$id\" href='$path' $class><img src='$src' $alt /></a>";
		echo "<a id=\"$id\" href='$path' $class><img src='$src' $alt /></a>";
	}
	static function table($data,$headlines=false){
		$table='<table class="ui-widget ui-corner-all">';
		$tbody.='<tbody>';
		foreach($data as $row){
			$class=strtolower(get_class($row));
			$tbody.='<tr>';
			$tbody.='<td class="first" style=\'width:50px;vertical-align:middle;\'>'.self::viewLink(admin_url("admin.php?page=$class&action=edit"),'Edit',$row->getId(),true).'</td>';
			if(!$headlines)
			$headlines=ObjectUtility::getProperties($row);
			$tbody.='<td class="center" style="width:20px;">'.self::input('select'.$row->getId(),'checkbox',$row->getId(),false,true).'</td>';
			foreach($headlines as $column){
				$method='get'.$column;
				$tbody.='<td>'.$row->$method().'</td>';
			}
			$tbody.='<td style=\'width:50px;\'>'.self::deleteButton('Delete',$row->getId(),get_bloginfo('url').'/'.$class.'/delete',$class,true).'</td>';
			$tbody.='</tr>';
		}
		$tbody.='</tbody>';
		$ths='';
		foreach($headlines as $column){
			$ths.='<th class="'.strtolower($column).'">'.$column.'</th>';
		}
		$table.='<thead><tr><th class="edit"></th><th class="center" style="width:15px;text-align:center">'.self::input('selectAllTop','checkbox','all',false,true).'</th>'.$ths.'<th class="delete"></th></tr></thead>';
		$table.='<tfoot><tr><th></th><th class="center" style="width:20px;text-align:center">'.self::input('selectAllBottom','checkbox','all',false,true).'</th>'.$ths.'<th class="delete"></th></tr></tfoot>';
		$table.=$tbody;
		$table.='</table></div>';
		echo $table;
	}
	static function ActionPath($class,$type){
		echo get_bloginfo('url').'/'.strtolower($class).'/'.strtolower($type);
	}
	static function Paging($href,$total,$currentpage,$perpage,$dontprint=false){
		$paging='<div class="tablenav-pages">';
		$paging.='<span class="displaying-num">';
		$pages=ceil($total/$perpage);
		/*
		 * Current page 2
		 * Perpage 10
		 * Total 23
		 * Page 1 1-10  1=perpage*currentpage-perpage+1 10=perpage*currentpage
		 * Page 2 11-20 11=perpage*currentpage-perpage+1 =10*2-10+1=11 20=perpage*currentpage
		 * Page 3 21-23 21=10*3-10+1=21 23=$total
		 */
		$start=$perpage*$currentpage-$perpage+1;
		$end=($perpage*$currentpage<=$total)?$perpage*$currentpage:$total;
		$paging.="Displaying $start-$end of ".'<span class="total-type-count">'.intval($total).'</span></span>';
		if(($currentpage-1)>1)
			$paging.=self::a('«',"$href?page=".intval($page).'&perpage='.intval($perpage),'page-numbers prev',true);		
		for($page=1;$page<$pages;$page++)
			if($page!=$currentpage)
				$paging.=self::a($page,"$href?page=".intval($page).'&perpage='.intval($perpage),'page-numbers',true);
			else
				$paging.='<span class="page-numbers current">'.intval($currentpage).'</span>';			
		if(($currentpage-1)<$pages)
			$paging.=self::a('»',"$href?page=".intval($page).'&perpage='.intval($perpage),'page-numbers next',true);
	}
	static function notification($id,$message,$error=false){
		if($error)
			echo "<div id=\"$id\" class=\"ui-state-error ui-corner-all\">$message</div>";		
		else
			echo "<div id=\"$id\" class=\"ui-state-highlight ui-corner-all\">$message</div>";
	}	
	static function registerFooterScript($script){
		self::$scripts[]=$script;
	}
	static function getFooterScripts(){
		return self::$scripts;
	}
	/*$_GET = array_map(’confHtmlEnt’, $_GET);

A nice function is


function cleanData($data) {
$data = trim($data);
$data = htmlentities($data);
$data = mysql_real_escape_string($data);
}

$_POST = array_map('cleanData', $_POST);*/
}