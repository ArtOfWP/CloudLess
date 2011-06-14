<?php
class View{
	private static $ViewSections=array();
	static function register($section,$callback,$priority=100){
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
	static function registerHandler($section,$callback){
		self::$ViewSections[$section]['handler']=$callback;
	}
	static function render($section,$params=array(),$isArray=false){
		$priorities=array_key_exists_v($section,self::$ViewSections);
		if($priorities)
			ksort($priorities);		
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
	static function isRegistered($section){
		return array_key_exists($section,self::$ViewSections);
	}
	static function hasCustomHandler($section){
		return isset(self::$ViewSections[$section]['handler']);
	}	
}
/*
 * deprecated since 11.6
 */
class ViewHelper{
	static function registerViewSection($section,$callback,$priority=100){
		View::register($section,$callback,$priority);
	}
	static function registerViewSectionHandler($section,$callback){
		View::registerHandler($section,$callback);
	}
	static function renderSection($section,$params=array(),$isArray=false){
		View::render($section,$params,$isArray);
	}
	static function isRegistered($section){
		return View::isRegistered($section);
	}
	static function hasCustomHandler($section){
		return View::hasCustomHandler($section);
	}
	
}