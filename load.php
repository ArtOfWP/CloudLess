<?php
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
function loadApp($dir){
	load($dir.'/app/core/');
	load($dir.'/app/controllers/');
}
function loadAoiSora(){
	include(PACKAGEPATH.'config.php');
	include(PACKAGEPATH.'lib/Route.php');
	include(PACKAGEPATH.'lib/Debug.php');
	load(PACKAGEPATH.'lib/helpers/');
	load(PACKAGEPATH.'lib/core/');
	load(PACKAGEPATH.'lib/filters/');	
	load(PACKAGEPATH.'lib/controllers/');
	global $loadviewengine;
	if($loadviewengine)
		include(PACKAGEPATH.'lib/viewengine/'.VIEWENGINE.'/'.VIEWENGINE.'.php');
}
function loadApplications(){
	$appsSettings=AoiSoraSettings::getApplications();
	global $apps;
	$apps=array();
	foreach($appsSettings as $app){
		$apps[]=$app->getValue();
		loadApp($app->getValue());
	}
}
?>