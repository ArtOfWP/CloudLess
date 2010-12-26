<?php
/*
Plugin Name: PHP MVC For WordPress (AoiSora)
Plugin URI: http://artofwp.com/products/php-mvc-for-wordpress/
Description: AoiSora is a PHP MVC Framework for WordPress.
Version: 10.12.2
Author: Andreas Nurbo
Author URI: http://artofwp.com/
*/
// Configures/loads AoiSora

if(!class_exists("AoiSora")){
define('PACKAGEPATH',dirname(__FILE__).'/');
require('init.php');

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
		if(!file_exists(ABSPATH.'/.htaccess') && !is_writable(ABSPATH.'/.htaccess')){
		add_action('after_plugin_row_'.plugin_basename(__FILE__),'after_aoisora_plugin_htaccess_row', 10, 2 );
	function after_aoisora_plugin_htaccess_row($plugin_file, $plugin_data){
		echo '
<tr class="error" style=""><td colspan="3" class="" style=""><div class="" style="padding:3px 3px 3px 3px;font-weight:bold;font-size:8pt;border:solid 1px #CC0000;background-color:#FFEBE8">AoiSora requries .htaccess with the following code before #BEGIN WORDPRESS.
<pre>'. 
get_htaccess_rules().'</pre>		
</div></td></tr>';
			//deactivate_plugins(plugin_basename(__FILE__));
		}
	}	
}
class AoiSora extends WpApplicationBase{
	public $options;
	private static $instance;
	function AoiSora(){
		parent::WpApplicationBase('AoiSora',__FILE__,true,false);
		$this->load_js();
	}
	function on_init_update(){
		$this->VERSION='10.12.2';
		$this->UPDATE_SITE='http://api.artofwp.com/?free_update=plugin';
		$this->SLUG='php-mvc-for-wordpress';
		$this->VERSION_INFO_LINK='http://api.artofwp.com/?update=plugin_information';		
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
	function on_activate(){
	$htaccessrules=get_htaccess_rules();
	$path=get_htaccess_rules_path();
		if(is_writable($path)){
			$temp=file_get_contents($path);
			if(strpos($temp,'PHPMVC')!==FALSE)
				return;
			$fh=fopen($path,'w');
			fwrite($fh,$htaccessrules);
			fwrite($fh,$temp);	
			fclose($fh);
		}
	}
}
AoiSora::instance();
}