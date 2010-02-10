<?php
ini_set('error_reporting', E_ALL-E_NOTICE);
ini_set('display_errors', 1);

class AoiSoraApp{
	public $options;
	public function AoiSoraApp(){
		$this->options= Option::create('AoiSora');
		if($this->options->isEmpty()){
			$this->options->applications=array();
			$this->options->installed=array();
			$this->options->save();
		}
		$appName='AoiSora';
		add_action( 'admin_init', array(&$this,'register_settings' ));			
		register_activation_hook("$appName/$appName.php", array(&$this,'activate'));
		register_deactivation_hook("$appName/$appName.php", array(&$this,'deactivate'));
		register_uninstall_hook("$appName/$appName.php", array(&$this,'delete'));
		$this->load_js();
	}
	function load_js(){
		wp_register_script('jquery-validate',"http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js",array('jquery'));
		wp_register_script('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.js'),array('jquery','jquery-ui-core'));
		wp_register_style('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.css'));
		wp_register_script('jquery-dialog',includes_url('/js/jquery/ui.dialog.js'));
		wp_register_script('jquery-ui',"http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js");			
		if(is_admin()){
			wp_register_style('forms',plugins_url('AoiSora/lib/css/forms.css'));			
			wp_register_style('wordpress',plugins_url('AoiSora/lib/css/wordpress/jquery-ui-1.7.2.wordpress.css'));			
		}
	}
	function activate(){
		
	}
	function deactivate(){
		$this->options= Option::create('AoiSora');
		$this->options->delete();
	}
	function delete(){
		$this->options= Option::create('AoiSora');
		$this->options->delete();
	}
	function register_settings(){
		WpHelper::registerSettings("AoiSora/AoiSora.php",array('AoiSora'));		
	}
}
	global $aoiSoraApp;
	$aoiSoraApp=new AoiSoraApp();
?>