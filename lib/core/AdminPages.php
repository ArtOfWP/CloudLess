<?php
class AdminPages{
	private $pagetitle;
	private $menutitle;
	private $accesslevel;
	private $name;
	private $dir;
	private $icon_url;
	function AdminPages($app,$pagetitle,$menutitle,$accesslevel,$name,$icon_url=false){
		$this->dir=$app->dir;
		$this->pagetitle=$pagetitle;
		$this->menutitle=$menutitle;
		$this->accesslevel=$accesslevel;
		$this->name=$name;
		$this->icon_url=$icon_url;
	}
	function addMenu(){
		if($this->icon_url)
		add_menu_page($this->pagetitle,$this->menutitle,$this->accesslevel,$this->name,array(&$this,'none'),$this->icon_url);		
		else
		add_menu_page($this->pagetitle,$this->menutitle,$this->accesslevel,$this->name,array(&$this,'none'));
	}
	function addSubmenu($pagetitle,$menutitle,$accesslevel,$controller,$action){
		add_submenu_page($this->name,$pagetitle,$menutitle,$accesslevel,$controller,array(&$this,$action));
	}
	function addOptionsPage($pagetitle,$menutitle,$accesslevel){
		add_options_page($pagetitle,$menutitle,$accesslevel,str_replace(" ","",strtolower($menutitle)),array(&$this,'options'));		
	}
	function none(){}
	function __call($method,$args){
		if(method_exists($this,$method))
			$this->$method();
		else{
			if(strtolower($method)=='options')
				include($this->dir. '/app/views/options.php');
			else{
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