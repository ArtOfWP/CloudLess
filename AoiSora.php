<?php
/*
Plugin Name: PHP MVC For WordPress (AoiSora)
Plugin URI: http://artofwp.com/wpdk
Description: AoiSora is a PHP MVC Framework for WordPress.
Version: 10.5.2
Author: Andreas Nurbo
Author URI: http://artofwp.com/
*/
if(is_admin()){
	add_filter('after_plugin_row','update_aoisora_load_first',10,3);
	function update_aoisora_load_first($plugin_file,$plugin_data){
		$plugin = plugin_basename(__FILE__);
		$active = get_option('active_plugins');
		if ( $active[0] == $plugin)
			return;
		$place=array_search($plugin, $active);
		if($place===FALSE)
			return;
		array_splice($active, $place, 1);
		array_unshift($active, $plugin);
		update_option('active_plugins', $active);
	}
}
// Configures/loads AoiSora
define('PACKAGEPATH',dirname(__FILE__).'/');
require('init.php');
class AoiSora extends WpApplicationBase{
	public $options;
	private static $instance;
	protected $VERSION='10.5.2';
	protected $UPDATE_SITE='http://artofwp.com/?free_update=plugin';
	protected $SLUG='php-mvc-for-wordpress';	
	function AoiSora(){
		parent::WpApplicationBase('AoiSora',__FILE__,true,false);
		if(is_admin()){
			$this->check_for_update();
		}
		$this->load_js();
	}
	function on_load_options(){
		$this->options= Option::create('AoiSora');
		if($this->options->isEmpty()){
			$this->options->applications=array();
			$this->options->installed=array();
			$this->options->save();
		}
	}
	function load_js(){
		wp_register_script('jquery-validate',"http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js",array('jquery'));
		wp_register_script('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.js'),array('jquery','jquery-ui-core'));	
		wp_register_style('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.css'));
		wp_register_script('jquery-dialog',includes_url('/js/jquery/ui.dialog.js'));
		wp_register_script('jquery-ui',"http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js");
		if(is_admin()){
			wp_register_script('jquery-ui-tag-it',plugins_url('AoiSora/lib/js/jquery.ui.tag-it/ui.tag-it.js'),array('jquery','jquery-ui-core'));			
			wp_register_style('forms',plugins_url('AoiSora/lib/css/forms.css'));			
			wp_register_style('wordpress',plugins_url('AoiSora/lib/css/wordpress/jquery-ui-1.7.2.wordpress.css'));			
		}
	}	
    static function instance(){	    	
    	if(self::$instance)
    		return self::$instance;
    	self::$instance=new AoiSora();
    	return self::$instance;
    }
}
AoiSora::instance();