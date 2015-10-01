<?php
if(!defined('LOADALL'))
	define('LOADALL',true);
define('PACKAGEPATH',dirname(__FILE__).'/../');
define('APPPATH',dirname(__FILE__).'/../');
define('LIB','/app/');
define('DOMAIN',LIB.'domain/');
define('VIEWS',LIB.'Views/');
define('CONTROLLERS',LIB.'/Controllers/');

include(PACKAGEPATH.'autoloader.php');
if(LOADALL){
include(PACKAGEPATH . 'lib/Route.php');
include(PACKAGEPATH . 'lib/Debug.php');
load(PACKAGEPATH.'lib/Helpers/');
    load(PACKAGEPATH.'lib/Events/');
    load(PACKAGEPATH.'lib/Interfaces/');
    load(PACKAGEPATH.'lib/Core/');
//	load(PACKAGEPATH.'lib/Filters/');
//include(PACKAGEPATH.'lib/Controllers/Filters/IFilter.php');
//include(PACKAGEPATH.'lib/Controllers/Filters/SecurityFilter.php');
load(PACKAGEPATH.'lib/Controllers/');
if(defined('VIEWENGINE') && VIEWENGINE=='WordPress'){
//C:\Users\andreas\My Projects\development\aoisora\wp-content\plugins\AoiSora\tests\config.php
    include('/../../../../wp-load.php');
	load(PACKAGEPATH.'lib/ViewEngines/'.VIEWENGINE.'/');//.VIEWENGINE.'.php');
}
}
if(LOADDATABASE){
global $db;
$db = new MySqlDatabase();
global $db_prefix;
$db_prefix='wp_aoisora_';
}