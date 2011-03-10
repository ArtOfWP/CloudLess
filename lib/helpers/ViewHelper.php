<?php
class ViewHelper{
	private static $ViewSections=array();
	static function registerViewSection($section,$callback){
		if(!isset(self::$ViewSections[$section]['handler'])){
	    	if(!isset(self::$ViewSections))
	    		self::$ViewSections=array();
	    	if(is_array($callback))
	    		$id=hash('md5',get_class($callback[0]).$callback[1].$priority);
	    	else
	    		$id=hash('md5',$callback.$priority);		    		
			self::$ViewSections[$section][$priority][$id]=$callback;
		}else{
			$handler=self::$ViewSections[$section]['handler'];
			call_user_func($handler,$section,$callback);
		}
	}
	static function registerViewSectionHandler($section,$callback){
		self::$ViewSections[$section]['handler']=$callback;
	}
	static function renderSection($section,$params=array(),$isArray=false){
		$functions=array_key_exists_v($section,self::$ViewSections);
		if(is_array($priorities)){
			ob_start();
			if(!$isArray && !is_array($params))
				$params=array($params);
			foreach($priorities as $priority => $functions)				
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