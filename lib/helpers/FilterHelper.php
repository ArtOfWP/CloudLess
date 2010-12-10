<?php
class FilterHelper{
	private static $FilterSections;
	static function registerFilter($filter,$callback){
		if(!isset(self::$FilterSections[$filter]['handler'])){
	    	if(!isset(self::$FilterSections))
	    		self::$FilterSections=array();
			self::$FilterSections[$filter][]=$callback;
		}else{
			$handler=self::$FilterSections[$filter]['handler'];
			call_user_func($handler,$filter,$callback);
		}
	}
	static function registerCustomHandler($filter,$callback){
		self::$FilterSections[$filter]['handler']=$callback;
	}
	static function runFilter($filter,$params=array()){
		$functions=self::$FilterSections[$filter];
		if(is_array($functions)){
			if(!is_array($params))
				$params=array($params);
			foreach($functions as $function){
				$value=call_user_func_array($function,$params);
				$params[0]=$value;
			}
		}
		return $value;
	}
	static function isRegistered($filter){
		return array_key_exists($filter,self::$FilterSections);
	}
	static function hasCustomHandler($filter){
		return isset(self::$FilterSections[$filter]['handler']);
	}
}