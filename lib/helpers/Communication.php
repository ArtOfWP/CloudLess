<?php
define ('PUT','put');
define ('GET','get');
define ('POST','post');
define ('DELETE','delete');
class Communication{
	static function createUrlAndQuery($url,$array){
		
	}
	static function cleanUrl($dirty_url){
		list($clean_url)= explode('?',$dirty_url);
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
	static function getFormValues($keys){
		$values = array();	
		$values=array_intersect_key($_POST,$keys);
		return $values;
	}
	static function getUpload($keys){
		$files=array_intersect_key($_FILES,$keys);
		return $files;
	}
	static function getReferer(){
		return $_SERVER['HTTP_REFERER'];
	}
		
	static function redirectTo($url,$data=false){
		if(function_exists('wp_redirect'))
			wp_redirect($url);
		else{
			header( 'Location: '.$url );
			exit;
		}
	}
	static function useRedirect(){
		return array_key_exists_v('_redirect',$_POST);
	}
}
?>