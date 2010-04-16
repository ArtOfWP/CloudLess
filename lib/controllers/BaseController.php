<?php
global $viewcontent;
class BaseController{
	protected $controller;
	protected $action;
	protected $filter;
	protected $title;
	protected $redirect;
	protected $viewpath;
	private $render=true;
	public $bag=array();
	public $viewcontent;
	public $values=array();
	private function initiate(){
		$item= get_class($this);
		$this->controller =str_replace('Controller','',$item);
		$this->values=Communication::getQueryString();
		$this->values+=Communication::getFormValues();
		$this->action=$this->values[ACTIONKEY];
		unset($this->values[CONTROLLERKEY]);
		unset($this->values[ACTIONKEY]);		
	}
	function BaseController($automatic=true,$viewpath=false){
		$this->initiate();
		$this->viewpath=$viewpath;
		Debug::Message('Loaded '.$this->controller.' extends Basecontroller');
		if($this->filter)
			$this->filter->perform($this,false);
		if($automatic)
			$this->automaticRender();
	}
	protected function automaticRender(){
			Debug::Message('Executing automatic action');
			$action=array_key_exists_v(ACTIONKEY,Communication::getQueryString());
			if(!isset($action) || empty($action))
				$action='index';
			Debug::Message('PreExecuted action: '.$action);				
			$this->$action();
			Debug::Message('Executed action: '.$action);			
			if($this->render){
				$view=$this->findView($this->controller,$action);
				ob_start();
				if($view){
					extract($this->bag, EXTR_REFS);
					require($view);
					$this->viewcontent=ob_get_contents();
				}else
					$this->viewcontent='Could not find view: '.$action;
				ob_end_clean();
				$this->render=false;
			}
			global $viewcontent;
			$viewcontent=$this->viewcontent;
	}
	function RenderToAction($action){
		$view=$this->findView($this->controller,$action);		
		ob_start();
		if($view){
			extract($this->bag, EXTR_REFS);			
			include($view);
			$this->viewcontent=ob_get_contents();
		}else
			$this->viewcontent='Could not find view: '.$action.' '.$view;
		$this->render=false;
		ob_end_clean();
		global $viewcontent;
		$viewcontent=$this->viewcontent;
		global $aoisoratitle;
		$aoisoratitle=$this->title;
		Debug::Value('TITLE',$aoisoratitle);
	}
	function Render($controller,$action){
		$view=$this->findView($controller,$action);		
		ob_start();
		if($view){
			extract($this->bag, EXTR_REFS);		
			include($view);
			$this->viewcontent=ob_get_contents();
		}else
			$this->viewcontent='Could not find view: '.$view;
		$this->render=false;
		ob_end_clean();
		global $viewcontent;
		$viewcontent=$this->viewcontent;
		global $aoisoratitle;
		$aoisoratitle=$this->title;		
	}
	function RenderFile($filepath){
		ob_start();
		if(file_exists($filepath)){
			extract($this->bag, EXTR_REFS);		
			include($filepath);
			$this->viewcontent=ob_get_contents();
		}else
			$this->viewcontent='Could not find view: '.$$filepath;
		$this->render=false;
		ob_end_clean();
		global $viewcontent;
		$viewcontent=$this->viewcontent;		
	}
	function RenderText($text){
		$this->render=false;
		global $viewcontent;
		$viewcontent=$text;
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
		if($this->viewpath){
			return $this->viewpath.$controller.'/'.$action.'.php';
		}		
		$apps=AoiSoraSettings::getApplications();
		$total=sizeof($apps);
		Debug::Message('Nbr of apps: '.$total);
		$lcontroller=strtolower($controller);
		$laction=strtolower($action);		
		foreach($apps as $app){
			$path=$app['path'];
			Debug::Value('Path',$path);
			Debug::Value('Searching',$path.VIEWS.$controller.'/'.$action.'.php');
			if(file_exists($path.VIEWS.$controller.'/'.$action.'.php'))
				return $path.VIEWS.$controller.'/'.$action.'.php';	
			if(file_exists($path.VIEWS.$lcontroller.'/'.$laction.'.php'))
					return $path.VIEWS.$lcontroller.'/'.$laction.'.php';
		}

		return false;
	}
}
?>