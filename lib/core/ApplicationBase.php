<?php
abstract class ApplicationBase{
	protected $installfrompath;
	private $models=array();
	private $dir;
	private $app;
	function ApplicationBase($appName,$appDir){
		$this->dir=$appDir;
		$this->app=$appName;
		$this->installfrompath=$appDir.'/app/core/domain/';
		register_activation_hook("$appName/$appName.php", array(&$this,'activate'));
		register_deactivation_hook("$appName/$appName.php", array(&$this,'deactivate'));
	}
	function activate(){
		if(method_exists($this,'onactivate'))
			$this->onactivate();
		AoiSoraSettings::addApplication($this->app,$this->dir);
	}
	function deactivate(){
		if(method_exists($this,'ondeactivate'))
			$this->ondeactivate();
		AoiSoraSettings::removeApplication($this->app);
	}
	function installed(){
		return AoiSoraSettings::installed($this->app);
	}
	public function install(){
		if(method_exists($this,'onpreinstall'))
			$this->onpreinstall();		
		Debug::Value('Install from path',$this->installfrompath);
		$this->models=array();
		$this->load($this->installfrompath);
		$result=true;
		$this->create();
		if($result)
			AoiSoraSettings::installApplication($this->app);			
		if(method_exists($this,'onafterinstall'))
			$this->onafterinstall();			
		return $result;
	}
	private function create(){
		global $db;
		foreach($this->models as $model){
			$m = new $model();
			$db->createTable($m);
		}
		$db->createStoredRelations();
	}
	public function uninstall(){
		if(method_exists($this,'onpreuninstall'))
			$this->onpreuninstall();
		$this->models=array();
		$this->load($this->installfrompath);
		$result=true;		
		$this->delete();
		if($result)
			AoiSoraSettings::uninstallApplication($this->app);
		if(method_exists($this,'onafteruninstall'))
			$this->onafteruninstall();			
	}
	function render_title($title,$sep=" &mdash; ",$placement="left"){
//		Debug::Message('Render Title');
		global $aoisoratitle;
		$title.=$aoisoratitle." $sep ";
		return $title;
	}
	private function delete(){
		global $db;		
		foreach($this->models as $model){
			$m = new $model();
			$db->dropTable($m);
		}
		$db->dropStoredRelations();		
	}	
	private function load($dir){
		Debug::Value('Loading directory',$dir);
		$handle = opendir($dir);
		while(false !== ($resource = readdir($handle))) {
			if($resource!='.' && $resource!='..'){
				if(is_dir($dir.$resource))
					$this->load($dir.$resource.'/');
				else{			
					$this->models[]=str_replace('.php','',$resource);				 	
					Debug::Value('Loaded',$resource);
				}
			}
		}
		var_dump($this->models);
		closedir($handle);
	}
	protected function printContent(){
		global $viewcontent;
		echo $viewcontent;
	}
}
?>