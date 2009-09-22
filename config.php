<?php
// Configures AoiSora for WordPress use
define('VIEWENGINE','WordPress');
if(!defined('LOADAPPS'))
	define('LOADAPPS',true);
	
if(!defined('DEBUG'))
	define('DEBUG',false);
if(!defined('SQLDEBUG'))
	define('SQLDEBUG',false);
if(!defined('HOST')){
	define('HOST','localhost');
	define('DATABASE','phpmvc');
	define('USERNAME','');
	define('PASSWORD','');
}
?>