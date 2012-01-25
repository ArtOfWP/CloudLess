<?php
/*
Plugin Name: PHP MVC For WordPress (AoiSora)
Plugin URI: http://artofwp.com/products/php-mvc-for-wordpress/
Description: AoiSora is a PHP MVC Framework for WordPress.
Version: 12.1
Author: Andreas Nurbo
Author URI: http://artofwp.com/
*/
// Configures/loads AoiSora

if(!class_exists("AoiSora")){
    $tempPath='';
    if(defined('WP_PLUGIN_DIR'))
        $tempPath=WP_PLUGIN_DIR.'/AoiSora/';
    else if(defined('WP_CONTENT_DIR'))
        $tempPath.'/plugins/AoiSora/';
    else
        $tempPath=ABSPATH.'wp-content/plugins/AoiSora/';
define('PACKAGEPATH',$tempPath);
function sl_file($file,$isPlugin=true){
    if($isPlugin)
        return WP_PLUGIN_DIR.'/'.$file.'/'.$file.'.php';
    return WP_PLUGIN_DIR.'/'.$file;
}
include('init.php');

if(is_admin()){
	add_filter('after_plugin_row','update_aoisora_load_first',10,3);
	function update_aoisora_load_first($plugin_file,$plugin_data){
		$plugin = plugin_basename(sl_file('AoiSora'));
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
		add_action('after_plugin_row_'.plugin_basename(sl_file('AoiSora')),'after_aoisora_plugin_htaccess_row', 10, 2 );
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
    /**
      * @var $options Options
      */
	public $options;
	private static $instance;
	function AoiSora(){
		parent::WpApplicationBase('AoiSora',sl_file('AoiSora'),true,false);
		add_action('plugins_loaded',array($this,'aoisoraLoaded'));
        $this->setFrontIncludes();
	}
	function onInitUpdate(){
		$this->VERSION='12.1';
		$this->UPDATE_SITE='http://api.artofwp.com/?free_update=plugin';
		$this->SLUG='php-mvc-for-wordpress';
		$this->VERSION_INFO_LINK='http://api.artofwp.com/?update=plugin_information';		
	}
	function onLoadOptions(){
        $applications=new Option('applications',array());
        $this->options->add($applications);
        $installed=new Option('installed',array());
        $this->options->add($installed);
        $this->options->init();
    }
    private function setFrontIncludes(){
        $cont=Container::instance();
        /**
         * @var $scripts ScriptIncludes
         * @var $styles ScriptIncludes
         */
        $scripts=$cont->fetch('ScriptIncludes');
        $styles=$cont->fetch('StyleIncludes');
        $jVal= new FrontInclude('jquery-validate',"http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js",array('jquery'));
        $scripts->register($jVal);
        $jUiStars = new FrontInclude('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.js'),array('jquery','jquery-ui-core','jquery-ui-widget'));
        $scripts->register($jUiStars);
        global $wp_version;
   		if(version_compare($wp_version,'3.1','<')){
   			$jUiWidget = new FrontInclude('jquery-ui-widget',plugins_url('AoiSora/lib/js/ui.widget.js'),'jquery');
            $scripts->register($jUiWidget);
        }
  		$jUiTagIt = new FrontInclude('jquery-ui-tag-it',plugins_url('AoiSora/lib/js/jquery.ui.tag-it/ui.tag-it.js'),array('jquery','jquery-ui-core','jquery-ui-widget'));
        $scripts->register($jUiTagIt);
        $jUiStyles = new FrontInclude('jquery-ui-stars',plugins_url('AoiSora/lib/js/jquery.ui.stars/ui.stars.min.css'));
        $styles->register($jUiStyles);
        $forms=new FrontInclude('forms',plugins_url('AoiSora/lib/css/forms.css'));
     	$wordpress= new FrontInclude('wordpress',plugins_url('AoiSora/lib/css/wordpress/jquery-ui-1.7.2.wordpress.css'));
        $styles->register($forms);
        $styles->register($wordpress);
    }
    function onInit(){
        if(PREROUTE && isset($_GET[CONTROLLERKEY]) && isset($_GET[ACTIONKEY]))
            Hook::register('template_redirect',array($this,'preRoute'));
    }
    function preRoute(){
        $success=Route::reroute();
        if(!$success){
            header("Status: 404");
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found",true,$http_response_code= 404);
            echo "<h1>404 Not Found</h1>";
            exit;
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
        aoisora_loaded();
		Hook::run('aoisora-libraries');
		Hook::run('aoisora-loaded');
	}
}
AoiSora::instance();
}
register_activation_hook(sl_file('AoiSora'), 'setup_htaccess_rules');
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