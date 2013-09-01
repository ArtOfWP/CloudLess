<?php
namespace CLMVC\Events;

/**
 * Class Hook
 */
class Hook{
	public static $Hooks=array();

    /**
     * Register a hook
     * @param string $hook
     * @param array|string $callback
     * @param int $priority where in list it should
     */
    static function register($hook,$callback,$priority=100) {
		if (!isset(self::$Hooks[$hook]['handler'])) {
            if (is_array($callback)) {
                            if (is_string($callback[0]))
                                $id = hash('md5', $callback[0] . $callback[1] . $priority);
                            else
                                $id = hash('md5', get_class($callback[0]) . $callback[1] . $priority);
                        }else
	    		$id=hash('md5',$callback.$priority);
			self::$Hooks[$hook][$priority][$id]=$callback;
		} else {
			$handler=self::$Hooks[$hook]['handler'];
			call_user_func($handler,$hook,$callback,$priority);
		}
	}

    /**
     * Register an custom handler for hook
     * @param $hook
     * @param $callback
     */
    static function registerHandler($hook,$callback) {
		self::$Hooks[$hook]['handler']=$callback;
	}

    /**
     * Run the hook
     * @param string $hook
     * @param array $params
     * @param bool $isArray
     */
    static function run($hook,$params=array(),$isArray=false) {
		$priorities=array_key_exists_v($hook,self::$Hooks);
		if($priorities)
			ksort($priorities);
		if(is_array($priorities)){
			if(!$isArray && !is_array($params))
				$params=array($params);
			foreach($priorities as $functions){
				foreach($functions as $function){
                    if(!is_callable($function)){
                                            if(is_array($function))
                                                if(is_string($function[0]))
                                                    $message=implode('::',$function);
                                                else
                                                    $message=get_class($function[0]).'->'.$function[1];
                                            else
                                                $message=$function;
                                            trigger_error('Hook cannot call '.$message.' it does not exist.',E_USER_WARNING);
                        continue;
                                        }

					call_user_func_array($function,$params);
				}
			}
		}
	}

    /**
     * Check if hook is registered
     * @param string $hook
     * @return bool
     */
    static function isRegistered($hook) {
		return array_key_exists($hook,self::$Hooks);
	}

    /**
     * Check if hook has a custom handler
     * @param string $hook
     * @return bool
     */
    static function hasCustomHandler($hook) {
		return isset(self::$Hooks[$hook]['handler']);
	}
}