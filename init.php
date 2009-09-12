<?php
include('config.php');
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
include('lib/viewengine/'.VIEWENGINE.'.php');
global $db;
$db = new MySqlDatabase();
$appsSettings=AoiSoraSettings::getApplications();
global $apps;
$apps=array();
foreach($appsSettings as $app){
	$apps[]=$app->getValue();
//	Debug::Value('Loading app',$app->getValue());
	loadApp($app->getValue());
}
function loadApp($dir){
	load($dir.'/app/core/');
	load($dir.'/app/controllers/');
}
?>