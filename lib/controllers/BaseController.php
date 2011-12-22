<?php
global $viewcontent;
if(class_exists('BaseController'))
	return;

class BaseController{
    private static $currentAction;
	protected $controller;
	protected $action;
	protected $defaultAction='index';
	protected $filter;
	protected $redirect;
	protected $viewpath;
	public $render=true;
	public $bag=array();
	public $viewcontent;
	public $values=array();
	private $automatic;
    private static $currentController;

    private function initiate(){
		Debug::message('BaseController initiate');		
		//TODO deprecated since 11.6
		if(method_exists($this,'on_controller_preinit'))	
			$this->on_controller_preinit();
		if(method_exists($this,'onControllerPreInit'))	
			$this->onControllerPreInit();			
			
		$item= get_class($this);
		$this->controller =str_replace('Controller','',$item);
		$this->values=Communication::getQueryString();
		$this->values+=Communication::getFormValues();
		$this->action=array_key_exists_v(ACTIONKEY,$this->values);
		if(!$this->action)
			$this->action=$this->defaultAction;
		unset($this->values[CONTROLLERKEY]);
		unset($this->values[ACTIONKEY]);
		//TODO deprecated since 11.6
		if(method_exists($this,'on_controller_init'))	
			$this->on_controller_init();
		if(method_exists($this,'onControllerInit'))	
			$this->onControllerInit();			
	}
	public function init(){
		Debug::message('BaseController init');
		$this->initiate();
		if($this->filter)
			if(!$this->filter->perform($this,$this->values))
				die("Action could not be performed.");
		if($this->automatic)
			$this->automaticRender();
	}
	public function __construct($automatic=true,$viewpath=false){
		$this->automatic=$automatic;		
		$this->viewpath=$viewpath;
		Debug::Message('Loaded '.$this->controller.' extends Basecontroller');
	}
	protected function automaticRender(){
		Debug::Message('Executing automatic action');
		$action=array_key_exists_v(ACTIONKEY,Communication::getQueryString());
		if(!isset($action) || empty($action))
			if($this->action)
				$action=$this->action;
			else
				$action='index';
		Debug::Message('PreExecuted action: '.$action);
		try{
			$this->executeAction($action);
		}catch(RuntimeException $ex){
			$this->viewcontent='Could not find action: '.$action;
			$this->render=false;
		}
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
	public function executeAction($action){
		if(method_exists($this,$action)){
			Debug::Message('Executed action: '.$action);
			$reflection = new ReflectionMethod($this, $action);
			if (!$reflection->isPublic())
				throw new RuntimeException("The action you tried to execute is not public.");
            self::$currentAction=$action;
			Hook::run($this->controller.'-pre'.ucfirst($action),$this);
			$this->$action();
			Hook::run($this->controller.'-post'.ucfirst($action),$this);
			if($this->render){
				$this->RenderToAction($action);
			}
		}else
			throw new RuntimeException("The action you tried to execute does not exist.");
	}
	protected function RenderToAction($action){
		Debug::Message('RenderToAction: '.$action);
		$this->Render($this->controller,$action);
	}
	protected function Render($controller,$action){
		Debug::Message('Render: '.$controller,' ',$action);		
		$view=$this->findView($controller,$action);
		if($view){
			extract($this->getBag(), EXTR_REFS);
			$section=View::generate($controller.'-render-pre'.ucfirst($action),$this);
			ob_start();
			include($view);
			$this->viewcontent=$section.ob_get_contents();
			ob_end_clean();
			$section=View::render($controller.'-render-post'.ucfirst($action),$this);
			$this->viewcontent.=$section;
		}else
			$this->viewcontent='Could not find view: '.$view;
		$this->render=false;
		
		global $viewcontent;
		$viewcontent=$this->viewcontent;
	}
	private function getBag(){
		return Filter::run($this->controller.'-bag',array($this->bag,$this->controller,$this->action,$this->values));
	}
	protected function RenderFile($filepath){
		Debug::Message('RenderFile: '.$filepath);		
		ob_start();
		if(file_exists($filepath)){
			extract($this->getBag(), EXTR_REFS);
			include($filepath);
			$this->viewcontent=ob_get_contents();
		}else
			$this->viewcontent='Could not find view: '.$$filepath;
		$this->render=false;
		ob_end_clean();
		global $viewcontent;
		$viewcontent=$this->viewcontent;		
	}
	protected function RenderText($text){
		Debug::Message('RenderText: '.$text);		
		$this->render=false;
		global $viewcontent;
		$viewcontent=$text;	
	}
	public function Notfound(){
        $view=$this->findView($this->controller,'notfound');
        if($view) {
            $this->setUpRouting($this->controller, 'notfound');
            $this->Render($this->controller,'notfound');
        }else{
            $this->setUpRouting('default','notfound');
            $this->Render('default','notfound');
        }
	}
	protected function redirect($query=false){
		if(defined('NOREDIRECT') && NOREDIRECT)
			return;
		$redirect=Communication::useRedirect();
		if($redirect)
			if(strtolower($redirect)=='referer'){
				$redirect=str_replace('&result=1','',Communication::getReferer());
				$redirect=str_replace('&result=2','',$redirect);
				$redirect=str_replace('&result=0','',$redirect);
				
				Communication::redirectTo(str_replace('&result=1','',$redirect),$query);
			}else{
				$redirect=str_replace('&result=1','',$redirect);
				$redirect=str_replace('&result=2','',$redirect);				
				$redirect=str_replace('&result=0','',$redirect);				
				Communication::redirectTo($redirect,$query);
			}
	}
	public static function ViewContents(){
		global $viewcontent;
		return $viewcontent;
	}
	public static function CurrentAction(){
        return self::$currentAction;
    }
    public static function CurrentController(){
         return self::$currentController;
    }
	private function findView($controller,$action){
		if($this->viewpath){
			return rtrim($this->viewpath,'/').'/'.$controller.'/'.$action.'.php';
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
	protected function setDefaultAction($action){
		$this->action=$action; 
	}

    public static function setUpRouting($controller, $action)
    {
        self::$currentController=$controller;
        self::$currentAction=$action;
    }
}