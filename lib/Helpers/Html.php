<?php
namespace CLMVC\Helpers;
/**
 * Class Html
 * Contains helper methods to generate HTML code
 */
class Html {
	private static $scripts=array();

    /**
     * Print a form that is used to create objects
     * @param string $id
     * @param string|object $object
     * @param bool $path
     * @param bool $classes
     * @param string $imagepath
     */
    static function createForm($id,$object,$path=false,$classes=false,$imagepath=''){
		if(!$path)
			$path=action_url($object,'create',true);
		self::form($id,$object,$path,POST,'Add New',strtolower(get_class($object)),$classes,$imagepath);
	}

    /**
     * Print a form that is used to update objects
     * @param string $id
     * @param string|object $object
     * @param bool $path
     * @param bool $classes
     * @param string $imagepath
     */
    static function updateForm($id,$object,$path=false,$classes=false,$imagepath=''){
		if(!$path)
		$path=action_url($object,'update',true);
		self::form($id,$object,$path,POST,'Update',strtolower(get_class($object)),$classes,$imagepath);
	}

    /**
     * Generate a form based on the supplied object
     * @param string $formid
     * @param object|string $object
     * @param string $action
     * @param string $method
     * @param string $submit
     * @param bool $nonce
     * @param bool $classes
     * @param string $imagepath
     */
    static function form($formid,$object,$action,$method,$submit='Send',$nonce=false,$classes=false,$imagepath=''){
		$imagepath=trim($imagepath,'/');
		$imagepath=$imagepath?$imagepath.'/':'';
		$elements=ObjectUtility::getPropertiesAndValues($object);
		$upload=$method==POST?'enctype="multipart/form-data"':'';
		$theForm='<form id="'.$formid.'" action="'.$action.'" method="'.$method.'" '.$upload.' ><table class="form-table">';
		$theForm.=self::input('_redirect','hidden','referer',false,true);
		if($nonce){
			$theForm.=self::input('_asnonce','hidden',Security::create()->create_nonce($nonce),false,true);
		}
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
					$theForm.=self::textarea($id,stripslashes($value),$id,true);
				}
				else if($field=='htmlarea'){
                    global $wp_version;
                    if(version_compare($wp_version,'3.3','<')){
                        $theForm.='<p class="alignright">
	<a class="button toggleVisual">Visual</a>
	<a class="button toggleHTML">HTML</a>
</p>';
                        $htmlarea=$id;
                        $theForm.=self::textarea($id,stripslashes($value),"$id theEditor",true);
                    }else{
                        ob_start();
					    initiate_editor($id,stripslashes($value));
                        $htmlarea=ob_get_contents();
                        ob_end_clean();
                        $theForm.= $htmlarea;
                    }
				}
				else if($field=='image'){
					if($value){
						$theForm.=self::input($id.'_hasimage','hidden',$value?$value:'',false,true);
						if(strpos($value,'http')===false)
							$source=UPLOADS_URI.$imagepath.$value;
						else
							$source=$value;
						$theForm.=self::imglink($source.'?TB_iframe=true',$source,'Full image','thickbox',true.$id);
						$theForm.='<br />';
					}
					$theForm.=self::input($id,'file',$value,false,true);
				}
				else if($field=='dropdown'){
					$dbfield=array_key_exists_v('dbrelation',$settings);
					$fillmethod=array_key_exists_v('fillmethod',$settings);
					if($dbfield){
						$temp = new $dbfield();
						if($fillmethod)
							$selects=$temp->$fillmethod();
						else
							$selects=$temp->findAll();
					}else{
						$values=array_key_exists_v('values',$settings);
						$values=trim($values,"{}");
						$values=explode('|',$values);
						$selects=array();
						foreach($values as $pair){
							$split=explode('=',$pair);
							if(sizeof($split)>1)
								$selects[$split[0]]=$split[1];
							else
								$selects[]=$split[0];
						}
					}
					if(strpos($fillmethod,'Sorted')!==false)
						$theForm.=self::selectDropdownSorted($id,$selects,$value,true);
					else if($values)
						$theForm.=self::selectSimple($id,$selects,$value,true);
					else
						$theForm.=self::select($id,$selects,false,$value,true);
					if($dbfield && array_key_exists_v('addnew',$settings)=='true'){
						$theForm.=self::a('Add new',PACKAGEURL.'plain.php?controller='.strtolower($dbfield).'&amp;action=createnew&amp;TB_iframe=true&amp;height=230&amp;width=340','thickbox button-secondary',true);
					}
				}
				else if($field=='url'){
					$theForm.=self::input($id,'text',str_replace('"','',$value),'regular-text',true);
					$theForm.='<br />'.self::ax('Test link',$value,false,'_blank',false,true);
				}
				else if($field=='checkbox'){					
					$theForm.=self::checkbox($id,1,$value,'checkbox',true);
				}
				else if($field=='currency'){
					$theForm.=self::currencydropdown($id,$value,true);
				}else if($field=='rating'){
					$selects=array(1=>"Very bad",2=>"Bad",3=>"Average",4=>"Good",5=>"Very good");
					$theForm.="<div id='stars$id' style='display:block;height:20px'>";
					$theForm.=self::select($id,$selects,false,$value,true);
					$theForm.="</div>";
					$stars[]="stars$id";
				}else if($field=='autosuggest'){
					$autosuggest=$id;
				}else if($field=='unique'){
					$unique=$id;	
					$validator=array_key_exists_v('validator',$settings);
					$theForm.=self::input($id,'text',stripslashes(str_replace('"','',$value)),false,true);
				}else if($field=='custom'){
					$split=array_key_exists_v('custommethod',$settings);
					if($split){
						$split=explode('|',$split);
						$theForm.="<select id=\"$id\" name=\"$id\">".call_user_func(array($split[0],$split[1]),$value)."</select>";
					}
				}else
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
							$theForm.="<li id=\"tag_$itemid\" class=\"ui-corner-all\"><span>$value<a href=\"javascript:detagit('#$id"."_list','#tag_$itemid','$value')\">x</span></a></li>";
						}
					$theForm.="</ul>";
					$theForm.=self::input($id.'_list','hidden',$list,false,true);
				}else if($field=='multiple'){
					$dbfield=array_key_exists_v('dbrelation',$settings);
					$method=$id.'List';
					$values=$object->$method(); //Repo::findAll($dbfield);
					if(!$values){
						$method=$id.'ListLazy';
						$values=$object->$method();
					}
					$temp=array();
					if($values)
						foreach($values as $value2)
							$temp[$value2->getId()]=$value2->getId();
					else
						$temp=false;
					if($dbfield){
						$value=Repo::findAll($dbfield);
					}
					$theForm.=self::select($id.'_list',$value,true,$temp,true);
					/*if($dbfield && array_key_exists_v('addnew',$settings)=='true'){
						$theForm.=self::a('Add new',PACKAGEURL.'plain.php?controller='.strtolower($dbfield).'&action=createnew&TB_iframe=true&height=230&width=340','thickbox button-secondary',true);
					}*/
					
				}
			}else{
				$theForm.=self::select($id,$value,false,false,true);
			}
			$theForm.='</td></tr>';
		}

		$theForm.=View::generate('render-form-'.$formid,array($object,$action,$method,$submit,$nonce,$classes,$imagepath));
		$theForm.=View::generate('render-form-'.$formid.'-'.$action,array($object,$action,$method,$submit,$nonce,$classes,$imagepath));
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
			$script='
		jQuery(document).ready(function(){';			
			if($unique)
			$script.='
			jQuery.validator.addMethod("unique", function(value, element) { 
				 return '.$validator.'("'.$formid.'",value,element); 
			}, jQuery.format("Already exists"));
			';
			if($autosuggest)
				$script.='';
			foreach($validation as $id => $rule)
				$rules[]=$id.':'.$rule;
			$script.='			
				jQuery("#'.$formid.'").validate({rules:{'.implode(',',$rules).'}});
		});
		';
			self::registerFooterScript($script);
		}
