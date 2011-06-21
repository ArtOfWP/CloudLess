<?php
class Shortcode{
	private static $shortCodeCallback;
	private static $shortCodes=array();
	static function registerHandler($callback){
		self::$shortCodeCallback=$callback;
	}
	static function register($id,$callback){
		if(self::$shortCodeCallback)
			call_user_func(self::$shortCodeCallback,$id,$callback);
		else
			self::$shortCodes[$id]=$callback;
	}
}