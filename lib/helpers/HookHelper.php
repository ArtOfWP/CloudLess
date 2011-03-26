<?php
class HookHelper{
	private static $Hooks=array();
	static function register($hook,$callback,$priority=100){
		if(!isset(self::$Hooks[$hook]['handler'])){
	    	if(is_array($callback))
	    		$id=hash('md5',get_class($callback[0]).$callback[1].$priority);
	    	else
	    		$id=hash('md5',$callback.$priority);
			self::$Hooks[$hook][$priority][$id]=$callback;
		}else{		
			$handler=self::$Hooks[$hook]['handler'];
			call_user_func($handler,$hook,$callback,$priority);
		}
	}
	static function registerCustomHandler($hook,$callback){
		self::$ViewSections[$hook]['handler']=$callback;
	}
	static function run($hook,$params=array(),$isArray=false){
		$priorities=array_key_exists_v($hook,self::$Hooks);
		if($priorities)
			ksort($priorities);
		if(is_array($priorities)){
			if(!$isArray && !is_array($params))
				$params=array($params);
			foreach($priorities as $priority => $functions){
				foreach($functions as $function){
					call_user_func_array($function,$params);
				}
			}
		}
	}
	static function isRegistered($hook){
		return array_key_exists($hook,self::$Hooks);
	}
	static function hasCustomHandler($hook){
		return isset(self::$Hooks[$hook]['handler']);
	}
}