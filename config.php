<?php
// Configures AoiSora for WordPress use
define('VIEWENGINE','wordpress');
if(!defined('LOADAPPS'))
	define('LOADAPPS',true);
	
if(!defined('DEBUG'))
	define('DEBUG',true);
if(!defined('SQLDEBUG'))
	define('SQLDEBUG',false);
if(!defined('HOST')){
	define('HOST','localhost');
	define('DATABASE','phpmvc');
	define('USERNAME','');
	define('PASSWORD','');
}
?>