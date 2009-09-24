<?php
// Configures AoiSora for WordPress use
define('VIEWENGINE','wordpress');
if(!defined('LOADAPPS'))
	define('LOADAPPS',true);
	define('LIB','/app/');
	define('DOMAIN',LIB.'domain/');
	define('VIEWS',LIB.'views/');
	define('CONTROLLERS',LIB.'/controllers/');
if(!defined('DEBUG'))
	define('DEBUG',true);
if(!defined('SQLDEBUG'))
	define('SQLDEBUG',false);

	global $db_prefix;
	global $table_prefix;
	$db_prefix=$table_prefix.'aoisora_';
	/*
if(!defined('HOST')){
	define('HOST','localhost');
	define('DATABASE','phpmvc');
	define('USERNAME','');
	define('PASSWORD','');
}*/
?>