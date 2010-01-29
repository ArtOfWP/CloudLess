<?php
/*
Plugin Name: PHP MVC For WordPress
Plugin URI: http://artofwp.com/wpdk
Description: AoiSora is a PHP MVC Framework for WordPress.
Version: 10.1
Author: Andreas Nurbo
Author URI: http://artofwp.com/
*/
// Configures/loads AoiSora
define('PACKAGEPATH',dirname(__FILE__).'/');
define('LOADAPPS',false);
global $loadviewengine;
$loadviewengine='WordPress';
require('init.php');

?>