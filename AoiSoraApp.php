<?php
ini_set('error_reporting', E_ALL-E_NOTICE);
ini_set('display_errors', 1);
?> 

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
		wp_register_script('jquery-validate',"http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js",array('jquery'));
		wp_register_script('jquery-ui',"http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js");		
		wp_register_script('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.js'),array('jquery','jquery-ui-core'));
		wp_register_style('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.css'));
		if(is_admin())
			wp_register_style('forms',plugins_url('AoiSora/lib/css/forms.css'));			
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