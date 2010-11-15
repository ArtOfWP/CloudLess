<?php
define ('PUT','put');
define ('GET','get');
define ('POST','post');
define ('DELETE','delete');
class Communication{
	static function createUrlAndQuery($url,$array){
		
	}
	static function cleanUrl($dirty_url){
		list($clean_url)= explode('?',htmlspecialchars(strip_tags($dirty_url),ENT_NOQUOTES));
		return $clean_url;
	}
	static function getMethod(){
		$tempMethod=$_SERVER['REQUEST_METHOD'];
		if(strcasecmp($tempMethod,PUT)==0)
			return PUT;
		else if(strcasecmp($tempMethod,POST)==0){
			if(isset($_POST['_method'])){
			if(strcasecmp($_POST['_method'],PUT)==0)
				return PUT;
			if(strcasecmp($_POST['_method'],DELETE)==0)
				return DELETE;
			}
			return POST;
		}else if(strcasecmp($tempMethod,GET)==0)
			return GET;
		else if(strcasecmp($tempMethod,DELETE)==0)
			return DELETE;
	}
	static function QueryStringEquals($key,$value){
		return array_key_has_value($key,$value,self::getQueryString());
	}
	static function getQueryString($key=false){
		if(defined('TESTING')){
			global $testquery;
			$qs=$testquery;	
		}else{
			global $wp_query;
			if(isset($wp_query) && !empty($wp_query->query_vars))
				$qs= $wp_query->query_vars;
			else
				$qs= $_GET;
		}
		if($key!==false)
			$qs=array_key_exists_v($key,$qs);
		return $qs;
	}
	static function getFormValues($keys=false){
		if($keys){
		$values = array();	
		$values=array_intersect_key($_POST,$keys);
		return $values;
		}
		return $_POST;
	}
	static function getUpload($keys){
		$files=array_intersect_key($_FILES,$keys);
		return $files;
	}
	static function getReferer(){
		if(function_exists('wp_get_referer'))
			return wp_get_referer();
		else
			return $_SERVER['HTTP_REFERER'];
	}
		
	static function redirectTo($url,$data=false){
			$data=ltrim($data,"&");
		if(is_array($data))
			$data=http_build_query($data);
		if(strpos($url,'?')===false)
			$redirect=$url."?".$data;
		else
			$redirect = $url."&".$data;
		if(function_exists('wp_redirect'))
			wp_redirect($redirect);
		else{
			header( 'Location: '.$redirect );
			exit;
		}
	}
	static function useRedirect(){
		return array_key_exists_v('_redirect',$_POST);
	}
	static function loadFromPost($class,$uploadSubFolder=false,$width=100,$height=100){
		if(is_string($class))
			$crudItem= new $class();
		else
			$crudItem=$class;
		$folder='';
		if($uploadSubFolder)
			$folder=stripslashes($this->uploadSubFolder).'/';
			
		$properties = ObjectUtility::getPropertiesAndValues($crudItem);
		Debug::Message('LoadFromPost');
		$arrvalues=self::getFormValues();
		Debug::Value('Post',$arrvalues);

		//		Debug::Value('Uploaded',Communication::getUpload($properties));
		$values=self::getFormValues($properties);
		$values=array_map('stripslashes',$values);
		Debug::Value('Loaded properties/values for '.get_class($crudItem),$values);		
		$arrprop=ObjectUtility::getArrayPropertiesAndValues($crudItem);
		$lists=array_search_key('_list',$arrvalues);
		Debug::Value('Loaded listvalues from post',$lists);
		$uploads=self::getUpload($properties);
		foreach($uploads as $property => $upload){
			Debug::Message('CHECKING UPLOADS');
			if(strlen($upload["name"])>0){
				Debug::Message('FOUND UPLOAD');
				$path=UPLOADS_DIR.$folder.$upload["name"];
				move_uploaded_file($upload["tmp_name"],$path);
				$values[$property]=$upload["name"];
				$image = new Resize_Image;
				$image->new_width = $width;
				$image->new_height = $height;
				$image->image_to_resize = $path;
				$image->ratio = true;
				$image->new_image_name = preg_replace('/\.[^.]*$/', '', $upload["name"]);
				$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
				$process = $image->resize();		
			}else{
				if(!isset($this->values[$property.'_hasimage'])){
					$values[$property]='';					
				}
				else{
					if(strpos($this->values[$property.'_hasimage'],'ttp')==1){			
						$url = $this->values[$property.'_hasimage'];
						$name=str_replace(' ','-',urldecode(basename($url)));
						$path=UPLOADS_DIR.$folder.$name;					
						$values[$property]=$name;
						
						Http::save_image($url,$path);					
						$image = new Resize_Image;
						$image->new_width = $width;
						$image->new_height = $height;
						$image->image_to_resize = $path; // Full Path to the file
						$image->ratio = true; // Keep Aspect Ratio?
						// Name of the new image (optional) - If it's not set a new will be added automatically
						$image->new_image_name = preg_replace('/\.[^.]*$/', '', $name);
						// Path where the new image should be saved. If it's not set the script will output the image without saving it 
						$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
						$process = $image->resize();
					}
				}
			}
		}
		ObjectUtility::setProperties($crudItem,$values);
		foreach($lists as $method => $value){
			Debug::Value($method,$value);
			$settings=ObjectUtility::getCommentDecoration($crudItem,str_ireplace("_list","",$method).'List');
			$dbrelation=array_key_exists_v('dbrelation',$settings);
			Debug::Value($method,$dbrelation);
			$field=array_key_exists_v('field',$settings);
			$objects=array();	
			if($field=='text'){
				$values=explode(',',$value);
				if(sizeof($values)==0)
					continue;
				foreach($values as $value){
					if($dbrelation && $field=='text'){
						$object= new $dbrelation;
						$object->setName(trim($value));
						$object->save();
						$objects[]=$object;
					}
				}
			}
			else if($dbrelation){
					$object=Repo::getById($dbrelation,$value);
					$objects[]=$object;					
				}
				
			ObjectUtility::addToArray($crudItem,str_ireplace("_list","",$method),$objects);
		}
		return $crudItem;		
	}
}