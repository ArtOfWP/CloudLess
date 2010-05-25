<?php
class AdminPages{
	private $pagetitle;
	private $menutitle;
	private $accesslevel;
	private $name;
	private $dir;
	private $icon_url;
	private $app;
	function AdminPages($app,$pagetitle,$menutitle,$accesslevel,$name,$icon_url=false){
		$this->app=$app;
		$this->dir=$app->dir;
		$this->pagetitle=$pagetitle;
		$this->menutitle=$menutitle;
		$this->accesslevel=$accesslevel;
		$this->name=$name;
		$this->icon_url=$icon_url;
	}
	function addMenu(){
		if($this->icon_url)
			$this->addScriptStyleAction(add_menu_page($this->pagetitle,$this->menutitle,$this->accesslevel,$this->name,array(&$this,'none'),$this->icon_url));		
		else
			$this->addScriptStyleAction(	add_menu_page($this->pagetitle,$this->menutitle,$this->accesslevel,$this->name,array(&$this,'none')));
	}
	function addSubmenu($pagetitle,$menutitle,$accesslevel,$controller,$action){
		$this->addScriptStyleAction(add_submenu_page($this->name,$pagetitle,$menutitle,$accesslevel,$controller,array(&$this,$action)));
	}
	function addOptionsPage($pagetitle,$menutitle,$accesslevel){
		$this->addScriptStyleAction(add_options_page($pagetitle,$menutitle,$accesslevel,str_replace(" ","",strtolower($menutitle)),array(&$this,'options')));		
	}
	private function addScriptStyleAction($pagename){
		if(method_exists($this->app,'on_admin_print_styles'))
			add_action("admin_print_styles-$pagename",array(&$this->app,'print_admin_styles'));
		if(method_exists($this->app,'on_admin_print_scripts'))
			add_action("admin_print_scripts-$pagename",array(&$this->app,'print_admin_scripts'));				
			
	}
	function none(){}
	function __call($method,$args){
		if(method_exists($this,$method))
			$this->$method();
		else{
			if(strtolower($method)=='options')
				include($this->dir. '/app/views/options.php');
			else if(!$this->app->installed()){
				$controller=array_key_exists_v(CONTROLLERKEY,$_GET);
				Route::rerouteToAction($controller,'install');
$this->printContent();				
			}else{
				Route::reroute();
				$this->printContent();
			}
		}
	}
	protected function printContent(){
		global $viewcontent;
		echo $viewcontent;
	}
}
?>