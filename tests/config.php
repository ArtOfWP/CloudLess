<?php
if(!defined('LOADALL'))
	define('LOADALL',true);
define('PACKAGEPATH',dirname(__FILE__).'/../');
define('APPPATH',dirname(__FILE__).'/../');
define('LIB','/app/');
define('DOMAIN',LIB.'domain/');
define('VIEWS',LIB.'views/');
define('CONTROLLERS',LIB.'/controllers/');

include(PACKAGEPATH.'load.php');
if(LOADALL){
include(PACKAGEPATH.'lib/Route.php');
include(PACKAGEPATH.'lib/Debug.php');
load(PACKAGEPATH.'lib/helpers/');
    load(PACKAGEPATH.'lib/events/');
    load(PACKAGEPATH.'lib/core/');
//	load(PACKAGEPATH.'lib/filters/');
//include(PACKAGEPATH.'lib/controllers/filters/IFilter.php');
//include(PACKAGEPATH.'lib/controllers/filters/SecurityFilter.php');
load(PACKAGEPATH.'lib/controllers/');
if(defined('VIEWENGINE') && VIEWENGINE=='wordpress'){
	load(PACKAGEPATH.'lib/viewengine/'.VIEWENGINE.'/');//.VIEWENGINE.'.php');
}
}
if(LOADDATABASE){
global $db;
$db = new MySqlDatabase();
global $db_prefix;
$db_prefix='wp_aoisora_';
}