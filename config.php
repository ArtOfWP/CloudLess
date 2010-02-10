<?php
// Configures AoiSora for WordPress use
define('VIEWENGINE','wordpress');
define('LIB','/app/');
define('DOMAIN',LIB.'domain/');
define('VIEWS',LIB.'views/');
define('CONTROLLERS',LIB.'/controllers/');

if(!defined('DEBUG'))
	define('DEBUG',false);
if(!defined('SQLDEBUG'))
	define('SQLDEBUG',false);
if(!defined('NOREDIRECT'))
	define('NOREDIRECT',false);
	/*
if(!defined('HOST')){
	define('HOST','localhost');
	define('DATABASE','phpmvc');
	define('USERNAME','');
	define('PASSWORD','');
}*/
?>