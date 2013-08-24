<?php

/**
 * Class Route
 */
class Route{
    /**
     * @return bool
     */
    static function reroute(){
		$controller=array_key_exists_v(CONTROLLERKEY,Communication::getQueryString());
		$action = array_key_exists_v(ACTIONKEY,Communication::getQueryString());/**/
		$success=true;
		if($action && $controller)
			$success=Route::rerouteToAction($controller,$action);
		else if($controller)
			$success=Route::rerouteToController($controller);
		else
			$success=false;
		Debug::Message('Rerouting');
		Debug::Value('Success',$success);
		return $success;
	}

    /**
     * Retroute request to controller
     * @param string $controller
     * @return bool
     */
    static function rerouteToController($controller){
		$controller=$controller.'Controller';
		if(class_exists($controller)){
			$ctrl=new $controller();
			$ctrl->init();
		}else{
			return false;
		}
		return true;
	}

    /**
     * Reroute request to action on controller
     * @param string $controller
     * @param string $action
     * @return bool
     */
    static function rerouteToAction($controller,$action){
		$controller=$controller.'Controller';
		Debug::Value('RerouteToAction',$controller.'->'.$action);
		if(class_exists($controller)){
			$ctrl=new $controller(false);
			$ctrl->init();
			try{
				$ctrl->executeAction($action);
			}catch(RuntimeException $ex){
				return false;
			}
		}else
			return false;
		return true;
	}
}