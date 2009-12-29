<?php
/*
Plugin Name: AoiSora
Plugin URI: http://artofwp.com/wpdk
Description: A PHP MVC Framework for WordPress
Version: 9.12
Author: Andreas Nurbo
Author URI: http://andreasnurbo.com/
*/

// Configures/loads AoiSora
define('PACKAGEPATH',dirname(__FILE__).'/');
define('LOADAPPS',true);
global $loadviewengine;
$loadviewengine='WordPress';
require('init.php');

?>