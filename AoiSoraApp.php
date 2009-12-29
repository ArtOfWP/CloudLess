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
		$this->load_js();
	}
	function load_js(){
		wp_register_script('jquery-ui',"http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js");		
		wp_register_script('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.js'),array('jquery','jquery-ui-core'));
		wp_register_style('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.css'));
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