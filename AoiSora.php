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
namespace CLMVC;

use CLMVC\Core\Container;
use CLMVC\Core\Application\ApplicationBase;
use CLMVC\Core\Includes\FrontInclude;
use CLMVC\Core\Includes\ScriptIncludes;
use CLMVC\Core\Options;
use CLMVC\Core\Option;
use CLMVC\Events\Hook;

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
    function __construct(){
		parent::__construct('AoiSora',sl_file('AoiSora'),true, true);
        $this->setFrontIncludes();
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
     * Called on plugins loaded. runs hooks. aoisora-libraries, aoisora-loaded
     */
    function loaded() {
		Hook::run('aoisora-libraries');
		Hook::run('aoisora-loaded');
	}
}
AoiSora::instance();
}