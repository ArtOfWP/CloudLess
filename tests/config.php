<?php
if(!defined('LOADALL'))
	define('LOADALL',true);
define('PACKAGE_PATH',dirname(__FILE__).'/../');
define('APPPATH',dirname(__FILE__).'/../');
define('LIB','/app/');
define('DOMAIN',LIB.'domain/');
define('VIEWS',LIB.'Views/');
define('CONTROLLERS',LIB.'/Controllers/');

include(PACKAGE_PATH.'autoloader.php');
if(LOADALL){
include(PACKAGE_PATH . 'lib/Route.php');
include(PACKAGE_PATH . 'lib/Debug.php');
load(PACKAGE_PATH.'lib/Helpers/');
    load(PACKAGE_PATH.'lib/Events/');
    load(PACKAGE_PATH.'lib/Interfaces/');
    load(PACKAGE_PATH.'lib/Core/');
//	load(PACKAGE_PATH.'lib/Filters/');
//include(PACKAGE_PATH.'lib/Controllers/Filters/IFilter.php');
//include(PACKAGE_PATH.'lib/Controllers/Filters/SecurityFilter.php');
load(PACKAGE_PATH.'lib/Controllers/');
if(defined('VIEWENGINE') && VIEWENGINE=='WordPress'){
//C:\Users\andreas\My Projects\development\aoisora\wp-content\plugins\AoiSora\tests\config.php
    include('/../../../../wp-load.php');
	load(PACKAGE_PATH.'lib/ViewEngines/'.VIEWENGINE.'/');//.VIEWENGINE.'.php');
}
}
if(LOADDATABASE){
global $db;
$db = new MySqlDatabase();
global $db_prefix;
$db_prefix='wp_aoisora_';
}