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
	static function getQueryString(){
		if(defined('TESTING')){
			global $testquery;
			return $testquery;	
		}else{
			global $wp_query;
			if(isset($wp_query) && !empty($wp_query->query_vars))
				return $wp_query->query_vars;
			else 
				return $_GET;
		}
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
		if(function_exists('wp_redirect'))
			wp_redirect($url.$data);
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