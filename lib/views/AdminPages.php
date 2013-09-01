<?php
namespace CLMVC\Views;
/**
 * Class AdminPages
 * TODO: Remove WordPress dependency
 */
class AdminPages{
	private $pagetitle;
	private $menutitle;
	private $accesslevel;
	private $name;
	private $dir;
	private $icon_url;
	private $app;

    /**
     * @param $app
     * @param $pagetitle
     * @param $menutitle
     * @param $accesslevel
     * @param $name
     * @param bool $icon_url
     */
    function AdminPages($app,$pagetitle,$menutitle,$accesslevel,$name,$icon_url=false){
		$this->app=$app;
		$this->dir=$app->dir;
		$this->pagetitle=$pagetitle;
		$this->menutitle=$menutitle;
		$this->accesslevel=$accesslevel;
		$this->name=$name;
		$this->icon_url=$icon_url;
	}

    /**
     * Add menu
     */
    function addMenu(){
		if($this->icon_url)
			$this->addScriptStyleAction(add_menu_page($this->pagetitle,$this->menutitle,$this->accesslevel,$this->name,array($this,'none'),$this->icon_url));
		else
			$this->addScriptStyleAction(add_menu_page($this->pagetitle,$this->menutitle,$this->accesslevel,$this->name,array($this,'none')));
	}

    /**
     * Add submenu
     * @param string $pagetitle
     * @param string $menutitle
     * @param string $accesslevel
     * @param string $controller
     * @param callback $action
     */
    function addSubmenu($pagetitle, $menutitle, $accesslevel, $controller, $action){
		$this->addScriptStyleAction(add_submenu_page($this->name,$pagetitle,$menutitle,$accesslevel,$controller,array($this,$action)));
	}

    /**
     * @param $pagetitle
     * @param $menutitle
     * @param $accesslevel
     */
    function addOptionsPage($pagetitle,$menutitle,$accesslevel){
		$this->addScriptStyleAction(add_options_page($pagetitle,$menutitle,$accesslevel,str_replace(" ","",strtolower($menutitle)),array(&$this,'options')));		
	}

    /**
     * @param $pagename
     */
    private function addScriptStyleAction($pagename){
		if(method_exists($this->app,'on_admin_print_styles'))
			add_action("admin_print_styles-$pagename",array(&$this->app,'print_admin_styles'));
		if(method_exists($this->app,'on_admin_print_scripts'))
			add_action("admin_print_scripts-$pagename",array(&$this->app,'print_admin_scripts'));
	}

    /**
     * Used for toplevel menu pages.
     */
    function none(){}

    /**
     * @param $method
     * @param $args
     */
    function __call($method,$args){
		if(method_exists($this,$method))
			$this->$method();
		else{
			if(strtolower($method)=='options')
				include($this->dir. '/app/Views/options.php');
			else{
				$controller=array_key_exists_v(CONTROLLERKEY,Communication::getQueryString());
				$action=array_key_exists_v(ACTIONKEY,Communication::getQueryString());
				if(!$this->app->installed() && !$action)
					Route::rerouteToAction($controller,'install');			
				else
					Route::reroute();
				$this->printContent();
			}
		}
	}

    /**
     * Prints the generated content
     */
    protected function printContent(){
		global $viewcontent;
		echo $viewcontent;
	}
}