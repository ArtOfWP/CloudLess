<?php
class ViewHelper{
	private static $ViewSections;
	static function registerViewSection($section,$callback){
		if(!isset(self::$ViewSections[$section]['handler'])){
	    	if(!isset(self::$ViewSections))
	    		self::$ViewSections=array();
			self::$ViewSections[$section][]=$callback;
		}else{
			$handler=self::$ViewSections[$section]['handler'];
			call_user_func($handler,$section,$callback);
		}
	}
	static function registerViewSectionHandler($section,$callback){
		self::$ViewSections[$section]['handler']=$callback;
	}
	static function renderSection($section,$params=array(),$isArray=false){
		$functions=self::$ViewSections[$section];
		if(is_array($functions)){
			ob_start();
			if(!$isArray && !is_array($params))
				$params=array($params);
			foreach($functions as $function)
				call_user_func_array($function,$params);
			$sections=ob_get_contents();
			ob_end_clean();
			echo $sections;
		}
	}
	static function isRegistered($filter){
		return array_key_exists($filter,self::$ViewSections);
	}
	static function hasCustomHandler($section){
		return isset(self::$ViewSections[$section]['handler']);
	}
}