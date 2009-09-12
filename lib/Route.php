<?php
class Route{
	static function reroute(){
		$controller=array_key_exists_v(CONTROLLERKEY,$_GET);
		$action = array_key_exists_v(ACTIONKEY,$_GET);

		$success=true;
		if($action && $controller)
			$success=Route::rerouteToAction($controller,$action);
		else if($controller)
			$success=Route::rerouteToController($controller);
		else
			$success=false;
		Debug::Value('Route result',$success?'success':'failure');
			
		return $success;
	}
	static function rerouteToController($controller){
		$controller=$controller.'Controller';
		Debug::Value('Controller',$controller);
		if(class_exists($controller)){
			$ctrl=new $controller;
		}else{
			die('Could not find controller: '.$controller);			
			return false;
		}
		return true;
	}
	static function rerouteToAction($controller,$action){
		$controller=$controller.'Controller';
		Debug::Value('Controller',$controller);
		Debug::Value('Action',$action);
		if(class_exists($controller)){
			if(method_exists($controller,$action)){
				$ctrl=new $controller(false);
				$ctrl->$action();
			}else
				return false;
		}else
			return false;
		return true;
	}
}
?>