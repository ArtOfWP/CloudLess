<?php
/*
Plugin Name: AoiSora
Plugin URI: http://artofwp.com/shop
Description: A PHP MVC Framework for WordPress
Version: 9.09
Author: Andreas Nurbo
Author URI: http://andreasnurbo.com/
*/

// Configures/loads AoiSora
define('PACKAGEPATH',dirname(__FILE__).'/');
define('LOADAPPS',true);
global $loadviewengine;
$loadviewengine='WordPress';
require('init.php');

function viewcomponent($app,$component,$params){
	include_once(WP_PLUGIN_DIR."/$app/".strtolower("app/views/components/$component/$component.php"));
	$c = new $component($params);
	$c->render();
}

?>