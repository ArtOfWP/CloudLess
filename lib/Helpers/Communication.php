<?php
namespace CLMVC\Helpers;
/**
 * Class Communication
 */
class Communication{
    /**
     * Remove query string and other stuff from an url
     * @param $dirty_url
     * @return mixed
     */
    static function cleanUrl($dirty_url){
		list($clean_url)= explode('?',htmlspecialchars(strip_tags($dirty_url),ENT_NOQUOTES));
		return $clean_url;
	}

    /**
     * Retrieves the request method. Checks for post key '_method'
     * @return string
     */
    static function getMethod(){
		$tempMethod=$_SERVER['REQUEST_METHOD'];
        if(strcasecmp($tempMethod,'put')==0)
			return 'put';
		else if(strcasecmp($tempMethod,'post')==0){
			if(isset($_POST['_method'])){
			    if(strcasecmp($_POST['_method'],'put')==0)
				    return 'put';
			    if(strcasecmp($_POST['_method'],'delete')==0)
				    return 'delete';
			}
			return 'post';
		}else if(strcasecmp($tempMethod,'get')==0)
			return 'get';
		else if(strcasecmp($tempMethod,'delete')==0)
			return 'delete';
	}

    /**
     * Checks if query string has key value pair
     * @param string $key
     * @param string|int $value
     * @return bool
     */
    static function QueryStringEquals($key, $value){
		return array_key_has_value($key,$value,self::getQueryString());
	}

    /**
     * Returns the query string
     * @param string $key
     * @return bool|mixed
     */
    static function getQueryString($key=null, $default = null){
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
		if($key)
			$qs=array_key_exists_v($key,$qs, $default);
		return $qs;
	}

    /**
     * Returns form values matching keys
     * @param array $keys
     * @return array
     */
    static function getFormValues($keys=array()){
        if(defined('TESTING')){
        	global $testpost;
            $qs=$testpost;
        }else{
            $qs= $_POST;
        }
		if(is_array($keys)){
    		$values=array_intersect_key($qs,$keys);
	    	return $values;
		} elseif(is_string($keys)) {
            $data = array();
            foreach($qs as $key => $value) {
                if (substr($key,0, strlen($keys)) === $keys) {
                    $data[substr($key,strlen($keys))] = $value;
                }
            }
            return $data;
        }
		return $qs;
	}

    /**
     * Get upload contents from $_FILES matching keys
     * @param $keys
     * @return array
     */
    static function getUpload($keys){
		$files=array_intersect_key($_FILES,$keys);
		return $files;
	}

    /**
     * Retrieve the referrer
     * @return mixed
     */
    static function getReferer(){
		if(function_exists('wp_get_referer'))
			return wp_get_referer();
		else
			return $_SERVER['HTTP_REFERER'];
	}

