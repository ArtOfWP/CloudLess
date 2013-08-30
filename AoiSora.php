<?php
/*
Plugin Name: PHP MVC For WordPress (AoiSora)
Plugin URI: http://artofwp.com/products/php-mvc-for-wordpress/
Description: AoiSora is a PHP MVC Framework for WordPress.
Version: 13.8
Author: Andreas Nurbo
Author URI: http://artofwp.com/
*/
// Configures/loads AoiSora

if(!class_exists("AoiSora")){
    function sl_file($file,$isPlugin=true){
        if($isPlugin)
            return CLOUDLESS_APP_DIR.'/'.$file.'/'.$file.'.php';
        return CLOUDLESS_APP_DIR.'/'.$file;
    }
include('init.php');

    /**
     * Class AoiSora
     * WordPress plugin that sets up the AoiSora (CloudLessMVC framework)
     */
class AoiSora extends ApplicationBase{
    /**
      * @var $options Options
      */
	public $options;
	private static $instance;

    /**
     * Sets up aoisoraLoaded hook, calls parent class. Setups up standard libraries
     */
    function AoiSora(){
		parent::__construct('AoiSora',sl_file('AoiSora'),true, true);
        $this->setFrontIncludes();
	}

    /**
     * Sets version and update information
     */
    function onInitUpdate(){
		$this->VERSION='13.8';
		$this->UPDATE_SITE='http://api.artofwp.com/?free_update=plugin';
		$this->SLUG='php-mvc-for-wordpress';
		$this->VERSION_INFO_LINK='http://api.artofwp.com/?update=plugin_information';		
	}

    /**
     * Initiates options for the plugin
     */
    function onLoadOptions(){
        $applications=new Option('applications',array());
        $this->options->add($applications);
        $installed=new Option('installed',array());
        $this->options->add($installed);
        $this->options->init();
    }

    /**
     * Configures standard JS libraries etc.
     */
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
        $jUiStars = new FrontInclude('jquery-ui-stars', clmvc_app_url('AoiSora','/lib/js/jquery.ui.stars/ui.stars.min.js'),array('jquery','jquery-ui-core','jquery-ui-widget'));
        $scripts->register($jUiStars);
        global $wp_version;
   		if(version_compare($wp_version,'3.1','<')){
   			$jUiWidget = new FrontInclude('jquery-ui-widget', clmvc_app_url('AoiSora','/lib/js/ui.widget.js'),'jquery');
            $scripts->register($jUiWidget);
        }
  		$jUiTagIt = new FrontInclude('jquery-ui-tag-it', clmvc_app_url('AoiSora','/lib/js/jquery.ui.tag-it/ui.tag-it.js'),array('jquery','jquery-ui-core','jquery-ui-widget'));
        $scripts->register($jUiTagIt);
        $jUiStyles = new FrontInclude('jquery-ui-stars', clmvc_app_url('AoiSora','/lib/js/jquery.ui.stars/ui.stars.min.css'));
        $styles->register($jUiStyles);
        $forms=new FrontInclude('forms', clmvc_app_url('AoiSora','/lib/css/forms.css'));
     	$wordpress= new FrontInclude('wordpress', clmvc_app_url('AoiSora','/lib/css/wordpress/jquery-ui-1.7.2.wordpress.css'));
        $styles->register($forms);
        $styles->register($wordpress);
    }

    /**
     * Setups routing
     */
    function onInit(){
        if(PREROUTE && isset($_GET[CONTROLLERKEY]) && isset($_GET[ACTIONKEY]))
            Hook::register('template_redirect',array($this,'preRoute'));
    }

    /**
     * Reroutes
     */
    function preRoute(){
        $success=Route::reroute();
        if(!$success){
            header("Status: 404");
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found",true,$http_response_code= 404);
            echo "<h1>404 Not Found</h1>";
            exit;
        }
    }

    /**
     * Instantiates the class
     * @return AoiSora
     */
    static function instance(){
    	if(self::$instance)
    		return self::$instance;
    	self::$instance=new AoiSora();
    	self::$instance->init();
    	return self::$instance;
    }

    /**
     * Configures correct behavior on updates/install
     */
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

    /**
     * Called on plugins loaded. runs hooks. aoisora-libraries, aoisora-loaded
     */
    function aoisoraLoaded(){
        aoisora_loaded();
		Hook::run('aoisora-libraries');
		Hook::run('aoisora-loaded');
	}
}
AoiSora::instance();
}