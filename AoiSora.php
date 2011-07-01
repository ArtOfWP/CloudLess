<?php
/*
Plugin Name: PHP MVC For WordPress (AoiSora)
Plugin URI: http://artofwp.com/products/php-mvc-for-wordpress/
Description: AoiSora is a PHP MVC Framework for WordPress.
Version: 11.6.0.2
Author: Andreas Nurbo
Author URI: http://artofwp.com/
*/
// Configures/loads AoiSora

if(!class_exists("AoiSora")){
define('PACKAGEPATH',dirname(__FILE__).'/');
include('init.php');

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
		if(!file_exists(ABSPATH.'/.htaccess') || !is_writable(ABSPATH.'/.htaccess')){
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
		add_action('plugins_loaded',array($this,'aoisoraLoaded'));
	}
	function onInitUpdate(){
		$this->VERSION='11.6.0.2';
		$this->UPDATE_SITE='http://api.artofwp.com/?free_update=plugin';
		$this->SLUG='php-mvc-for-wordpress';
		$this->VERSION_INFO_LINK='http://api.artofwp.com/?update=plugin_information';		
	}
	function onLoadOptions(){
		$this->options= Option::create('AoiSora');
		if($this->options->isEmpty()){
			$this->options->applications=array();
			$this->options->installed=array();
			$this->options->save();
		}
	}
	function onInit(){
		wp_register_script('jquery-validate',"http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js",array('jquery'));
		wp_register_script('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.js'),array('jquery','jquery-ui-core','jquery-ui-widget'));
		wp_register_style('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.css'));
		global $wp_version;
		if(version_compare($wp_version,'3.1','<'))
			wp_register_script('jquery-ui-widget',plugins_url('AoiSora/lib/js/ui.widget.js'),'jquery');
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
    	self::$instance->init();
    	return self::$instance;
    }
    function onUpdate(){
	    $htaccessrules=get_htaccess_rules();
		$path=get_htaccess_rules_path();
		if(is_writable($path)){
			$temp=file_get_contents($path);
			$fh=fopen($path,'w');
			if(strpos($temp,'PHPMVC')!==FALSE){
				$htaccessrules=str_replace('$1','\$1',$htaccessrules);
				$htaccessrules=str_replace('$2','\$2',$htaccessrules);				
				$temp=preg_replace("/\# BEGIN PHPMVC.*\# END PHPMVC/s",$htaccessrules,$temp);
			}else
				fwrite($fh,$htaccessrules);
			fwrite($fh,$temp);
			fclose($fh);
		}
    }
	function aoisoraLoaded(){
		do_action('aoisora-loaded');
		Hook::run('aoisora-libraries');
		Hook::run('aoisora-loaded');
	}
}
AoiSora::instance();
}
register_activation_hook(__FILE__, 'setup_htaccess_rules');
function setup_htaccess_rules(){
	$htaccessrules=get_htaccess_rules();
	$path=get_htaccess_rules_path();
	if(is_writable($path)){
		$temp=file_get_contents($path);
		$fh=fopen($path,'w');
		if(strpos($temp,'PHPMVC')!==FALSE){
			$htaccessrules=str_replace('$1','\$1',$htaccessrules);
			$htaccessrules=str_replace('$2','\$2',$htaccessrules);				
			$temp=preg_replace("/\# BEGIN PHPMVC.*\# END PHPMVC/s",$htaccessrules,$temp);
		}else
			fwrite($fh,$htaccessrules);
		fwrite($fh,$temp);	
		fclose($fh);
	}
}