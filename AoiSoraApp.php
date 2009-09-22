<?php
class AoiSoraApp{
	public $options;
	public function AoiSoraApp(){
		$this->options= new WpOption('AoiSora');
		if($this->options->isEmpty()){
			$this->options->applications=array();
			$this->options->installed=array();
			$this->options->save();
		}
		$appName='AoiSora';
		register_activation_hook("$appName/$appName.php", array(&$this,'activate'));
		register_deactivation_hook("$appName/$appName.php", array(&$this,'deactivate'));
		register_uninstall_hook("$appName/$appName.php", array(&$this,'delete'));
	}
	function activate(){
		$this->options->save();
	}
	function deactivate(){
		$this->options= new WpOption('AoiSora');
		$this->options->delete();
	}
	function delete(){
		$this->options= new WpOption('AoiSora');
		$this->options->delete();
	}
}
	global $aoiSoraApp;
	$aoiSoraApp=new AoiSoraApp();
?>