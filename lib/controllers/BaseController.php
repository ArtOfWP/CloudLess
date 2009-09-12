<?php
global $viewcontent;
class BaseController{
	protected $controller;
	private $render=true;
	public $bag=array();
	public $viewcontent;
	public $values=array();
	private function initiate(){
		$item= get_class($this);
		$this->controller =str_replace('Controller','',$item);
		global $wp_query;
		$this->values=$wp_query->query_vars;
		unset($this->values[CONTROLLERKEY]);
		unset($this->values[ACTIONKEY]);		
	}
	function BaseController($automatic=true){
		$this->initiate();
		Debug::Message('Loaded '.$this->controller.' extends Basecontroller');
		if($automatic){
			Debug::Message('Executing automatic action');
			$action=array_key_exists_v(ACTIONKEY,$_GET);
			if(!isset($action) || empty($action))
				$action='index';
			$this->$action();
			Debug::Message('Executed action: '.$action);			
			if($this->render){
				$view=$this->findView($this->controller,$action);
				ob_start();
				if($view){
					require($view);
					$this->viewcontent=ob_get_contents();
				}else
					$this->viewcontent='Could not find view: '.$view;
				ob_end_clean();
			}
			global $viewcontent;
			$viewcontent=$this->viewcontent;
		}
	}
	function Render($controller,$action){
		$view=$this->findView($this->controller,$action);		
		ob_start();
		if($view){
			include($view);
			$this->viewcontent=ob_get_contents();
		}else
			$this->viewcontent='Could not find view: '.$view;
		$this->render=false;
		ob_end_clean();
		global $viewcontent;
		$viewcontent=$this->viewcontent;
	}
	function Notfound(){
		ob_start();
		include(VIEWS.$controller.'/'.$action.'.php');
		$this->render=false;
		$this->viewcontent=ob_get_contents();
		ob_end_clean();
		global $viewcontent;
		$viewcontent=$this->viewcontent;		
	}
	static function ViewContents(){
		global $viewcontent;
		return $viewcontent;
	}
	private function findView($controller,$action){
		global $apps;
		$found=false;
		$total=sizeof($apps);
		$i=0;
		while(!$found && $i<$total){
			$app=$apps[$i];
			Debug::Value('Searching',$app.VIEWS.$controller.'/'.$action.'.php');
			if(file_exists($app.VIEWS.$controller.'/'.$action.'.php'))
				$found = $app.VIEWS.$controller.'/'.$action.'.php';
			$i++;
		}
		return $found;
	}
}
?>