    /**
     * Redirect to url with data
     * @param string $url
     * @param string|array|bool $data
     */
    static function redirectTo($url,$data=null){
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

    /**
     * Check if redirect should be used and if so return the redirect url
     * @return bool|mixed
     */
    static function useRedirect(){
		return array_key_exists_v('_redirect',$_POST);
	}
	//TODO work in progress
    /**
     * Loads an object with properties matching the $_POST data
     * @param $class
     * @param bool $uploadSubFolder
     * @param $thumbnails
     * @param int $width
     * @param int $height
     * @return mixed
     */
    static function loadFromPost($class,$uploadSubFolder=false,$thumbnails,$width=100,$height=100){
		if(is_string($class))
			$crudItem= new $class();
		else
			$crudItem=$class;
		$folder='';
		if($uploadSubFolder)
			$folder=stripslashes($uploadSubFolder).'/';
			
		$properties = ObjectUtility::getPropertiesAndValues($crudItem);
		Debug::Message('LoadFromPost');
		//		Debug::Value('Uploaded',Communication::getUpload($properties));
		$propertyFormValues=Communication::getFormValues($properties);
		$propertyFormValues=array_map('stripslashes',$propertyFormValues);
		Debug::Value('Loaded properties/values for '.get_class($crudItem),$propertyFormValues);		
		$arrprop=ObjectUtility::getArrayPropertiesAndValues($crudItem);
		$lists=array_search_key('_list',$propertyFormValues);
		Debug::Value('Loaded listvalues from post',$lists);
		$uploads=Communication::getUpload($properties);
		foreach($uploads as $property => $upload){
			Debug::Message('CHECKING UPLOADS');
			if(strlen($upload["name"])>0){
				Debug::Message('FOUND UPLOAD');
				if(isset($thumbnails[$property]) && $thumbnails[$property]=='thumb')
					$path=UPLOADS_DIR.$folder.'thumbs/'.$upload["name"];
				else
					$path=UPLOADS_DIR.$folder.$upload["name"];
				
				$path=UPLOADS_DIR.$folder.$upload["name"];
				move_uploaded_file($upload["tmp_name"],$path);
				chmod($path, octdec(644));				
				$propertyFormValues[$property]=$upload["name"];
				if(isset($thumbnails[$property]) && $thumbnails[$property][0]=='create'){
					$image = new Resize_Image;
					$image->new_width = $width;
					$image->new_height = $height;
					$image->image_to_resize = $path;
					$image->ratio = true;
					$image->new_image_name = preg_replace('/\.[^.]*$/', '', $upload["name"]);
					$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
					$propertyFormValues[$thumbnails[$property][1]]='thumbs/'.$upload["name"];
					$process = $image->resize();
					chmod($process['new_file_path'], octdec(644));
				}
			}else{
				Debug::Message('No upload '.$property);
				if(!isset($formValues[$property.'_hasimage']) && empty($propertyFormValues[$property])){
					$propertyFormValues[$property]='';
				}
				else{
					if(strpos($formValues[$property.'_hasimage'],'ttp')==1){
						Debug::Message('HAS IMAGE LINK '.$property);
						$url = $formValues[$property.'_hasimage'];
						$name=str_replace(' ','-',urldecode(basename($url)));
						if(isset($thumbnails[$property]) && $thumbnails[$property]=='thumb')
							$path=UPLOADS_DIR.$folder.'thumbs/'.$name;
						else
							$path=UPLOADS_DIR.$folder.$name;
						$propertyFormValues[$property]=$name;
						
						Http::save_image($url,$path);
						if(isset($thumbnails[$property]) && $thumbnails[$property][0]=='create'){
							Debug::Message('CREATE THUMBNAIL');
							$image = new Resize_Image;
							$image->new_width = $width;
							$image->new_height = $height;
							$image->image_to_resize = $path; // Full Path to the file
							$image->ratio = true; // Keep Aspect Ratio?
							$image->new_image_name = preg_replace('/\.[^.]*$/', '', $name);
							$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
							$propertyFormValues[$thumbnails[$property][1]]='thumbs/'.$name;
							$process = $image->resize();
							chmod($process['new_file_path'], octdec(644));							
						}
					}else{
						Debug::Message('HAS IMAGE '.$property);
						Debug::Value('Thumbnails',$thumbnails);
						if(isset($thumbnails[$property]) && $thumbnails[$property][0]=='create'){
							Debug::Message('CREATE THUMBNAIL');
							$url = $formValues[$property.'_hasimage'];
							$name=str_replace(' ','-',urldecode(basename($url)));							
							$path=UPLOADS_DIR.$folder.$name;
							$image = new Resize_Image;
							$image->new_width = $width;
							$image->new_height = $height;
							$image->image_to_resize = $path; // Full Path to the file
							$image->ratio = true; // Keep Aspect Ratio?
							// Name of the new image (optional) - If it's not set a new will be added automatically
							$image->new_image_name = preg_replace('/\.[^.]*$/', '', $name);
							// Path where the new image should be saved. If it's not set the script will output the image without saving it 
							$image->save_folder = UPLOADS_DIR.$folder.'thumbs/';
							$propertyFormValues[$thumbnails[$property][1]]='thumbs/'.$name;
							$process = $image->resize();
							chmod($process['new_file_path'], octdec(644));							
						}						
					}
				}
			} 
		}
		ObjectUtility::setProperties($crudItem,$propertyFormValues);
		foreach($lists as $method => $value){
			Debug::Value($method,$value);
			$settings=ObjectUtility::getCommentDecoration($crudItem,str_ireplace("_list","",$method).'List');
			$dbrelation=array_key_exists_v('dbrelation',$settings);
			Debug::Value($method,$dbrelation);
			$field=array_key_exists_v('field',$settings);
			$objects=array();	
			if($field=='text'){
				$propertyFormValues=explode(',',trim($value," ,."));
				if(sizeof($propertyFormValues)==0)
					continue;
				foreach($propertyFormValues as $value){
					if($dbrelation && $field=='text'){
						$object= new $dbrelation;
						$object->setName(trim($value));
						$object->save();
						$objects[]=$object;
					}
				}
			}
			else if($dbrelation){
					if(is_array($value))
						foreach($value as $val){
							$object=Repo::getById($dbrelation,$val);
							$objects[]=$object;
						}
					else{	
						$object=Repo::getById($dbrelation,$value);
						$objects[]=$object;
					}
				}
				
			ObjectUtility::addToArray($crudItem,str_ireplace("_list","",$method),$objects);
		}
		return $crudItem;		
	}

    /**
     * See if a query string is in array
     * @param $string
     * @param $array
     * @return bool
     */
    public static function queryStringIn($string, $array) {
        return in_array(self::getQueryString($string), $array);
    }
}
