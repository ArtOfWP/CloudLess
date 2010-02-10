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
	static function form($formid,$object,$action,$method,$submit='Send',$nonce=false,$classes=false){
		$elements=ObjectUtility::getPropertiesAndValues($object);
		$upload=$method==POST?'enctype="multipart/form-data"':'';
		$theForm="<form id='$formid' action='$action' method='$method' $upload ><table class='form-table'>";
		$theForm.=HtmlHelper::input('_redirect','hidden','referer',false,true);
		if($nonce)
		$theForm.=HtmlHelper::input('_wpnonce','hidden',wp_create_nonce($nonce),false,true);
		$validation=array();
		foreach($elements as $id => $value){
			if($id=='Id'){
				if($value>0)
				$theForm.=HtmlHelper::input($id,'hidden',$value,false,true);
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
				$theForm.='<tr valign=\'top\'>';
				$field=array_key_exists_v('field',$settings);
				$theForm.='<th scope=\'row\'>';
				$theForm.=HtmlHelper::label($id,$required,true);
				$theForm.='</th><td>';
				if(!$field)
					$field='text';
				if($field=='textarea'){
					$theForm.=HtmlHelper::textarea($id,stripslashes($value),false,true);
				}
				else if($field=='image'){
					if(strpos($value,'http')===false)
					$theForm.=HtmlHelper::img(WP_PLUGIN_URL.$value,'',false,true);
					else
					$theForm.=HtmlHelper::img($value,'',false,true);
					$theForm.='<br />';
					if($value)
						$theForm.=HtmlHelper::input($id.'_hasimage','hidden',$value?$value:'',false,true);
					$theForm.=HtmlHelper::input($id,'file',$value,false,true);
				}
				else if($field=='dropdown'){
					$dbfield=array_key_exists_v('dbrelation',$settings);
					if($dbfield){
						$temp = new $dbfield();
						$selects=$temp->findAll();
					}
					$values=array_key_exists_v('values',$settings);
					$theForm.=HtmlHelper::select($id,$selects,false,$value,true);
					if($dbfield && array_key_exists_v('addnew',$settings)=='true'){
						$theForm.=HtmlHelper::a('Add new',Communication::cleanUrl($_SERVER["REQUEST_URI"]).'?page='.strtolower($dbfield).'&action=createnew',false,true);
					}
				}
				else if($field=='url'){
					$theForm.=HtmlHelper::input($id,'text',str_replace('"','',$value),false,true);
					$theForm.='<br />'.HtmlHelper::a('Test link',$value,false,true);
				}
				else if($field=='currency'){
					$theForm.=HtmlHelper::currencydropdown($id,$value,true);
				}
				else
				$theForm.=HtmlHelper::input($id,$field,stripslashes(str_replace('"','',$value)),false,true);
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
			$theForm.=HtmlHelper::label($id,$required,true);
			$theForm.='</th><td>';
				
			if($field){
				if($field=='text'){
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
					$theForm.=HtmlHelper::input($id.'_list','text',$list,false,true);
				}else if($field=='multiple'){
					$dbfield=array_key_exists_v('dbrelation',$settings);
					if($dbfield){
						$value=Repo::findAll($dbfield);
					}
					$theForm.=HtmlHelper::select($id.'_list',$value,true,false,true);
					if($dbfield){
						$theForm.=	HtmlHelper::a('Add new',Communication::cleanUrl($_SERVER["REQUEST_URI"]).'?page='.strtolower($dbfield).'&action=createnew',false,true);
					}
				}
			}else{
				$theForm.=HtmlHelper::select($id,$value,false,false,true);
			}
			$theForm.='</td>';
		}
		$theForm.='</table>';
		$theForm.='<p class="submit">';
		if($classes)
		$theForm.=HtmlHelper::input('submit','submit',$submit,array_key_exists_v('submit',$classes),true );
		else
		$theForm.=HtmlHelper::input('submit','submit',$submit,"button-primary",true);
		$theForm.='</p></form>';
		$script='';
		if(sizeof($validation)>0){
			$rules=array();
			foreach($validation as $id => $rule)
			$rules[]=$id.':'.$rule;
			$script='<script>jQuery(document).ready(function(){jQuery("#'.$formid.'").validate({rules:{'.implode(',',$rules).'}});});</script>';
		}
		echo $theForm.$script;
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
			$select.=HtmlHelper::option(str_replace('"','',$key),$element,$selectedCurrency==$element,true);
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
		$select.=HtmlHelper::option(0,'None',false,true);
		if(is_array($array))
			foreach($array as $element){
				if(is_string($element) || is_int($element)){
					$key=key($array);
					$select.=HtmlHelper::option(str_replace('"','',$key),$element,$selectedValues==$element,true);
				}
				else
				$select.=HtmlHelper::option($element->getId(),$element,$selectedValues==$element.'',true );
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
		$theForm.=HtmlHelper::input('_redirect','hidden','referer',false,true);
		$theForm.=HtmlHelper::input('_wpnonce','hidden',wp_create_nonce($nonce),false,true);
		$theForm.=HtmlHelper::input('_method','hidden','delete',false,true);
		$theForm.=HtmlHelper::input('Id','hidden',$value,false,true);
		$theForm.=str_replace('>'," onclick=\"return confirm('Are you sure you want to delete?')\" >",HtmlHelper::input('delete'.$value,'submit',$text,'button-secondary',true));		
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
	static function imglink($src,$path,$alt=false,$class=false,$dontprint=false){
		$class=$class?' class=\''.$class.'\' ':'';
		$alt=$alt?" alt='".$alt."'":'';
		if($dontprint)
			return "<a href='$path' $class><img src='$src' $alt /></a>";
		echo "<a href='$path' $class><img src='$src' $alt /></a>";
	}
	static function table($data,$headlines=false){
		$table='<table class="ui-widget ui-corner-all">';
		$tbody.='<tbody>';
		foreach($data as $row){
			$class=strtolower(get_class($row));
			$tbody.='<tr>';
			$tbody.='<td class="first" style=\'width:50px;vertical-align:middle;\'>'.HtmlHelper::viewLink(admin_url("admin.php?page=$class&action=edit"),'Edit',$row->getId(),true).'</td>';
			if(!$headlines)
			$headlines=ObjectUtility::getProperties($row);
			$tbody.='<td class="center" style="width:20px;">'.HtmlHelper::input('select'.$row->getId(),'checkbox',$row->getId(),false,true).'</td>';
			foreach($headlines as $column){
				$method='get'.$column;
				$tbody.='<td>'.$row->$method().'</td>';
			}
			$tbody.='<td style=\'width:50px;\'>'.HtmlHelper::deleteButton('Delete',$row->getId(),get_bloginfo('url').'/'.$class.'/delete',$class,true).'</td>';
			$tbody.='</tr>';
		}
		$tbody.='</tbody>';
		$ths='';
		foreach($headlines as $column){
			$ths.='<th class="'.strtolower($column).'">'.$column.'</th>';
		}
		$table.='<thead><tr><th class="edit"></th><th class="center" style="width:15px;text-align:center">'.HtmlHelper::input('selectAllTop','checkbox','all',false,true).'</th>'.$ths.'<th class="delete"></th></tr></thead>';
		$table.='<tfoot><tr><th></th><th class="center" style="width:20px;text-align:center">'.HtmlHelper::input('selectAllBottom','checkbox','all',false,true).'</th>'.$ths.'<th class="delete"></th></tr></tfoot>';
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
			$paging.=HtmlHelper::a('«',"$href?page=".intval($page).'&perpage='.intval($perpage),'page-numbers prev',true);		
		for($page=1;$page<$pages;$page++)
			if($page!=$currentpage)
				$paging.=HtmlHelper::a($page,"$href?page=".intval($page).'&perpage='.intval($perpage),'page-numbers',true);
			else
				$paging.='<span class="page-numbers current">'.intval($currentpage).'</span>';			
		if(($currentpage-1)<$pages)
			$paging.=HtmlHelper::a('»',"$href?page=".intval($page).'&perpage='.intval($perpage),'page-numbers next',true);
	}
	static function notification($id,$message,$error=false){
		if($error)
			echo "<div id=\"$id\" class=\"ui-state-error ui-corner-all\">$message</div>";		
		else
			echo "<div id=\"$id\" class=\"ui-state-highlight ui-corner-all\">$message</div>";
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
?>