//TODO make it better
        global $wp_version;

		if(version_compare($wp_version,'3.3','<')){
            $script="jQuery(document).ready(function() {
        	var id = '$htmlarea';
	        jQuery('a.toggleVisual').click(
		function() {
			tinyMCE.execCommand('mceAddControl', false, id);
		}
	);
	jQuery('a.toggleHTML').click(
		function() {
			tinyMCE.execCommand('mceRemoveControl', false, id);
		}
	);
});";
			self::registerFooterScript($script);
		}

		if(isset($stars)){
			$script='';
			foreach($stars as $id)
				$script.="
				jQuery(\"#$id\").stars({inputType: \"select\"});
				";
			self::registerFooterScript($script);
		}
		if(isset($tags)){	
			$script='';	
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
					if(old)
						jQuery(field).val(old+','+value);
					else
						jQuery(field).val(value);
				};";
				$script.="
				function detagit(field,id,value){
					jQuery(id).remove();
					old=jQuery(field).val();					
					temp=old.replace(value,'');
					temp=old.replace(',,',',');
					temp=temp.replace(value,'');
					jQuery(field).val(temp);	
				};";
			self::registerFooterScript($script);
		}
		echo $theForm;
	}

    /**
     * Return or print a label
     * @param string $id the id of the element to connect label too
     * @param string $class
     * @param bool $dont_print
     * @return string
     */
    static function label($id, $class='', $dont_print=false){
		$class=$class?"class='$class' ":'';
		if($dont_print)
			return "<label for='$id' $class >$id:</label>";
		echo "<label for='$id' $class >$id:</label>";
	}

    /**
     * Return or print a checkbox
     * @param string $id
     * @param string $value
     * @param bool $checked
     * @param string $class
     * @param bool $dont_print
     * @return string
     */
    static function checkbox($id, $value, $checked=false, $class='', $dont_print=false){
		$class=$class?"class=\"$class\" ":'';
		$value=htmlspecialchars(strip_tags($value), ENT_QUOTES);
		$checked=$checked?" checked='checked'":'';
		if($dont_print)
			return "<input id=\"$id\" name=\"$id\" type=\"checkbox\" value=\"$value\" $checked $class />";
		echo  "<input id=\"$id\" name=\"$id\" type=\"checkbox\" value=\"$value\" $checked $class />";
	}

    /**
     * Return or print input tag
     * @param string $id
     * @param string $type
     * @param string $value
     * @param string $class
     * @param bool $dont_print
     * @return string
     */
    static function input($id, $type, $value, $class='',$dont_print=false){
		$class=$class?"class=\"$class\" ":'';
		$value=htmlspecialchars(strip_tags($value), ENT_QUOTES);
		if($dont_print)
			return "<input id=\"$id\" name=\"$id\" type=\"$type\" value=\"$value\"  $class />";
		echo  "<input id=\"$id\" name=\"$id\" type=\"$type\" value=\"$value\"  $class />";
	}

    /**
     * Return or print textarea
     * @param string $id
     * @param string $value
     * @param string $class
     * @param bool $dont_print
     * @return string
     */
    static function textarea($id, $value, $class='', $dont_print=false){
		$class=$class?"class='$class' ":'';
		$value=htmlspecialchars($value, ENT_QUOTES);		
		if($dont_print)
		return "<textarea id=\"$id\" name=\"$id\" rows=\"14\" cols=\"40\" $class>$value</textarea>";
		echo "<textarea id=\"$id\" name=\"$id\" rows=\"14\" cols=\"40\" $class>$value</textarea>";
			
	}

    /**
     * Return or print currency dropdown.
     * @filter html_currency_dropdown_currencies
     * @param string $id
     * @param string $selectedCurrency
     * @param bool $dont_print
     * @param string $class
     * @return string
     */
    static function currencydropdown($id, $selectedCurrency, $dont_print=false, $class=''){
		$currency=Filter::run('html_currency_dropdown_currencies',array(array("USD"=>"United States Dollars","CAD$"=>"Canada Dollars","EUR"=>"Euro","GBP"=>"United Kingdom Pounds","SEK"=>"Swedish Kronor","NOK" => "Norwegian Kronor","AUD"=>"Australian dollar","CHF"=>"Swiss franc","KRW"=>"South Korean Won","JPY"=>"Japanese Yen","CNY"=>"Chinese Yuan (Renminbi)")));
		
		$class=$class?' class="'.$class.'" ':'';
		$select='<select id="'.$id.'" name="'.$id.'" '.$class.'>';
		foreach($currency as $key => $element)
			$select.=self::option(str_replace('"','',$key),$element,strtolower($selectedCurrency)==strtolower($key),true);
		$select.='</select>';
		if($dont_print)
			return $select;
		echo $select;
	}

    /**
     * Return or print formatted currency
     * @filter html_format_currency
     * @param $currency
     * @param $value
     * @param bool $decimals
     * @return mixed
     */
    static function formatCurrency($currency,$value,$decimals=false){
		$price=$value;
		$currency=strtolower($currency);
		if($decimals!==false)
			$value=number_format($value,$decimals);
		switch($currency){
			case 'aud':
			case 'usd':
				$price="$".$value;
				break;
			case 'gbp':
				$price="�".$value;
				break;
			case 'eur':
				$price="EUR ".$value;
				break;
			case 'cad':
				$price="CDN$".$value;
				break;
			case 'nok':
			case 'sek':
				$price=str_replace('.',',',$value).' kr';
				break;
			case 'chf':
				$price='Fr'.str_replace('.',',',$value);
			case 'krw':
				$price="&#8361;".$value;
			case 'cny':
			case 'jpy':
				$price="&yen;".$value;
			default:
				$price=$value.' '.$currency;
		}
		return Filter::run('html_format_currency',array($price,$currency,$value,$decimals));
	}

    /**
     * Return or print dropdown select
     * @param string $id
     * @param array $array
     * @param bool $selectedValues
     * @param bool $dont_print
     * @param bool $class
     * @return string
     */
    static function selectDropdownSorted($id,$array,$selectedValues=false,$dont_print=false,$class=false){
		$class=$class?' class="'.$class.'" ':'';
		$select='<select id="'.$id.'" name="'.$id.'" '.$class.'>';
		foreach($array as $key => $pair){
			if(is_array($pair)){
				if(is_string($pair['parent']) || is_int($pair['parent']))
					$select.=self::option(str_replace('"','',$key),$pair['parent'],$selectedValues==$key,true);
				else
					$select.=self::option($pair['parent']->getId(),$pair['parent'],$selectedValues==$pair['parent'].'',true );
				$children=$pair['children'];
				if(is_array($children)){
					foreach($children as $key2=>$element){
						if(is_string($element) || is_int($element)){
							$select.=self::option(str_replace('"','',$key2),' - '.$element,$selectedValues==$key2,true);
						}
						else
							$select.=self::option($element->getId(),' - '.$element,$selectedValues==$element.'',true );
					}
				}
			}else{
				if(is_string($pair) || is_int($pair))
					$select.=self::option(str_replace('"','',$pair),$pair,$selectedValues==$pair,true);
				else
					$select.=self::option($pair->getId(),$pair,$selectedValues==$pair.'',true );				
			}
		}		
		$select.='</select>';
		if($dont_print)
			return $select;
		echo $select;		
	}

    /**
     *
     * @param $id
     * @param string[]|object[] $array
     * @param bool $selectedValues
     * @param bool $dont_print
     * @param bool $class
     * @return string
     */
    static function selectSimple($id,$array,$selectedValues=false,$dont_print=false,$class=false){
		$class=$class?' class="'.$class.'" ':'';
		$select='<select id="'.$id.'" name="'.$id.'" '.$class.'>';
		if(is_array($array))
			foreach($array as $key=>$element){
				if(is_string($element) || is_int($element)){
					$select.=self::option(str_replace('"','',$key),$element,$selectedValues==$key||$selectedValues==$element,true);
				}
				else
					$select.=self::option($element->getId(),$element,$selectedValues==$element.'',true );
			}
		$select.='</select>';
		if($dont_print)
			return $select;
		echo $select;		
	}

    /**
     * Return or print select tag
     * @param string $id
     * @param string[]|array|object[] $array
     * @param bool $multiple
     * @param bool $selectedValues
     * @param bool $dont_print
     * @param string $class
     * @return string
     */
    static function select($id,$array,$multiple=false,$selectedValues=false,$dont_print=false,$class=''){
		$class=$class?"class=\"$class\" ":'';				
		$select='<select id="'.$id.'" name="'.$id;
		if($multiple)
			$select.='[]" multiple="multiple" size="5" ';
		else
			$select.='" ';
		$select.=$class.' >';
		$select.=self::option(0,'None',false,true);
		if(is_array($array))
			foreach($array as $key=>$element){
				if(is_string($element) || is_int($element)){
					$selected=false;
					if(is_array($selectedValues))
						$selected=array_key_exists($key,$selectedValues);
					else
						$selected=$selectedValues==$key;
					$select.=self::option(str_replace('"','',$key),$element,$selected,true);
				}
				else{
					if(is_array($selectedValues)){
						$selected=array_key_exists($element->getId().'',$selectedValues);
					}
					else
						if($selectedValues)
							$selected=$selectedValues->getId()==$element->getId();
					$select.=self::option($element->getId(),$element,$selected,true );
				}
			}
		$select.='</select>';
		if($dont_print)
			return $select;
		echo $select;
	}

    /**
     * Return or print option tag
     * @param string $value
     * @param string $display
     * @param bool $selected
     * @param bool $dont_print
     * @return string
     */
    static function option($value,$display,$selected=false,$dont_print=false){
		$display=htmlspecialchars($display, ENT_QUOTES);
		$text="<option";
		$value=htmlspecialchars($value, ENT_QUOTES);
		if($selected)
			$text.=' selected="selected"';
		$text.=' value="'.$value.'">'.$display.'</option>';
		if($dont_print)
			return $text;
		echo $text;

	}
	/*
	static function deleteLink($text,$value,$path,$nonce_base,$dont_print=false){
		$s = Security::create();		
		$link='"'.urldecode($path).'?_redirect=referer&_method=delete&_asnonce='.$s->create_nonce($nonce_base).'&Id='.$value."\"";
		$click=" onclick=\"return confirm('Are you sure you want to delete?')\"";
		$a="<a href=$link $onclick>$text</a>";
		if($dont_print)
			return $a;
		echo $a;
	}*/

    /**
     * Return or print an delete button
     * @param string $text
     * @param string$id
     * @param $path
     * @param $nonce_base
     * @param bool $dont_print
     * @return string
     */
    static function deleteButton($text,$id,$path,$nonce_base,$dont_print=false){
		$s = Security::create();
		$theForm="<form action=\"".urldecode($path)."\" method=\"".POST."\" >";
		$theForm.=self::input('_redirect','hidden','referer',false,true);
		$theForm.=self::input('_asnonce','hidden',$s->create_nonce($nonce_base),false,true);
		$theForm.=self::input('_method','hidden',DELETE,false,true);
		$theForm.=self::input('Id','hidden',$id,false,true);
		$theForm.=str_replace('/>'," onclick=\"return confirm('Are you sure you want to delete?')\" />",self::input('delete'.$id,'submit',$text,'button-secondary',true));
		$theForm.='</form>';
		if($dont_print)
			return $theForm;
		echo $theForm;
	}

    /**
     * Return or print an edit link for $id, link has class edit-link
     * @param string $uri
     * @param string $text
     * @param string $id
     * @param string $nonce
     * @param bool $dont_print
     * @return string
     */
    static function editLink($uri,$text,$id,$nonce,$dont_print=false){
		$nonce=Security::create()->create_nonce($nonce);
		if($dont_print)
			return "<a href=\"$uri&amp;Id=$id&amp;_asnonce=$nonce\" class=\"edit-link\" >$text</a>";
		echo "<a href=\"$uri&amp;Id=$id&amp;_asnonce=$nonce\" class=\"edit-link\" >$text</a>";
	}

    /**
     * Return or print an edit button for $id, link has classes button-secondary edit-button
     * @param string $uri
     * @param string $text
     * @param string $id
     * @param string $nonce
     * @param bool $dont_print
     * @return string
     */
    static function editButton($uri,$text,$id,$nonce,$dont_print=false){
		$nonce=Security::create()->create_nonce($nonce);
		if($dont_print)
			return "<a href=\"$uri&amp;Id=$id&amp;_asnonce=$nonce\" class=\"button-secondary edit-button\" >$text</a>";
		echo "<a href=\"$uri&amp;Id=$id&amp;_asnonce=$nonce\" class=\"button-secondary edit-button\" >$text</a>";
	}

    /**
     * Return or print a view link that points to $id
     * @param string $uri
     * @param string $text
     * @param string|int$id
     * @param bool $dont_print
     * @return string
     */
    static function viewLink($uri,$text,$id,$dont_print=false){
		if($dont_print)
			return "<a href=\"$uri&amp;Id=$id\" class=\"button-secondary\" >$text</a>";
		echo "<a href=\"$uri&amp;Id=$id\" class=\"button-secondary\" >$text</a>";
	}

    /**
     * Print or return an action link
     * @param string $text
     * @param string $href
     * @param string|string[] $class
     * @param string $target
     * @param string $onclick
     * @param bool $dont_print
     * @return string
     */
    static function ax($text, $href, $class='', $target='', $onclick='', $dont_print=false){
		$class=$class?" class=\"$class\" ":'';
		$target=$target?" target=\"$target\" ":'';
		$onclick=$onclick?" onclick=\"$onclick\" ":'';
		$text=stripslashes($text);
		$text=htmlspecialchars($text, ENT_QUOTES);
		if($dont_print)
    		return "<a href=\"$href\" $class $target $onclick>$text</a>";
		echo "<a href=\"$href\" $class $target $onclick>$text</a>";
	}

    /**
     * Print or return a link
     * @param string $text
     * @param string $href
     * @param string|string[] $class
     * @param bool $dont_print
     * @return string
     */
    static function a($text, $href, $class='',$dont_print=false){
		$class=$class?" class=\"$class\" ":'';
		$text=stripslashes($text);
		$text=htmlspecialchars($text, ENT_QUOTES);
		if($dont_print)
		    return "<a href=\"$href\" $class>$text</a>";
		echo "<a href=\"$href\" $class>$text</a>";
	}

    /**
     * Generate an image tag
     * @param string $src
     * @param string $alt
     * @param string|string[] $class
     * @param bool $dont_print
     * @return string
     */
    static function img($src,$alt='',$class='',$dont_print=false){
		$class=$class?" class='$class'":'';
		$alt=$alt?" alt='".htmlspecialchars(strip_tags($alt), ENT_QUOTES)."'":'';
		if($dont_print)
            return 	"<img $class src='$src' $alt />";
		echo "<img $class src='$src' $alt />";
	}

    /**
     * Generate an image link
     * @param string $src
     * @param string $href
     * @param bool $alt
     * @param bool $class
     * @param bool $dont_print
     * @param bool $id
     * @return string
     */
    static function imglink($src, $href, $alt=false, $class=false, $dont_print=false, $id=false){
		$class=$class?' class=\''.$class.'\' ':'';
		$alt=$alt?" alt='".htmlspecialchars($alt, ENT_QUOTES)."'":'';
		$id=$id?" id=\"$id\" ":'';
		if($dont_print)
			return "<a href='$href' $class><img src='$src' $alt /></a>";
		echo "<a href='$href' $class><img src='$src' $alt /></a>";
	}

    /**
     * Print or return a delete all button
     * @param string $text
     * @param string $id
     * @param string $path
     * @param string $nonce
     * @param bool $dont_print
     * @return string
     */
    static function deleteAllButton($text,$id,$path,$nonce, $dont_print=false){
		$theForm="<form action=\"".urldecode($path)."\" method=\"".POST."\" >";
		$theForm.=self::input('_redirect','hidden','referer',false,true);
		$theForm.=self::input('_asnonce','hidden',Security::create()->create_nonce($nonce),false,true);
		$theForm.=self::input('_method','hidden','delete',false,true);
		$theForm.=str_replace('>'," onclick=\"return confirm('Are you sure you want to delete selected?')\" >",self::input('delete'.$id,'submit',$text,'button-secondary',true));		
//		$theForm.='</form>';
		if($dont_print)
			return $theForm;
		echo $theForm;		
	}

    /**
     * Print or return the form closing tag
     * @param bool $dont_print
     * @return string
     */
    static function endForm($dont_print=false){
		if($dont_print)
			return "</form>";
		echo "</form>";		
	}

    /**
     * Generate an table element based on the supplied data.
     * @param string $id
     * @param array $data
     * @param bool $headlines
     * @param bool $nonce
     * @param bool $useLinks
     * @param string $cssClass
     */
    static function table($id,$data,$headlines=false,$nonce=false,$useLinks=false,$cssClass=''){
		$table="<table id=\"$id\" class=\"ui-widget ui-corner-all $cssClass\">\n";
		$tbody="
		<tbody>\n";
		foreach($data as $row){
			$class=strtolower(get_class($row));
			$tbody.="<tr>\n";
//			$tbody.="<td class=\"first\" style=\"vertical-align:middle;\">".self::editLink(admin_url("admin.php?page=$class&action=edit"),'Edit',$row->getId(),$nonce,true)."</td>\n";
			if(!$headlines)
				$headlines=ObjectUtility::getProperties($row);
			$tbody.='<td class="first checkbox"><input name="'.$class.'[]" type="checkbox" value="'.$row->getId().'"  class="all"  /></td>';
			$notFirst=false;
			foreach($headlines as $column){
				if(is_array($column)){
					$method='get'.$column[0];
					$value=$row->$method();					
					if($column[1]=='date'){
						$format=$column[2];
						$value=date($format,strtotime($value));
					}
					$column=$column[0];
				}else{
					$method='get'.$column;
					$value=$row->$method();					
				}
				
				$tbody.='<td class="'.strtolower($column).'">';

				if($notFirst){
					$val=empty($value)?'':htmlspecialchars($value, ENT_QUOTES);
				}
				else{
					$notFirst=true;
					$val=self::editLink(admin_url("admin.php?page=$class&amp;action=edit"),htmlspecialchars($value, ENT_QUOTES),$row->getId(),$nonce,true);	
				}
				$filteredColumn=Filter::run("htmlhelper-table-row-$id",array($val,$row,$column,$class,$notFirst));
				$tbody.=$filteredColumn;
				$tbody.="</td>\n";
			}
			$customColumns=Filter::run("htmlhelper-table-row-columns-$id",array(array(),$row));
			if($customColumns)
				foreach($customColumns as $column)
					$tbody.=$column;
			//			$tbody.='<td style=\'width:50px;\'>'.self::deleteButton('Delete',$row->getId(),get_site_url().'/'.$class.'/delete',$nonce,true).'</td>';
			$tbody.="</tr>";
		}
		$tbody.="</tbody>\n";
		$ths='';
		$headlines=Filter::run("htmlhelper-table-head-columns-$id",array($headlines));
		foreach($headlines as $column){
			if(is_array($column)){
				if(isset($column['title']))
					$columnName=$column['title'];
				else
					$columnName=$column[0];
				$columnClass=$column[0];
			}else{
				$columnName=$column;
				$columnClass=$column;
			}
			$columnName=htmlspecialchars($columnName, ENT_QUOTES);				
			$ths.="<th class=\"".strtolower($columnClass)."\">$columnName</th>\n";
		}
		$table.="<thead>\n<tr>\n<th class=\"first checkbox\">".self::input('selectAllTop','checkbox','all',false,true)."</th>$ths\n</tr></thead>";
		$table.="<tfoot>\n<tr>\n<th class=\"first checkbox\">".self::input('selectAllBottom','checkbox','all',false,true)."</th>$ths\n</tr></tfoot>";
		$table.=$tbody;
		$table.="
		</table>";
		
		$script="
		jQuery('#selectAllTop').click(function(){
		val=this.checked;
		jQuery('.all').each(function(index) {this.checked=val;});
		jQuery('#selectAllBottom').attr('checked',this.checked);
		});
		jQuery('#selectAllBottom').click(function(){
		val=this.checked;
		jQuery('.all').each(function(index) {this.checked=val;});
		jQuery('#selectAllTop').attr('checked',this.checked);
	});	
		";
		self::registerFooterScript($script);		
		echo $table;
	}

    /**
     * Generate an url that is used for actions for manipulating objects. Url ends with /$class/$type
     * @param string|object $class
     * @param string $type the action that should be performed
     */
    static function ActionPath($class,$type){
		echo action_url($class,$type);
	}

    /**
     * Generate paging links
     * @param string $href
     * @param int $total
     * @param int $currentpage
     * @param int $perpage
     * @param bool $dont_print
     * @param string $next
     * @param string $prev
     * @return string
     */
    static function paging($href,$total,$currentpage,$perpage,$dont_print=false,$next="&raquo;",$prev="&laquo;"){
		$paging='<div class="tablenav"><div class="tablenav-pages">';
		$paging.='<span class="displaying-num">';
		if(strpos($href,'?')!==false)
			$current="&amp;current=";
		else 
			$current="?current=";
		$pages=ceil($total/$perpage);

		$mod=fmod($currentpage,$perpage);
		$startPage=$mod?$currentpage-$mod:$currentpage-$perpage;

		$listPages=($pages)>($startPage+10)?$startPage+10:$pages;

		$start=$total?($perpage*$currentpage-$perpage+1):0;
		$end=($perpage*$currentpage<=$total)?$perpage*$currentpage:$total;
		$paging.="Displaying $start - $end of ".'<span class="total-type-count">'.intval($total).'</span></span>';
		if($pages>$perpage && ($currentpage)>$perpage)
			$paging.=' <a href="'.$href.$current.intval($startPage).'" class="prev page-numbers">'.$prev."</a> ";
		if($pages>1)
			for($page=$startPage+1;$page<=$listPages;$page++){
				if($page!=$currentpage)
					$paging.=self::a($page,$href.$current.intval($page),'page-numbers',true);
				else
					$paging.='<span class="page-numbers current">'.intval($currentpage).'</span>';
			}
		if($pages>$listPages && ($currentpage-1)<$pages)
			$paging.=' <a href="'.$href.$current.intval($page).'" class="next page-numbers">'.$next."</a> ";
		$paging.="</div></div>";
		if($dont_print)
			return $paging;
		echo $paging;	
	}

    /**
     * Print an notification div, has classes ui-corner-all.
     * If error ui-state-error, if not error ui-state-highlight
     * @param string $id
     * @param string $message
     * @param bool $error
     */
    static function notification($id,$message,$error=false){
		if($error)
			echo "<div id=\"$id\" class=\"ui-state-error ui-corner-all\">$message</div>";		
		else
			echo "<div id=\"$id\" class=\"ui-state-highlight ui-corner-all\">$message</div>";
	}

    /**
     * Register script to be printed in footer
     * @param $script
     */
    static function registerFooterScript($script){
		self::$scripts[]=$script;
	}

    /**
     * Returns all scripts that should be printed in footer
     * @return array
     */
    static function getFooterScripts(){
		return self::$scripts;
	}
}