<?php
abstract class ApplicationBase{
	protected $installfrompath;
	private $models=array();
	protected abstract function config_install();
	protected abstract function config_uninstall();
	public function install(){
		$this->config_install();
		$this->load($this->installfrompath);
		$this->create();
	}
	private function create(){
		foreach($this->models as $model){
			$m = new $model();
			global $db;
			$db->createTable($m);
		}
		$db->createStoredRelations();
	}
	public function uninstall(){
		$this->config_uninstall();
		$this->load($this->installfrompath);
		$this->delete();
	}
	private function delete(){
		foreach($this->models as $model){
			$m = new $model();
			global $db;
			$db->dropTable($m);
		}
		$db->dropStoredRelations();		
	}	
	private function load($dir){
		$handle = opendir($dir);
		while(false !== ($resource = readdir($handle))) {
			if($resource!='.' && $resource!='..'){
				if(is_dir($dir.$resource))
					$this->load($dir.$resource.'/');
				else{
					$this->models[]=str_replace('.php','',$resource);
				 	include($dir.$resource);
				}
			}
		}
		closedir($handle);
	}
	protected function printContent(){
		global $viewcontent;
		echo $viewcontent;
	}
}
?>