<?php
namespace CLMVC\Views;
/**
 * Class Shortcode
 */
class Shortcode{
	private static $shortCodeCallback;
	private static $shortCodes=array();

    /**
     * Register shortcode handler
     * @param $callback
     */
    static function registerHandler($callback){
		self::$shortCodeCallback=$callback;
	}

    /**
     * Register a shortcode
     * @param $id
     * @param $callback
     */
    static function register($id,$callback){
		if(self::$shortCodeCallback)
			call_user_func(self::$shortCodeCallback,$id,$callback);
		else
			self::$shortCodes[$id]=$callback;
	}
}