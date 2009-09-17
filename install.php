<?php
define('HOST','localhost');
define('DATABASE','phpmvc');
define('USERNAME','');
define('PASSWORD','');
function load($dir){
	$handle = opendir($dir);
	while(false !== ($resource = readdir($handle))) {
		if($resource!='.' && $resource!='..'){
			if(is_dir($dir.$resource))
				load($dir.$resource.'/');
			else
			 	include($dir.$resource);
		}
	}
	closedir($handle);
}
include('lib/Route.php');
include('lib/Debug.php');
load(dirname(__FILE__).'/lib/helpers/');
load(dirname(__FILE__).'/lib/core/');
load(dirname(__FILE__).'/lib/controllers/');
global $db;
$db = new MySqlDatabase();

$setting = new Setting();
$setting->setKey('');
$setting->setValue('');
$setting->setApplication('');
$db->createTable($setting);
?>